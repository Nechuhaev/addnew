<?php

namespace App\Console\Commands;

use App\ArticleCategory;
use App\Console\Commands\Concerns\TranslatesViaDeepL;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TranslateBlogCategories extends Command
{
    use TranslatesViaDeepL;

    /**
     * php artisan categories:translate-blog
     *
     * Перекладає категорії блогу (ArticleCategory: Транспорт, Електроніка,
     * Робота тощо) на українську через DeepL. Ці категорії ніколи не мали
     * мовного механізму — рубрики на /blog завжди показувались лише
     * російською, незалежно від локалі відвідувача.
     */
    protected $signature = 'categories:translate-blog {--limit=}';

    protected $description = 'Перекладає категорії блогу на українську через DeepL';

    /** @var Client */
    protected $http;

    public function __construct()
    {
        parent::__construct();
        $this->http = new Client();
    }

    public function handle()
    {
        $limit = (int) ($this->option('limit') ?: 50);

        $categories = ArticleCategory::where(function ($q) {
                $q->whereNull('name_uk')->orWhere('name_uk', '');
            })
            ->limit($limit)
            ->get();

        if ($categories->isEmpty()) {
            $this->info('Усі категорії блогу вже перекладені.');
            return 0;
        }

        $this->info('Перекладаю ' . $categories->count() . ' категорій(ії) блогу');

        foreach ($categories as $category) {
            try {
                $translated = $this->translateOne($category);
                $this->info("ID={$category->id}: {$category->getOriginal('name')} -> " . ($translated ? 'OK' : 'нічого перекладати (усі поля порожні)'));
            } catch (\Throwable $e) {
                $this->error("ID={$category->id}: помилка — " . $e->getMessage());
            }

            usleep(200000);
        }

        return 0;
    }

    /**
     * @return bool true, якщо щось реально переклали й зберегли
     */
    protected function translateOne(ArticleCategory $category): bool
    {
        $fields = ['name', 'content', 'meta_title', 'meta_description'];
        $texts = [];
        $presentFields = [];

        foreach ($fields as $field) {
            $value = $category->getOriginal($field);
            if (!empty($value)) {
                $texts[] = $value;
                $presentFields[] = $field;
            }
        }

        if (empty($texts)) {
            return false;
        }

        // RU -> UK: сирі колонки категорій блогу — російською (той самий
        // стандарт, що й для статей), перекладаємо на українську для _uk.
        $translations = $this->translateViaDeepL($texts, 'RU', 'UK');

        $updates = [];
        foreach ($presentFields as $i => $field) {
            $updates["{$field}_uk"] = $translations[$i] ?? null;
        }

        $category->update($updates);

        return true;
    }
}
