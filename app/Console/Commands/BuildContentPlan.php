<?php

namespace App\Console\Commands;

use App\ArticleCategory;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BuildContentPlan extends Command
{
    /**
     * php artisan content:build-plan
     */
    protected $signature = 'content:build-plan';

    protected $description = 'Поповнює семантичне ядро, будує кластери і чергу тем контент-плану';

    /** @var Client */
    protected $http;

    public function __construct()
    {
        parent::__construct();
        $this->http = new Client();
    }

    public function handle()
    {
        $anthropicKey = env('ANTHROPIC_API_KEY');
        if (empty($anthropicKey)) {
            $this->error('ANTHROPIC_API_KEY не задан у .env');
            return 1;
        }

        $minPlanned = (int) env('MIN_PLANNED_ARTICLES', 20);
        $plannedCount = DB::table('content_plan_items')->where('status', 'planned')->count();
        $this->info("У поточному плані {$plannedCount} тем зі статусом 'planned'");

        if ($plannedCount >= $minPlanned) {
            $this->info("Тем у плані достатньо (>= {$minPlanned}), новий пул генерувати не потрібно. Завершую.");
            return 0;
        }

        $existingKeywords = DB::table('semantic_keywords')->pluck('keyword')->map(function ($k) {
            return mb_strtolower(trim($k));
        })->flip()->toArray(); // ['ключ' => 0] — для швидкого isset()-пошуку

        $serpstatToken = env('SERPSTAT_API_TOKEN');
        $newKeywords = [];

        if (!empty($serpstatToken)) {
            $this->info('Знайдено SERPSTAT_API_TOKEN — використовую реальні дані Serpstat для семантики');
            try {
                $seeds = $this->generateSeedKeywords();
                $this->info('Кореневі запити для Serpstat: ' . implode(', ', $seeds));
                $fetched = $this->fetchSerpstatKeywords($seeds);
                foreach ($fetched as $kw) {
                    $lower = mb_strtolower(trim($kw['keyword']));
                    if (!isset($existingKeywords[$lower])) {
                        $newKeywords[] = $kw;
                        $existingKeywords[$lower] = 0;
                    }
                }
                $this->info('Serpstat повернув ' . count($newKeywords) . ' нових унікальних ключів (з реальними метриками)');
            } catch (\Throwable $e) {
                $this->warn('Помилка при роботі з Serpstat API, переходжу на резервний варіант (Claude): ' . $e->getMessage());
            }
        }

        if (empty($newKeywords)) {
            $this->info('Генерую ключі через Claude (без реальних обсягів пошуку)');
            try {
                $claudeKeywords = $this->generateNewKeywordsClaude(array_keys($existingKeywords));
                foreach ($claudeKeywords as $kw) {
                    $lower = mb_strtolower(trim($kw));
                    if (!isset($existingKeywords[$lower]) && $kw !== '') {
                        $newKeywords[] = ['keyword' => $kw, 'volume' => null, 'cpc' => null, 'source' => 'claude'];
                        $existingKeywords[$lower] = 0;
                    }
                }
            } catch (\Throwable $e) {
                $this->error('Не вдалося згенерувати нові ключові фрази: ' . $e->getMessage());
            }
        }

        if (!empty($newKeywords)) {
            $now = now();
            $rows = array_map(function ($kw) use ($now) {
                return [
                    'keyword' => Str::limit($kw['keyword'], 190, ''),
                    'volume' => $kw['volume'] ?? null,
                    'cpc' => $kw['cpc'] ?? null,
                    'source' => $kw['source'] ?? 'serpstat',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $newKeywords);
            DB::table('semantic_keywords')->insert($rows);
            $this->info('Додано ' . count($rows) . ' нових ключів у пул');
        }

        $keywordsPool = DB::table('semantic_keywords')->get(['keyword', 'volume'])->map(function ($row) {
            return ['keyword' => $row->keyword, 'volume' => $row->volume];
        })->toArray();

        if (empty($keywordsPool)) {
            $this->error('Пул ключів порожній, немає з чого будувати кластери і план. Завершую.');
            return 1;
        }

        $existingTopics = DB::table('content_plan_items')->pluck('topic')->map(function ($t) {
            return mb_strtolower(trim($t));
        })->flip()->toArray();
        // Також не пропонуємо теми, що дослівно збігаються з назвами вже існуючих статей
        $existingArticleNames = DB::table('articles')->pluck('name')->map(function ($n) {
            return mb_strtolower(trim($n));
        })->flip()->toArray();
        $existingTopics = array_merge($existingTopics, $existingArticleNames);

        $categories = ArticleCategory::all(['id', 'name'])->toArray();

        try {
            $newEntries = $this->clusterAndPlan($keywordsPool, $existingTopics, $categories);
        } catch (\Throwable $e) {
            $this->error('Не вдалося побудувати кластери і контент-план: ' . $e->getMessage());
            return 1;
        }

        if (empty($newEntries)) {
            $this->info('Нових унікальних тем не згенеровано (можливо, все вже в плані).');
            return 0;
        }

        // Пріоритет: теми з більшим сумарним обсягом пошуку кластера — раніше в черзі
        usort($newEntries, function ($a, $b) {
            return $b['cluster_volume'] <=> $a['cluster_volume'];
        });

        $maxPriority = (int) DB::table('content_plan_items')->max('priority');
        $now = now();
        $rows = [];
        foreach ($newEntries as $i => $entry) {
            $rows[] = [
                'cluster' => $entry['cluster'],
                'cluster_volume' => $entry['cluster_volume'],
                'topic' => Str::limit($entry['topic'], 495, ''),
                'focus_keyword_hint' => Str::limit($entry['focus_keyword_hint'], 250, ''),
                'category_id' => $entry['category_id'],
                'priority' => $maxPriority + $i + 1,
                'status' => 'planned',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('content_plan_items')->insert($rows);

        $totalPlanned = DB::table('content_plan_items')->where('status', 'planned')->count();
        $this->info('Додано ' . count($rows) . " нових тем у контент-план. Всього запланованих тем: {$totalPlanned}");

        return 0;
    }

    // -----------------------------------------------------------------
    // Anthropic (Claude) API
    // -----------------------------------------------------------------

    protected function callClaude(string $prompt, int $maxTokens = 4000): string
    {
        $response = $this->http->post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => env('ANTHROPIC_API_KEY'),
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ],
            'json' => [
                'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
                'max_tokens' => $maxTokens,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ],
            'timeout' => 180,
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $textBlocks = array_filter($data['content'] ?? [], function ($b) {
            return ($b['type'] ?? '') === 'text';
        });
        return trim(implode("\n", array_map(function ($b) {
            return $b['text'];
        }, $textBlocks)));
    }

    protected function extractJson(string $text, string $openChar, string $closeChar): string
    {
        $text = trim($text);
        $start = strpos($text, $openChar);
        $end = strrpos($text, $closeChar);
        if ($start === false || $end === false || $end < $start) {
            throw new \RuntimeException("У відповіді моделі не знайдено структуру {$openChar}...{$closeChar}");
        }
        return substr($text, $start, $end - $start + 1);
    }

    // -----------------------------------------------------------------
    // Serpstat API
    // -----------------------------------------------------------------

    protected function serpstatCall(string $method, array $params): array
    {
        $url = 'https://api.serpstat.com/v4/?token=' . env('SERPSTAT_API_TOKEN');
        $response = $this->http->post($url, [
            'json' => ['id' => '1', 'method' => $method, 'params' => $params],
            'timeout' => 60,
        ]);
        $data = json_decode((string) $response->getBody(), true);
        if (isset($data['error'])) {
            $err = $data['error'];
            throw new \RuntimeException('Serpstat API помилка [' . ($err['code'] ?? '?') . ']: ' . ($err['message'] ?? ''));
        }
        return $data['result'] ?? [];
    }

    protected function generateSeedKeywords(): array
    {
        $siteTopic = env('SITE_TOPIC', 'дошка оголошень');
        $seedCount = (int) env('SERPSTAT_SEED_COUNT', 10);

        $prompt = "Ти — SEO-спеціаліст. Тема сайту: \"{$siteTopic}\".\n\n"
            . "Дай {$seedCount} КОРОТКИХ кореневих пошукових запитів (1-2 слова кожен) — "
            . "базових тем, які реально шукають в Google люди, зацікавлені цією темою. "
            . "Це будуть \"затравки\" для подальшого пошуку пов'язаних запитів, тому обирай "
            . "саме широкі, популярні кореневі слова, а не вузькі фрази.\n\n"
            . "Дай відповідь СТРОГО у форматі JSON-масиву рядків, без пояснень і без "
            . "markdown-розмітки, наприклад:\n[\"слово1\", \"слово2\"]";

        $raw = $this->callClaude($prompt, 500);
        $json = $this->extractJson($raw, '[', ']');
        return json_decode($json, true) ?: [];
    }

    protected function fetchSerpstatKeywords(array $seeds): array
    {
        $se = env('SERPSTAT_SE', 'g_ua');
        $size = (int) env('SERPSTAT_KEYWORDS_PER_SEED', 30);
        $pool = [];

        foreach ($seeds as $seed) {
            $rows = $this->fetchSerpstatKeywordsForSeed($seed, $se, $size);
            $this->info("Serpstat: '{$seed}' → " . count($rows) . ' пов\'язаних ключів');
            foreach ($rows as $row) {
                $kw = trim($row['keyword'] ?? '');
                if ($kw === '') {
                    continue;
                }
                $lower = mb_strtolower($kw);
                $volume = $row['region_queries_count'] ?? ($row['queries_count'] ?? null);
                $cpc = $row['cost'] ?? null;
                if (!isset($pool[$lower]) || ($volume ?? 0) > ($pool[$lower]['volume'] ?? 0)) {
                    $pool[$lower] = ['keyword' => $kw, 'volume' => $volume, 'cpc' => $cpc, 'source' => 'serpstat'];
                }
            }
            // Невелика пауза між запитами, щоб не впиратися в rate limit Serpstat
            // (код помилки -32429 "Too many requests" при надто частих викликах поспіль).
            usleep(700000); // 0.7 секунди
        }

        return array_values($pool);
    }

    /**
     * Запит до Serpstat для одного кореневого слова з однією повторною
     * спробою, якщо перший запит впав через "Too many requests".
     */
    protected function fetchSerpstatKeywordsForSeed(string $seed, string $se, int $size): array
    {
        $attempts = 0;
        while ($attempts < 2) {
            $attempts++;
            try {
                $result = $this->serpstatCall('SerpstatKeywordProcedure.getRelatedKeywords', [
                    'keyword' => $seed,
                    'se' => $se,
                    'size' => $size,
                ]);
                return $result['data'] ?? [];
            } catch (\Throwable $e) {
                $isRateLimit = Str::contains($e->getMessage(), ['Too many requests', '-32429']);
                if ($isRateLimit && $attempts < 2) {
                    $this->warn("Serpstat: rate limit для '{$seed}', чекаю 3 сек і пробую ще раз...");
                    sleep(3);
                    continue;
                }
                $this->warn("Serpstat: не вдалося отримати related keywords для '{$seed}': " . $e->getMessage());
                return [];
            }
        }
        return [];
    }

    // -----------------------------------------------------------------
    // Резервний варіант без Serpstat
    // -----------------------------------------------------------------

    protected function generateNewKeywordsClaude(array $existingKeywords): array
    {
        $siteTopic = env('SITE_TOPIC', 'дошка оголошень');
        $language = env('ARTICLE_LANGUAGE', 'українська');
        $target = (int) env('TARGET_NEW_KEYWORDS', 60);

        $existingStr = !empty($existingKeywords)
            ? implode(', ', array_slice($existingKeywords, -300))
            : '(поки що немає)';

        $prompt = "Ти — SEO-спеціаліст. Тема сайту: \"{$siteTopic}\".\nМова: {$language}.\n\n"
            . "Згенеруй {$target} НОВИХ пошукових ключових фраз (семантичне ядро) для цього сайту — "
            . "конкретні фрази, які реально могли б вводити в Google люди, зацікавлені цією темою. "
            . "Включай суміш: короткі (2-3 слова) і довші (4-6 слів) запити, різні наміри пошуку.\n\n"
            . "НЕ повторюй ці вже наявні ключі:\n{$existingStr}\n\n"
            . "Дай відповідь СТРОГО у форматі JSON-масиву рядків, без пояснень і без markdown-розмітки.";

        $raw = $this->callClaude($prompt, 3000);
        $json = $this->extractJson($raw, '[', ']');
        return json_decode($json, true) ?: [];
    }

    // -----------------------------------------------------------------
    // Кластеризація і побудова плану
    // -----------------------------------------------------------------

    protected function clusterAndPlan(array $keywordsPool, array $existingTopicsLower, array $categories): array
    {
        $siteTopic = env('SITE_TOPIC', 'дошка оголошень');
        $language = env('ARTICLE_LANGUAGE', 'українська');
        $topicsPerCluster = (int) env('TOPICS_PER_CLUSTER', 3);

        $hasVolumes = false;
        $lines = [];
        foreach ($keywordsPool as $k) {
            if (!empty($k['volume'])) {
                $hasVolumes = true;
                $lines[] = "- {$k['keyword']} (обсяг: ~{$k['volume']}/міс)";
            } else {
                $lines[] = "- {$k['keyword']}";
            }
        }
        $keywordsStr = implode("\n", $lines);
        $volumeNote = $hasVolumes
            ? 'У списку вказано реальний щомісячний обсяг пошуку — врахуй це при формуванні кластерів і тем.'
            : '';

        $categoryNames = array_map(function ($c) {
            return $c['name'];
        }, $categories);
        $categoriesStr = implode(', ', $categoryNames);

        $prompt = "Ти — SEO-спеціаліст і контент-стратег. Тема сайту: \"{$siteTopic}\" (дошка оголошень).\n"
            . "Мова контенту: {$language}.\n\n"
            . "Ось семантичне ядро сайту:\n{$keywordsStr}\n\n{$volumeNote}\n\n"
            . "На сайті вже є ТАКІ категорії блогу: {$categoriesStr}.\n\n"
            . "Завдання:\n"
            . "1. Згрупуй ключі в 5-15 тематичних КЛАСТЕРІВ.\n"
            . "2. Для КОЖНОГО кластера запропонуй {$topicsPerCluster} конкретні теми статей.\n"
            . "3. Для кожної теми вкажи, ДО ЯКОЇ З ІСНУЮЧИХ КАТЕГОРІЙ вище вона найбільше підходить "
            . "(використовуй назву категорії ТОЧНО як у списку). Якщо жодна не підходить — напиши null.\n\n"
            . "Дай відповідь СТРОГО у форматі JSON-масиву, без пояснень, за структурою:\n"
            . "[{\"cluster\": \"...\", \"keywords\": [\"...\"], \"topics\": "
            . "[{\"topic\": \"...\", \"focus_keyword_hint\": \"...\", \"category\": \"назва або null\"}]}]";

        $raw = $this->callClaude($prompt, 8000);
        $json = $this->extractJson($raw, '[', ']');
        $clusters = json_decode($json, true) ?: [];

        $volumeLookup = [];
        foreach ($keywordsPool as $k) {
            $volumeLookup[mb_strtolower(trim($k['keyword']))] = $k['volume'] ?? 0;
        }

        $categoryIdByNameLower = [];
        foreach ($categories as $c) {
            $categoryIdByNameLower[mb_strtolower(trim($c['name']))] = $c['id'];
        }

        $newEntries = [];
        foreach ($clusters as $cluster) {
            $clusterName = trim($cluster['cluster'] ?? '');
            $clusterVolume = 0;
            foreach (($cluster['keywords'] ?? []) as $kw) {
                $clusterVolume += $volumeLookup[mb_strtolower(trim($kw))] ?? 0;
            }

            foreach (($cluster['topics'] ?? []) as $t) {
                $topic = trim($t['topic'] ?? '');
                if ($topic === '' || isset($existingTopicsLower[mb_strtolower($topic)])) {
                    continue;
                }

                $categoryId = null;
                $categoryName = $t['category'] ?? null;
                if (!empty($categoryName)) {
                    $categoryId = $categoryIdByNameLower[mb_strtolower(trim($categoryName))] ?? null;
                }

                $newEntries[] = [
                    'cluster' => $clusterName,
                    'cluster_volume' => $clusterVolume,
                    'topic' => $topic,
                    'focus_keyword_hint' => trim($t['focus_keyword_hint'] ?? ''),
                    'category_id' => $categoryId,
                ];
                $existingTopicsLower[mb_strtolower($topic)] = 0;
            }
        }

        return $newEntries;
    }
}