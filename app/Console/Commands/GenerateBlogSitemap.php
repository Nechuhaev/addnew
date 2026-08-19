<?php

namespace App\Console\Commands;

use App\Article;
use Illuminate\Console\Command;

class GenerateBlogSitemap extends Command
{
    /**
     * php artisan content:sitemap
     *
     * Перегенеровує public/blog.xml на основі поточного стану таблиці
     * articles. Раніше цей файл був статичним і не оновлювався з 2021 —
     * через це Google не бачив жодної статті, опублікованої автоматично.
     */
    protected $signature = 'content:sitemap';

    protected $description = 'Генерує public/blog.xml на основі поточних статей блогу';

    public function handle()
    {
        $articles = Article::orderBy('updated_at', 'desc')->get(['slug', 'created_at', 'updated_at']);

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        foreach ($articles as $article) {
            $urlNode = $xml->addChild('url');
            $urlNode->addChild('loc', route('blog.article', $article->slug));

            $lastmod = $article->updated_at ?? $article->created_at ?? now();
            $urlNode->addChild('lastmod', $lastmod->format('Y-m-d'));
            $urlNode->addChild('changefreq', 'weekly');
            $urlNode->addChild('priority', '0.6');
        }

        file_put_contents(public_path('blog.xml'), $xml->asXML());

        $this->info('blog.xml оновлено, статей у файлі: ' . $articles->count());

        return 0;
    }
}