<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\TranslatesViaDeepL;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Переклад RU → UK через DeepL для областей, міст, країн, категорій і тегів:
 * заповнює порожні *_uk (name, meta_title, meta_description, content), якщо є RU-текст.
 * Назви тегів сюди не входять — для них tags:translate-deepl (зі зворотною перевіркою).
 *
 * - За замовчуванням ТІЛЬКИ ЗВІТ: скільки полів і символів по таблицях
 *   (окремо Україна / інші країни) + кілька пробних перекладів. Запис — лише з --apply.
 * - Пріоритет: спершу все, що стосується України, потім решта.
 * - Бюджет символів на запуск: --max-chars; запобіжник квоти — RESERVE символів
 *   лишаються для статей блогу.
 * - Плейсхолдери ---name--- DeepL не чіпає (ignore_tags), HTML зберігається.
 * - Пише лише в порожні *_uk (заповнене вручну не перезаписує).
 * - Журнал записаного (CSV); --rollback=ФАЙЛ обнуляє записане тим запуском.
 */
class TranslateGeoContentDeepL extends Command
{
    use TranslatesViaDeepL;

    protected $signature = 'geo:translate-deepl
        {--apply : Записати переклади (без цього лише звіт)}
        {--tables=ad_cities,ad_regions,ad_countries,ad_categories,ad_tags : Які таблиці}
        {--fields=name,meta_title,meta_description,content : Які поля}
        {--only-ukraine : Лише області/міста України}
        {--max-chars=100000 : Максимум символів DeepL за один запуск}
        {--sample=8 : Скільки коротких пробних перекладів показати у звіті}
        {--rollback= : Шлях до CSV-журналу: обнулити *_uk, записані тим запуском}';

    protected $description = 'Перекладає назви й SEO-тексти областей/міст/країн/категорій/тегів RU→UK через DeepL; за замовчуванням лише звіт';

    const RESERVE = 150000;      // символів квоти, які лишаємо для статей блогу
    const BATCH_TEXTS = 40;      // текстів в одному запиті (ліміт DeepL — 50)
    const BATCH_CHARS = 30000;   // символів в одному запиті (ліміт тіла — 128 KiB)

    public function handle()
    {
        if ($this->option('rollback')) {
            return $this->rollback($this->option('rollback'));
        }

        $items = $this->collect();
        $this->summary($items);

        // План у межах бюджету: однакові тексти перекладаються один раз
        $maxChars = max(1000, (int) $this->option('max-chars'));
        $plan = [];
        $chars = 0;
        $skippedBudget = 0;
        foreach ($items as $it) {
            $key = $it['text'];
            if (!isset($plan[$key])) {
                $len = mb_strlen($it['text']);
                if ($chars + $len > $maxChars) {
                    $skippedBudget++;
                    continue;
                }
                $plan[$key] = ['text' => $it['text'], 'targets' => []];
                $chars += $len;
            }
            $plan[$key]['targets'][] = $it;
        }
        $targets = array_sum(array_map(function ($p) { return count($p['targets']); }, $plan));
        $this->line('');
        $this->info("План цього запуску (ліміт {$maxChars}): {$targets} полів, " . count($plan) . " унікальних текстів, ≈{$chars} символів DeepL");
        if ($skippedBudget) {
            $this->line("  не влізло в ліміт: {$skippedBudget} полів — наступним запуском або більшим --max-chars");
        }

        $usage = $this->deeplUsage();
        if ($usage) {
            $this->line("Квота DeepL: використано {$usage[0]} з {$usage[1]}; після запуску лишиться ≈" . ($usage[1] - $usage[0] - $chars));
        } else {
            $this->warn('Не вдалося отримати квоту DeepL (перевірте DEEPL_API_KEY / DEEPL_API_FREE).');
        }

        if (!$this->option('apply')) {
            $this->showSample($plan, (int) $this->option('sample'));
            $this->warn('Це лише звіт, нічого не записано. Записати: php artisan geo:translate-deepl --apply' . $this->optionsEcho());

            return 0;
        }
        if (!$plan) {
            $this->info('Нічого перекладати.');

            return 0;
        }
        if ($usage && ($usage[1] - $usage[0]) < $chars + self::RESERVE) {
            $this->error('Недостатньо квоти DeepL з резервом ' . self::RESERVE . ' символів для статей блогу. Зменште --max-chars або дочекайтесь нового періоду.');

            return 1;
        }

        $logPath = storage_path('app/geo_deepl_' . date('Ymd_His') . '.csv');
        $log = fopen($logPath, 'w');
        fputcsv($log, ['table', 'id', 'field', 'value_uk']);

        $written = 0;
        $used = 0;
        $batches = $this->batches(array_values($plan));
        foreach ($batches as $bi => $batch) {
            try {
                $out = $this->translateTexts(array_column($batch, 'text'));
            } catch (\Throwable $e) {
                $this->error('DeepL: ' . $e->getMessage());
                break;
            }
            foreach ($batch as $i => $p) {
                $used += mb_strlen($p['text']);
                foreach ($p['targets'] as $t) {
                    $uk = $this->clean($t['field'], $p['text'], $out[$i]);
                    if ($uk === '') {
                        continue;
                    }
                    $col = $t['field'] . '_uk';
                    $n = DB::table($t['table'])->where('id', $t['id'])
                        ->where(function ($q) use ($col) {
                            $q->whereNull($col)->orWhere($col, '');
                        })
                        ->update([$col => $t['field'] === 'name' ? mb_substr($uk, 0, 250) : $uk]);
                    if ($n) {
                        fputcsv($log, [$t['table'], $t['id'], $t['field'], $uk]);
                        $written++;
                    }
                }
            }
            $this->line('порція ' . ($bi + 1) . '/' . count($batches) . ": записано {$written}, символів DeepL ≈{$used}");
            usleep(250000);
        }
        fclose($log);

        $this->info("Готово. Записано {$written} полів, символів DeepL ≈{$used}.");
        $this->line("Журнал: {$logPath}");
        $this->line("Відкат цього запуску: php artisan geo:translate-deepl --rollback={$logPath}");
        $this->line('Після запису: php artisan cache:clear (кеш блоків сторінок областей/міст).');

        return 0;
    }

    /**
     * Усі поля, де є RU-текст і порожній *_uk. Порядок: Україна → решта; у межах —
     * таблиці й поля в заданому порядку, за id.
     */
    protected function collect(): array
    {
        $tables = array_filter(array_map('trim', explode(',', (string) $this->option('tables'))));
        $fields = array_filter(array_map('trim', explode(',', (string) $this->option('fields'))));
        $ukraineId = DB::table('ad_countries')->where('slug', 'ukraina')->value('id');

        $first = [];
        $rest = [];
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                $this->warn("Таблиці {$table} немає — пропускаю");
                continue;
            }
            foreach ($fields as $field) {
                if ($table === 'ad_tags' && $field === 'name') {
                    continue;   // назви тегів — tags:translate-deepl
                }
                if (!Schema::hasColumn($table, $field) || !Schema::hasColumn($table, $field . '_uk')) {
                    continue;
                }
                $q = DB::table($table . ' as t')
                    ->whereRaw("TRIM(COALESCE(t.`{$field}`, '')) <> ''")
                    ->whereRaw("TRIM(COALESCE(t.`{$field}_uk`, '')) = ''")
                    ->orderBy('t.id');
                $select = ['t.id', "t.{$field} as text"];
                if ($table === 'ad_cities') {
                    $q->join('ad_regions as r', 'r.id', '=', 't.region_id');
                    $select[] = 'r.country_id';
                } elseif ($table === 'ad_regions') {
                    $select[] = 't.country_id';
                }
                if ($this->option('only-ukraine')) {
                    if (!in_array($table, ['ad_cities', 'ad_regions'], true)) {
                        continue;
                    }
                    $q->where($table === 'ad_cities' ? 'r.country_id' : 't.country_id', $ukraineId);
                }
                foreach ($q->select($select)->cursor() as $r) {
                    $text = trim((string) $r->text);
                    $plain = strip_tags($text);
                    if ($field === 'name' && !preg_match('/[\x{0400}-\x{04FF}]/u', $plain)) {
                        continue;   // без кирилиці — перекладати нічого
                    }
                    if (preg_match('/[іїєґІЇЄҐ]/u', $plain) && !preg_match('/[ыэъёЫЭЪЁ]/u', $plain)) {
                        continue;   // у RU-колонці вже український текст — квоту не витрачаємо
                    }
                    $isUa = !isset($r->country_id) || (int) $r->country_id === (int) $ukraineId;
                    $item = ['table' => $table, 'id' => $r->id, 'field' => $field, 'text' => $text, 'ua' => $isUa];
                    if ($isUa) {
                        $first[] = $item;
                    } else {
                        $rest[] = $item;
                    }
                }
            }
        }

        return array_merge($first, $rest);
    }

    protected function summary(array $items): void
    {
        $rows = [];
        foreach ($items as $it) {
            $k = $it['table'] . '.' . $it['field'] . '_uk|' . ($it['ua'] ? 'Україна' : 'інші країни');
            if (!isset($rows[$k])) {
                [$name, $where] = explode('|', $k);
                $rows[$k] = [$name, $where, 0, 0];
            }
            $rows[$k][2]++;
            $rows[$k][3] += mb_strlen($it['text']);
        }
        $this->info('Порожні *_uk при наявному RU-тексті: ' . count($items) . ' полів, ≈' . array_sum(array_column($rows, 3)) . ' символів');
        if ($rows) {
            $this->table(['поле', 'де', 'полів', 'символів'], array_values($rows));
        }
    }

    protected function batches(array $plan): array
    {
        $out = [];
        $cur = [];
        $len = 0;
        foreach ($plan as $p) {
            $l = mb_strlen($p['text']);
            if ($cur && (count($cur) >= self::BATCH_TEXTS || $len + $l > self::BATCH_CHARS)) {
                $out[] = $cur;
                $cur = [];
                $len = 0;
            }
            $cur[] = $p;
            $len += $l;
        }
        if ($cur) {
            $out[] = $cur;
        }

        return $out;
    }

    /**
     * Плейсхолдери ---x--- обгортаються в <keep>, які DeepL не перекладає.
     */
    protected function translateTexts(array $texts): array
    {
        $send = array_map(function ($t) {
            return preg_replace('/---[a-z_]+---/', '<keep>$0</keep>', $t);
        }, $texts);
        $out = $this->translateViaDeepL($send, 'RU', 'UK', ['ignore_tags' => 'keep']);

        return array_map(function ($t) {
            return str_replace(['<keep>', '</keep>'], '', $t);
        }, $out);
    }

    /**
     * Назви: без доданих DeepL лапок і крапки в кінці. Тексти — як є.
     */
    protected function clean(string $field, string $src, string $dst): string
    {
        $dst = trim($dst);
        if ($field !== 'name') {
            return $dst;
        }
        $dst = trim(html_entity_decode($dst, ENT_QUOTES, 'UTF-8'));
        if (mb_strpos($src, '«') === false && mb_strpos($src, '"') === false) {
            $dst = trim(str_replace(['«', '»', '"', '„', '“', '”'], '', $dst));
        }
        if (mb_substr($src, -1) !== '.' && mb_substr($dst, -1) === '.') {
            $dst = rtrim(mb_substr($dst, 0, -1));
        }

        return $dst;
    }

    protected function showSample(array $plan, int $n): void
    {
        if ($n < 1 || !$plan) {
            return;
        }
        // Лише короткі тексти (назви, title) — щоб проба майже не коштувала квоти
        $short = array_values(array_filter($plan, function ($p) {
            return mb_strlen($p['text']) <= 120;
        }));
        if (!$short) {
            return;
        }
        $step = max(1, intdiv(count($short), $n));
        $pick = [];
        for ($i = 0; $i < count($short) && count($pick) < $n; $i += $step) {
            $pick[] = $short[$i];
        }
        try {
            $out = $this->translateTexts(array_column($pick, 'text'));
        } catch (\Throwable $e) {
            $this->error('Не вдалося отримати пробні переклади: ' . $e->getMessage());

            return;
        }
        $this->line('Пробні переклади (у БД нічого не пишеться):');
        foreach ($pick as $i => $p) {
            $t = $p['targets'][0];
            $this->line("  [{$t['table']}.{$t['field']}] {$p['text']}  →  " . $this->clean($t['field'], $p['text'], $out[$i]));
        }
    }

    protected function optionsEcho(): string
    {
        $s = '';
        foreach (['tables', 'fields', 'max-chars'] as $o) {
            if ($this->option($o) !== $this->getDefinition()->getOption($o)->getDefault()) {
                $s .= " --{$o}=" . $this->option($o);
            }
        }

        return $s . ($this->option('only-ukraine') ? ' --only-ukraine' : '');
    }

    protected function deeplUsage(): ?array
    {
        try {
            $isFree = filter_var(env('DEEPL_API_FREE', true), FILTER_VALIDATE_BOOLEAN);
            $base = env('DEEPL_BASE_URL', $isFree ? 'https://api-free.deepl.com' : 'https://api.deepl.com');
            $resp = (new Client())->get(rtrim($base, '/') . '/v2/usage', [
                'headers' => ['Authorization' => 'DeepL-Auth-Key ' . env('DEEPL_API_KEY')],
                'timeout' => 20,
            ]);
            $u = json_decode((string) $resp->getBody(), true);

            return [(int) $u['character_count'], (int) $u['character_limit']];
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function rollback(string $path): int
    {
        if (!is_file($path)) {
            $this->error("Файл не знайдено: {$path}");

            return 1;
        }
        $fh = fopen($path, 'r');
        fgetcsv($fh);
        $n = 0;
        while (($r = fgetcsv($fh)) !== false) {
            [$table, $id, $field, $value] = $r;
            if (!in_array($table, ['ad_cities', 'ad_regions', 'ad_countries', 'ad_categories', 'ad_tags'], true)
                || !in_array($field, ['name', 'meta_title', 'meta_description', 'content'], true)) {
                continue;
            }
            $n += DB::table($table)->where('id', $id)->where($field . '_uk', $value)->update([$field . '_uk' => null]);
        }
        fclose($fh);
        $this->info("Обнулено полів: {$n}");

        return 0;
    }
}
