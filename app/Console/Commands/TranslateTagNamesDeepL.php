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
 *   пробних перекладів для оцінки якості). Запис лише з --apply.
 * - Пріоритет: спершу теги, що є в sitemap (їх бачать люди й Google),
 *   потім решта за id. Бюджет символів на запуск: --max-chars.
 * - Пропускає назви без кирилиці (цифри, латиниця) і назви, що вже містять
 *   українські літери (і/ї/є/ґ): вони вже українські.
 * - Не чіпає name, slug та вже заповнені name_uk. Регістр першої літери
 *   узгоджується з оригіналом (DeepL любить робити велику).
 * - Запобіжник квоти: --apply відмовляється працювати, якщо після запуску
 *   у квоті DeepL лишиться менше RESERVE символів (резерв для статей блогу).
 * - Кожен запис пишеться в CSV-журнал; --rollback=ФАЙЛ обнуляє name_uk,
 *   які записав саме цей запуск.
 */
class TranslateTagNamesDeepL extends Command
{
    use TranslatesViaDeepL;

    protected $signature = 'tags:translate-deepl
        {--apply : Записати переклади (без цього лише звіт)}
        {--max-chars=60000 : Максимум символів джерела за один запуск}
        {--sample=8 : Скільки назв перекласти для перегляду якості у звіті}
        {--rollback= : Шлях до CSV-журналу: обнулити name_uk, записані тим запуском}';

    protected $description = 'Перекладає назви тегів RU→UK через DeepL (name_uk); за замовчуванням лише звіт';

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

        // План запуску: унікальні назви (без урахування регістру) в межах бюджету
        $plan = [];
        $chars = 0;
        $totalUniqueChars = 0;
        $seen = [];
        foreach ($all as $it) {
            $key = mb_strtolower($it['name']);
            $len = mb_strlen($it['name']);
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $totalUniqueChars += $len;
            }
            if (!isset($plan[$key])) {
                if ($chars + $len > $maxChars) {
                    continue;
                }
                $plan[$key] = ['src' => $it['name'], 'ids' => []];
                $chars += $len;
            }
            $plan[$key]['ids'][] = ['id' => $it['id'], 'name' => $it['name']];
        }

        $this->info('Назв тегів без name_uk, що потребують перекладу: ' . count($all) . ' (унікальних: ' . count($seen) . ', ≈' . $totalUniqueChars . ' символів)');
        $this->line('  з них у sitemap (пріоритет): ' . count($prio));
        $this->line('План цього запуску (ліміт ' . $maxChars . '): ' . count($plan) . ' унікальних назв, ≈' . $chars . ' символів');

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

        $logPath = storage_path('app/tag_names_deepl_' . date('Ymd_His') . '.csv');
        $log = fopen($logPath, 'w');
        fputcsv($log, ['id', 'name', 'name_uk']);

        $written = 0;
        $charsUsed = 0;
        $batches = array_chunk($plan, self::BATCH, true);
        foreach ($batches as $bi => $batch) {
            $keys = array_keys($batch);
            $texts = [];
            foreach ($keys as $k) {
                $texts[] = $batch[$k]['src'];
            }
            try {
                $out = $this->translateViaDeepL($texts, 'RU', 'UK');
            } catch (\Throwable $e) {
                $this->error('DeepL: ' . $e->getMessage());
                break;
            }
            foreach ($keys as $i => $k) {
                foreach ($batch[$k]['ids'] as $pair) {
                    $uk = $this->matchCase($pair['name'], isset($out[$i]) ? $out[$i] : '');
                    if ($uk === null) {
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
                $charsUsed += mb_strlen($batch[$k]['src']);
            }
            $this->line('порція ' . ($bi + 1) . '/' . count($batches) . ': записано тегів ' . $written . ', символів DeepL ≈' . $charsUsed);
            usleep(250000);
        }
        fclose($log);

        $this->info("Готово. Записано name_uk для {$written} тегів, символів DeepL ≈{$charsUsed}.");
        $this->line("Журнал: {$logPath}");
        $this->line("Відкат цього запуску: php artisan tags:translate-deepl --rollback={$logPath}");

        return 0;
    }

    /**
     * Пробні переклади для оцінки якості (рівномірна вибірка з плану).
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
            $out = $this->translateViaDeepL($texts, 'RU', 'UK');
        } catch (\Throwable $e) {
            $this->error('Не вдалося отримати пробні переклади: ' . $e->getMessage());

            return;
        }
        $this->line('Пробні переклади (у БД нічого не пишеться):');
        foreach ($texts as $i => $src) {
            $this->line('  ' . $src . '  →  ' . $this->matchCase($src, isset($out[$i]) ? $out[$i] : ''));
        }
    }

    /**
     * Регістр першої літери як в оригіналі; розкодовує HTML-сутності
     * (DeepL у режимі html може повернути &amp; тощо).
     */
    protected function matchCase($src, $dst)
    {
        $dst = trim(html_entity_decode((string) $dst, ENT_QUOTES, 'UTF-8'));
        if ($dst === '') {
            return null;
        }
        $s1 = mb_substr($src, 0, 1);
        if (mb_strtolower($s1) === $s1 && mb_strtoupper($s1) !== $s1) {
            $dst = mb_strtolower(mb_substr($dst, 0, 1)) . mb_substr($dst, 1);
        }

        return $dst;
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
