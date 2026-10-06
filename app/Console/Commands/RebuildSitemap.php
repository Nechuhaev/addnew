<?php

namespace App\Console\Commands;

use App\Ad;
use App\AdCategory;
use App\AdCountry;
use App\AdTag;
use App\Article;
use App\ArticleCategory;
use App\Page;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

/**
 * Перебудова sitemap (заміна sitemap:update, яка лишається в розкладі, доки
 * ми не переключимось).
 *
 * Що змінено проти CreateCountrySitemap:
 *  - теги: лише ті, що НЕ закриті noindex (власний текст + хоч одне
 *    оголошення в Україні, або >= TAG_MIN_ADS оголошень), узгоджено з Tag.php;
 *  - новий authors.xml (магазини з товарами) і static.xml (головна, блог,
 *    магазини, сторінки Page), яких у sitemap не було взагалі;
 *  - для всього, крім оголошень, кожна сторінка йде у двох мовах (/ і /ru/)
 *    з xhtml:link hreflang uk/ru;
 *  - файли пишуться атомарно (tmp + rename): Google не прочитає недописаний;
 *  - застарілі ads-N/tags-N/regions-N/cities-N, яких немає в новому індексі,
 *    переносяться в storage/app/sitemap_stale/ (не видаляються).
 *
 * Режим --preview пише в storage/app/sitemap_preview/ і public/ не чіпає.
 */
class RebuildSitemap extends Command
{
    protected $signature = 'sitemap:rebuild {--preview : Не чіпати public/, писати в storage/app/sitemap_preview/}';

    protected $description = 'Перебудовує sitemap: індексовані теги, магазини, hreflang-альтернативи, атомарний запис';

    const COUNTRY_ID = 62;   // Україна
    const TAG_MIN_ADS = 5;   // узгоджено з порогом noindex у Front/Ad/Tag.php
    const CHUNK = 5000;

    protected $dir;
    protected $preview = false;
    protected $written = [];
    protected $alternates = false;

    public function handle()
    {
        ini_set('memory_limit', '1024M');

        $this->preview = (bool) $this->option('preview');
        $this->dir = $this->preview ? storage_path('app/sitemap_preview') : public_path();
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0775, true);
        }

        $this->alternates = method_exists(Url::class, 'addAlternate');
        if (!$this->alternates) {
            $this->warn('Ця версія spatie/laravel-sitemap не має addAlternate: hreflang-альтернатив не буде.');
        }

        $country = AdCountry::find(self::COUNTRY_ID);
        if (!$country) {
            $this->error('Країну не знайдено.');
            return 1;
        }
        $cityIds = $country->cities->pluck('id')->all();
        $index = SitemapIndex::create();

        $this->info($this->preview ? 'РЕЖИМ ПЕРЕГЛЯДУ: public/ не чіпаємо' : 'БОЙОВИЙ РЕЖИМ: пишемо в public/');

        // 1. Головна, блог, каталог магазинів, сторінки Page
        $static = [route('index'), route('blog.index'), route('stores')];
        Page::all()->each(function ($p) use (&$static) {
            if ($p->url) {
                $static[] = $p->url;
            }
        });
        $this->writePairs('static.xml', $static, $index);

        // 2. Магазини (користувачі, у яких є товари)
        $shopIds = DB::table('users')->whereIn('id', function ($q) {
            $q->select('user_id')->from('ads')->where('is_product', 1);
        })->pluck('id')->all();
        $shops = [];
        foreach ($shopIds as $id) {
            $shops[] = route('author', ['id' => $id]);
        }
        $this->writePairs('authors.xml', $shops, $index);

        // 3. Блог: рубрики і статті
        $blogCats = ArticleCategory::all()->map(function ($c) {
            return $c->url;
        })->all();
        $this->writePairs('articles.xml', $blogCats, $index);

        $posts = [];
        foreach (Article::all() as $a) {
            $mod = null;
            try {
                $raw = $a->getOriginal('updated_at');
                $mod = $raw ? Carbon::parse($raw) : null;
            } catch (\Throwable $e) {
                $mod = null;
            }
            $posts[] = ['url' => $a->url, 'lastmod' => $mod];
        }
        $this->writePairs('blog.xml', $posts, $index);

        // 4. Рубрики оголошень, регіони, міста
        $adCats = AdCategory::all()->map(function ($c) {
            return $c->url;
        })->all();
        $this->writePairs('ad_categories.xml', $adCats, $index);

        $regions = [];
        DB::table('ad_regions')
            ->where('country_id', $country->id)
            ->whereNotNull('slug')
            ->pluck('slug')
            ->each(function ($slug) use (&$regions, $country) {
                $regions[] = route('region.page', ['country' => $country->slug, 'region' => $slug]);
            });
        $this->writePairs('regions-1.xml', $regions, $index);

        $cities = [];
        DB::table('ad_cities')
            ->join('ad_regions', 'ad_cities.region_id', '=', 'ad_regions.id')
            ->where('ad_regions.country_id', $country->id)
            ->select('ad_regions.slug as region', 'ad_cities.slug as city')
            ->get()
            ->each(function ($r) use (&$cities, $country) {
                if ($r->region && $r->city) {
                    $cities[] = route('city.page', ['country' => $country->slug, 'region' => $r->region, 'city' => $r->city]);
                }
            });
        $this->writePairs('cities-1.xml', $cities, $index);

        // 5. Теги: лише ті, що не закриті noindex
        $tagTotal = AdTag::count();
        $kept = 0;
        $inUa = function ($q) use ($cityIds) {
            $q->whereIn('city_id', $cityIds);
        };
        AdTag::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where(function ($q) use ($inUa) {
                $q->whereHas('ads', $inUa, '>=', self::TAG_MIN_ADS)
                    ->orWhere(function ($w) use ($inUa) {
                        $w->where(function ($c) {
                            $c->where(function ($x) {
                                $x->whereNotNull('content')->where('content', '!=', '');
                            })->orWhere(function ($x) {
                                $x->whereNotNull('content_uk')->where('content_uk', '!=', '');
                            });
                        })->whereHas('ads', $inUa);
                    });
            })
            ->chunk(self::CHUNK, function ($tags, $page) use ($index, &$kept) {
                $urls = [];
                foreach ($tags as $t) {
                    $urls[] = route('tag', ['slug' => $t->slug]);
                }
                $kept += count($urls);
                $this->writePairs("tags-{$page}.xml", $urls, $index);
            });
        $this->info("Теги: у базі {$tagTotal}, у sitemap {$kept} (решта закрита noindex)");

        // 6. Оголошення (без альтернатив: текст користувацький, один на обидві мови)
        $adsCount = 0;
        Ad::query()
            ->select('id', 'slug')
            ->whereIn('city_id', $cityIds)
            ->chunk(self::CHUNK, function ($ads, $page) use ($index, &$adsCount) {
                $sm = Sitemap::create();
                $n = 0;
                foreach ($ads as $ad) {
                    if ($ad->slug) {
                        $sm->add(route('ad.page', ['slug' => $ad->slug]));
                        $n++;
                    }
                }
                $adsCount += $n;
                $name = "ads-{$page}.xml";
                $this->put($sm, $name);
                $index->add('/' . $name);
                $this->line("{$name}: {$n} URL");
            });
        $this->info("Оголошень у sitemap: {$adsCount}");

        // Індекс пишемо ПОСЛЕ всіх дочірніх файлів
        $this->put($index, 'sitemap.xml');
        $moved = $this->moveStale();

        $this->info('Готово. Файлів записано: ' . count($this->written) . ($this->preview ? ' (перегляд)' : ", застарілих перенесено: {$moved}"));

        return 0;
    }

    /**
     * Пише сторінки обома мовами (/ і /ru/), кожну з xhtml:link hreflang uk/ru.
     * $items: рядки-URL або ['url' => ..., 'lastmod' => Carbon|null].
     */
    protected function writePairs($filename, array $items, SitemapIndex $index)
    {
        $sm = Sitemap::create();
        $count = 0;
        foreach ($items as $item) {
            $url = is_array($item) ? $item['url'] : $item;
            $mod = is_array($item) ? ($item['lastmod'] ?? null) : null;
            if (!$url) {
                continue;
            }
            $ru = $this->ruUrl($url);
            foreach ([$url, $ru] as $loc) {
                $entry = Url::create($loc);
                if ($mod) {
                    $entry->setLastModificationDate($mod);
                }
                if ($this->alternates) {
                    $entry->addAlternate($url, 'uk')->addAlternate($ru, 'ru');
                }
                $sm->add($entry);
                $count++;
            }
        }
        $this->put($sm, $filename);
        $index->add('/' . $filename);
        $this->line("{$filename}: {$count} URL (" . intdiv($count, 2) . ' сторінок × 2 мови)');
    }

    /**
     * Російська версія адреси: https://host/path → https://host/ru/path,
     * головна https://host → https://host/ru.
     */
    protected function ruUrl($url)
    {
        $parts = parse_url($url);
        $base = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '');
        $path = rtrim($parts['path'] ?? '', '/');

        return $base . '/ru' . $path;
    }

    /**
     * Атомарний запис: у тимчасовий файл, потім rename. Google ніколи не
     * побачить напівзаписаний sitemap.
     */
    protected function put($sitemap, $filename)
    {
        $final = $this->dir . '/' . $filename;
        $tmp = $final . '.tmp';
        $sitemap->writeToFile($tmp);
        rename($tmp, $final);
        $this->written[] = $filename;
    }

    /**
     * Переносить у storage/app/sitemap_stale/ файли ads-N, tags-N, regions-N,
     * cities-N, яких немає в новому індексі (лишились від старих запусків).
     */
    protected function moveStale()
    {
        if ($this->preview) {
            return 0;
        }

        $moved = 0;
        $stale = storage_path('app/sitemap_stale');
        foreach (['ads', 'tags', 'regions', 'cities'] as $prefix) {
            $files = glob($this->dir . '/' . $prefix . '-*.xml');
            foreach ($files ?: [] as $path) {
                $name = basename($path);
                if (!preg_match('/^' . $prefix . '-\d+\.xml$/', $name) || in_array($name, $this->written, true)) {
                    continue;
                }
                if (!is_dir($stale)) {
                    mkdir($stale, 0775, true);
                }
                rename($path, $stale . '/' . $name);
                $this->line("перенесено застарілий файл {$name} → storage/app/sitemap_stale/");
                $moved++;
            }
        }

        return $moved;
    }
}
