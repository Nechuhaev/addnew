<?php

namespace App\Services;

use App\StopWord;
use Illuminate\Support\Facades\DB;

/**
 * Реальний попит для контент-плану (замість вигаданих AI ключів):
 *  - пошук на самому сайті (search_queries, загальний пошук);
 *  - експорт запитів Google Search Console у storage/app/gsc-queries.csv
 *    (стандартний «Queries.csv» зі звіту «Результати пошуку»: запит, кліки, покази, CTR, позиція).
 * Відсіює «дорослі»/заборонені (стоп-слова), телефони, бренд сайту, надто короткі й порожні запити.
 */
class ContentDemand
{
    const GSC_FILE = 'app/gsc-queries.csv';

    /**
     * @return array [['keyword' => ..., 'volume' => int, 'source' => 'gsc'|'site_search'], ...] — за спаданням volume
     */
    public static function keywords(int $siteSearchDays = 90, int $siteSearchMin = 3): array
    {
        $pool = [];
        foreach (self::fromGscCsv() as $row) {
            self::add($pool, $row['keyword'], $row['volume'], 'gsc');
        }
        foreach (self::fromSiteSearch($siteSearchDays, $siteSearchMin) as $row) {
            self::add($pool, $row['keyword'], $row['volume'], 'site_search');
        }

        $pool = array_values($pool);
        usort($pool, function ($a, $b) {
            return $b['volume'] <=> $a['volume'];
        });

        return $pool;
    }

    public static function fromSiteSearch(int $days, int $min): array
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('search_queries')) {
            return [];
        }

        return DB::table('search_queries')
            ->where('source', 'site')
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('LOWER(TRIM(query)) AS q, COUNT(*) AS n')
            ->groupBy('q')
            ->having('n', '>=', $min)
            ->orderByDesc('n')
            ->limit(2000)
            ->get()
            ->map(function ($r) {
                return ['keyword' => $r->q, 'volume' => (int) $r->n];
            })
            ->all();
    }

    /** Покази з експорту Search Console (перший стовпець — запит, стовпець «Покази»/«Impressions») */
    public static function fromGscCsv(?string $path = null): array
    {
        $path = $path ?: storage_path(self::GSC_FILE);
        if (!is_file($path) || !($fh = fopen($path, 'r'))) {
            return [];
        }

        $header = fgetcsv($fh);
        if (!$header) {
            fclose($fh);
            return [];
        }
        $header = array_map(function ($h) {
            return mb_strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h)));
        }, $header);
        $impCol = null;
        foreach ($header as $i => $h) {
            if (in_array($h, ['impressions', 'покази', 'показы', 'показів'], true)) {
                $impCol = $i;
            }
        }
        if ($impCol === null) {
            $impCol = 2; // Queries.csv: запит, кліки, покази, CTR, позиція
        }

        $out = [];
        while (($row = fgetcsv($fh)) !== false) {
            $q = trim((string) ($row[0] ?? ''));
            $imp = (int) preg_replace('/\D/', '', (string) ($row[$impCol] ?? '0'));
            if ($q !== '' && $imp > 0) {
                $out[] = ['keyword' => mb_strtolower($q), 'volume' => $imp];
            }
        }
        fclose($fh);

        return $out;
    }

    public static function isUsable(string $q): bool
    {
        $q = trim($q);
        if (mb_strlen($q) < 4 || !preg_match('/\p{L}{3,}/u', $q)) {
            return false;
        }
        if (preg_match('/addnew|аддн[еі]в/iu', $q)) {
            return false; // запити про сам сайт — не теми для статей
        }
        if (preg_match('/^\+?[\d\s\-\(\)]{7,}$/', $q)) {
            return false; // телефони
        }

        if (self::isShopName($q)) {
            return false; // назва магазину — люди шукають сам магазин, а не статтю
        }

        return StopWord::findNoindexMatchIn($q) === null && StopWord::findMatchIn($q) === null;
    }

    /** Запит — частина назви магазину на сайті («велого» → «Веломагазин VeloGO (Велогоу, Велого)») */
    protected static function isShopName(string $q): bool
    {
        static $names = null;
        if ($names === null) {
            $names = DB::table('users')
                ->whereIn('id', DB::table('ads')->where('is_product', 1)->select('user_id')->distinct())
                ->pluck('firstname')
                ->map(function ($n) {
                    return mb_strtolower((string) $n);
                })
                ->filter()
                ->all();
        }
        foreach ($names as $name) {
            if (mb_strpos($name, $q) !== false) {
                return true;
            }
        }

        return false;
    }

    protected static function add(array &$pool, string $keyword, int $volume, string $source): void
    {
        $k = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $keyword)));
        if (!self::isUsable($k)) {
            return;
        }
        if (isset($pool[$k])) {
            $pool[$k]['volume'] += $volume;
            return;
        }
        $pool[$k] = ['keyword' => $k, 'volume' => $volume, 'source' => $source];
    }
}
