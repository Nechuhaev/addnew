<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\TranslatesViaDeepL;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Переклад назв тегів RU → UK через DeepL (заповнює ad_tags.name_uk).
 *
 * - За замовчуванням ТІЛЬКИ ЗВІТ (скільки назв, скільки символів, кілька
 *   пробних перекладів з вердиктом). Запис лише з --apply.
 * - Пріоритет: спершу теги з sitemap, потім решта за id. Бюджет символів
 *   на запуск: --max-chars (однослівні назви коштують удвічі, див. нижче).
 * - ПЕРЕВІРКА ЗВОРОТНИМ ПЕРЕКЛАДОМ для однослівних назв: без контексту DeepL
 *   плутає омоніми («входные» → «вихідні» замість «вхідні»). Для однослівної
 *   назви переклад приймається, лише якщо зворотний переклад UK→RU збігається
 *   з оригіналом (без урахування регістру та е/ё). Відхилені лишаються без
 *   name_uk (на UA-сторінці показується оригінал) і пишуться в окремий CSV.
 *   Багатослівні назви контекст визначає самі, їх приймаємо без перевірки.
 * - Пропускає назви без кирилиці і ті, що вже містять і/ї/є/ґ.
 * - Не чіпає name, slug та вже заповнені name_uk. Регістр першої літери
 *   узгоджується з оригіналом; “ялинки” додані DeepL прибираються.
 * - Запобіжник квоти: --apply відмовляється, якщо після запуску лишиться
 *   менше RESERVE символів (резерв для статей блогу).
 * - Журнал записаного (CSV); --rollback=ФАЙЛ обнуляє записане тим запуском.
 */
class TranslateTagNamesDeepL extends Command
{
    use TranslatesViaDeepL;

    protected $signature = 'tags:translate-deepl
        {--apply : Записати переклади (без цього лише звіт)}
        {--max-chars=60000 : Максимум символів за один запуск (з урахуванням зворотної перевірки)}
        {--sample=10 : Скільки назв перекласти для перегляду якості у звіті}
        {--rollback= : Шлях до CSV-журналу: обнулити name_uk, записані тим запуском}
        {--retry-rejected : Повторно спробувати назви, відхилені зворотною перевіркою раніше}';

    protected $description = 'Перекладає назви тегів RU→UK через DeepL (name_uk) із перевіркою зворотним перекладом; за замовчуванням лише звіт';

    const BATCH = 40;          // назв в одному запиті DeepL (ліміт 50)
    const RESERVE = 150000;    // символів квоти, які лишаємо для статей блогу

    public function handle()
    {
        if ($this->option('rollback')) {
            return $this->rollback($this->option('rollback'));
        }

        $apply = (bool) $this->option('apply');
        $maxChars = max(1000, (int) $this->option('max-chars'));
        $sitemapSlugs = $this->sitemapSlugs();
        $skip = $this->option('retry-rejected') ? [] : $this->previouslyRejected();

        $rows = DB::table('ad_tags')
            ->where(function ($q) {
                $q->whereNull('name_uk')->orWhere('name_uk', '');
            })
            ->select('id', 'slug', 'name')
            ->orderBy('id')
            ->get();

        $prio = [];
        $rest = [];
        foreach ($rows as $r) {
            if (isset($skip[$r->id])) {
                continue;   // відхилена раніше: результат був би той самий, а квота витрачається
            }
            $n = trim((string) $r->name);
            $len = mb_strlen($n);
            if ($len < 2 || $len > 100) {
                continue;
            }
            if (!preg_match('/[\x{0400}-\x{04FF}]/u', $n)) {
                continue;   // без кирилиці: перекладати не треба
            }
            if (preg_match('/[іїєґІЇЄҐ]/u', $n)) {
                continue;   // вже українська
            }
            $item = ['id' => $r->id, 'name' => $n];
            if (isset($sitemapSlugs[$r->slug])) {
                $prio[] = $item;
            } else {
                $rest[] = $item;
            }
        }
        $all = array_merge($prio, $rest);

        // План запуску: унікальні назви (без урахування регістру) в межах бюджету.
        // Однослівні коштують удвічі (переклад + зворотна перевірка).
        $plan = [];
        $chars = 0;
        $totalUniqueChars = 0;
        $seen = [];
        $single = 0;
        foreach ($all as $it) {
            $key = mb_strtolower($it['name']);
            $len = mb_strlen($it['name']);
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $totalUniqueChars += $len;
                if ($this->isSingleWord($it['name'])) {
                    $single++;
                }
            }
            if (!isset($plan[$key])) {
                $cost = $this->isSingleWord($it['name']) ? $len * 2 : $len;
                if ($chars + $cost > $maxChars) {
                    continue;
                }
                $plan[$key] = ['src' => $it['name'], 'cost' => $cost, 'ids' => []];
                $chars += $cost;
            }
            $plan[$key]['ids'][] = ['id' => $it['id'], 'name' => $it['name']];
        }

        $this->info('Назв тегів без name_uk, що потребують перекладу: ' . count($all) . ' (унікальних: ' . count($seen) . ', однослівних: ' . $single . ', ≈' . $totalUniqueChars . ' символів без перевірки)');
        $this->line('  з них у sitemap (пріоритет): ' . count($prio));
        $this->line('  пропущено раніше відхилених: ' . count($skip) . ' (повторити: --retry-rejected)');
        $this->line('План цього запуску (ліміт ' . $maxChars . '): ' . count($plan) . ' унікальних назв, ≈' . $chars . ' символів з урахуванням перевірки');

        $usage = $this->usage();
        if ($usage) {
            $this->line("Квота DeepL: використано {$usage[0]} з {$usage[1]}; після цього запуску лишиться ≈" . ($usage[1] - $usage[0] - $chars));
        }

        if (!$apply) {
            $this->showSample($plan, (int) $this->option('sample'));
            $this->warn('Це лише звіт, нічого не записано. Щоб записати: php artisan tags:translate-deepl --apply --max-chars=' . $maxChars);

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

        $stamp = date('Ymd_His');
        $logPath = storage_path('app/tag_names_deepl_' . $stamp . '.csv');
        $rejPath = storage_path('app/tag_names_deepl_rejected_' . $stamp . '.csv');
        $log = fopen($logPath, 'w');
        $rej = fopen($rejPath, 'w');
        fputcsv($log, ['id', 'name', 'name_uk']);
        fputcsv($rej, ['id', 'name', 'candidate', 'back_translation']);

        $written = 0;
        $rejected = 0;
        $charsUsed = 0;
        $batches = array_chunk($plan, self::BATCH, true);
        foreach ($batches as $bi => $batch) {
            $keys = array_keys($batch);
            $texts = [];
            foreach ($keys as $k) {
                $texts[] = $batch[$k]['src'];
            }
            try {
                $res = $this->translateChecked($texts);
            } catch (\Throwable $e) {
                $this->error('DeepL: ' . $e->getMessage());
                break;
            }
            foreach ($keys as $i => $k) {
                $r = $res[$i];
                foreach ($batch[$k]['ids'] as $pair) {
                    $uk = $this->matchCase($pair['name'], $r['raw']);
                    if ($uk === null) {
                        continue;
                    }
                    if (!$r['ok']) {
                        fputcsv($rej, [$pair['id'], $pair['name'], $uk, (string) $r['back']]);
                        $rejected++;
                        continue;
                    }
                    DB::table('ad_tags')
                        ->where('id', $pair['id'])
                        ->where(function ($q) {
                            $q->whereNull('name_uk')->orWhere('name_uk', '');
                        })
                        ->update(['name_uk' => mb_substr($uk, 0, 250)]);
                    fputcsv($log, [$pair['id'], $pair['name'], $uk]);
                    $written++;
                }
                $charsUsed += $batch[$k]['cost'];
            }
            $this->line('порція ' . ($bi + 1) . '/' . count($batches) . ': записано ' . $written . ', відхилено перевіркою ' . $rejected . ', символів DeepL ≈' . $charsUsed);
            usleep(250000);
        }
        fclose($log);
        fclose($rej);

        $this->info("Готово. Записано name_uk для {$written} тегів, відхилено зворотною перевіркою {$rejected}, символів DeepL ≈{$charsUsed}.");
        $this->line("Журнал: {$logPath}");
        $this->line("Відхилені (лишились без name_uk): {$rejPath}");
        $this->line("Відкат цього запуску: php artisan tags:translate-deepl --rollback={$logPath}");

        return 0;
    }

    /**
     * Переклад RU→UK. Для однослівних назв (де DeepL плутає омоніми) робить
     * зворотний переклад UK→RU і приймає результат, лише якщо він збігається
     * з оригіналом. Повертає по кожній назві: raw (відповідь DeepL), back, ok.
     */
    protected function translateChecked(array $srcs)
    {
        // ВЕЛИКІ літери DeepL повертає великими: перекладаємо нижній регістр,
        // а регістр відновлюємо в matchCase за оригіналом кожного тегу.
        $send = [];
        foreach ($srcs as $i => $s) {
            $send[$i] = $this->isAllCaps($s) ? mb_strtolower($s) : $s;
        }
        $out = $this->translateViaDeepL($send, 'RU', 'UK');
        $res = [];
        $backIdx = [];
        $backTexts = [];
        foreach ($srcs as $i => $src) {
            $raw = isset($out[$i]) ? $out[$i] : '';
            $uk = $this->matchCase($src, $raw);
            $ok = $uk !== null;
            $res[$i] = ['raw' => $raw, 'uk' => $uk, 'back' => null, 'ok' => $ok];
            if (!$ok || !$this->isSingleWord($src)) {
                continue;
            }
            if ($this->norm($uk) === $this->norm($src)) {
                continue;   // написання однакове в обох мовах: перевіряти нема що
            }
            $backIdx[] = $i;
            $backTexts[] = $uk;
        }
        if ($backTexts) {
            $back = $this->translateViaDeepL($backTexts, 'UK', 'RU');
            foreach ($backIdx as $j => $i) {
                $b = isset($back[$j]) ? trim(html_entity_decode((string) $back[$j], ENT_QUOTES, 'UTF-8')) : '';
                $res[$i]['back'] = $b;
                $res[$i]['ok'] = $this->norm($b) === $this->norm($srcs[$i]);
            }
        }

        return $res;
    }

    /**
     * Пробні переклади з вердиктом (рівномірна вибірка з плану).
     */
    protected function showSample(array $plan, $n)
    {
        if ($n < 1 || !$plan) {
            return;
        }
        $keys = array_keys($plan);
        $step = max(1, intdiv(count($keys), $n));
        $texts = [];
        for ($i = 0; $i < count($keys) && count($texts) < $n; $i += $step) {
            $texts[] = $plan[$keys[$i]]['src'];
        }
        try {
            $res = $this->translateChecked($texts);
        } catch (\Throwable $e) {
            $this->error('Не вдалося отримати пробні переклади: ' . $e->getMessage());

            return;
        }
        $this->line('Пробні переклади з перевіркою (у БД нічого не пишеться):');
        foreach ($texts as $i => $src) {
            $r = $res[$i];
            $verdict = $r['ok'] ? 'прийнято' : 'ВІДХИЛЕНО' . ($r['back'] !== null ? ', зворотний переклад «' . $r['back'] . '»' : '');
            $this->line('  ' . $src . '  →  ' . $r['uk'] . '   [' . $verdict . ']');
        }
    }

    protected function isSingleWord($s)
    {
        return !preg_match('/\s/u', trim((string) $s));
    }

    protected function isAllCaps($s)
    {
        $s = trim((string) $s);

        return mb_strlen($s) > 1 && mb_strtoupper($s) === $s && mb_strtolower($s) !== $s;
    }

    protected function norm($s)
    {
        $s = mb_strtolower(trim((string) $s));
        $s = str_replace('ё', 'е', $s);

        return rtrim($s, ".,!?;: ");
    }

    /**
     * Регістр першої літери як в оригіналі; розкодовує HTML-сутності;
     * прибирає лапки, яких не було в оригіналі (DeepL любить додавати «»).
     */
    protected function matchCase($src, $dst)
    {
        $dst = trim(html_entity_decode((string) $dst, ENT_QUOTES, 'UTF-8'));
        if ($dst === '') {
            return null;
        }
        if (mb_strpos($src, '«') === false && mb_strpos($src, '"') === false) {
            $dst = trim(str_replace(['«', '»', '"', '„', '“', '”'], '', $dst));
            if ($dst === '') {
                return null;
            }
        }
        if ($this->isAllCaps($src)) {
            return mb_strtoupper($dst);
        }
        $s1 = mb_substr($src, 0, 1);
        if (mb_strtolower($s1) === $s1 && mb_strtoupper($s1) !== $s1) {
            $dst = mb_strtolower(mb_substr($dst, 0, 1)) . mb_substr($dst, 1);
        }

        return $dst;
    }

    /**
     * id тегів, відхилених зворотною перевіркою в попередніх запусках.
     */
    protected function previouslyRejected()
    {
        $ids = [];
        foreach (glob(storage_path('app/tag_names_deepl_rejected_*.csv')) ?: [] as $f) {
            $fh = fopen($f, 'r');
            fgetcsv($fh);
            while (($r = fgetcsv($fh)) !== false) {
                if (isset($r[0]) && ctype_digit((string) $r[0])) {
                    $ids[(int) $r[0]] = true;
                }
            }
            fclose($fh);
        }

        return $ids;
    }

    protected function sitemapSlugs()
    {
        $slugs = [];
        $files = glob(public_path('tags-*.xml'));
        foreach ($files ?: [] as $f) {
            if (preg_match_all('~<loc>https?://[^/]+/ad-tag/([^<]+)</loc>~', file_get_contents($f), $m)) {
                foreach ($m[1] as $s) {
                    $slugs[urldecode($s)] = true;
                }
            }
        }

        return $slugs;
    }

    protected function usage()
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

    protected function rollback($path)
    {
        if (!is_file($path)) {
            $this->error("Файл не знайдено: {$path}");

            return 1;
        }
        $fh = fopen($path, 'r');
        fgetcsv($fh);
        $n = 0;
        while (($r = fgetcsv($fh)) !== false) {
            $n += DB::table('ad_tags')->where('id', $r[0])->where('name_uk', $r[2])->update(['name_uk' => null]);
        }
        fclose($fh);
        $this->info("Обнулено name_uk: {$n}");

        return 0;
    }
}
