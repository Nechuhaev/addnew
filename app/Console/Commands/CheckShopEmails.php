<?php

namespace App\Console\Commands;

use App\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckShopEmails extends Command
{
    /**
     * php artisan shops:check-email
     *
     * Для кожного магазину із заповненим site_url завантажує головну
     * сторінку сайту й шукає реальний контактний email (mailto: посилання
     * в першу чергу, як найнадійніше джерело). Якщо знайдений email
     * відрізняється від того, що вказаний у нас — автоматично оновлює
     * поле email магазину й позначає це в лозі (звідти список магазинів
     * покаже попереджувальну іконку). Якщо збігається — просто фіксує
     * "OK", і список підсвітить email зеленим.
     */
    protected $signature = 'shops:check-email {--limit=} {--user=}';

    protected $description = 'Звіряє email магазину з тим, що реально вказано на сайті магазину';

    /** @var Client */
    protected $http;

    public function __construct()
    {
        parent::__construct();
        $this->http = new Client();
    }

    public function handle()
    {
        $limit = (int) ($this->option('limit') ?: env('SHOP_EMAIL_CHECK_PER_RUN', 20));
        $userId = $this->option('user');

        $query = User::whereHas('ads', function ($q) {
                $q->where('is_product', 1);
            })
            ->whereNotNull('site_url')
            ->where('site_url', '!=', '');

        if ($userId) {
            $query->where('id', $userId);
        } else {
            $query->orderByRaw('(SELECT MAX(checked_at) FROM shop_email_checks WHERE shop_email_checks.user_id = users.id) IS NOT NULL')
                ->orderByRaw('(SELECT MAX(checked_at) FROM shop_email_checks WHERE shop_email_checks.user_id = users.id) ASC')
                ->limit($limit);
        }

        $shops = $query->get();

        if ($shops->isEmpty()) {
            $this->info('Немає магазинів із заповненим site_url для перевірки.');
            return 0;
        }

        $this->info('Перевіряю email для ' . $shops->count() . ' магазинів(ну)');

        foreach ($shops as $i => $shop) {
            $this->info('[' . ($i + 1) . '/' . $shops->count() . "] {$shop->username} ({$shop->site_url})");

            try {
                $this->checkOne($shop);
            } catch (\Throwable $e) {
                $this->error('Помилка: ' . $e->getMessage());
            }

            usleep(300000);
        }

        return 0;
    }

    /**
     * Типові шляхи сторінки контактів — пробуємо по черзі, якщо
     * на головній сторінці email знайти не вдалось.
     */
    const CONTACT_PATHS = [
        '/contacts', '/contact', '/contact-us', '/contacts.html',
        '/kontakty', '/kontakti', '/about', '/about-us', '/o-nas',
    ];

    protected function checkOne(User $shop): void
    {
        $foundEmail = $this->fetchAndExtract($shop->site_url);

        if (!$foundEmail) {
            $base = rtrim($shop->site_url, '/');
            foreach (self::CONTACT_PATHS as $path) {
                $foundEmail = $this->fetchAndExtract($base . $path);
                if ($foundEmail) {
                    $this->info("  (знайдено на {$path})");
                    break;
                }
                usleep(200000);
            }
        }

        if (!$foundEmail) {
            $this->warn('Email не знайдено ані на головній, ані на сторінках контактів');
            $this->logCheck($shop->id, $shop->email, null, false);
            return;
        }

        $matches = strtolower(trim($foundEmail)) === strtolower(trim($shop->email));

        if ($matches) {
            $this->info('Email збігається: OK');
            $this->logCheck($shop->id, $shop->email, $foundEmail, true);
            return;
        }

        // Захист від дублікатів: якщо знайдений email уже належить
        // ІНШОМУ магазину — це майже напевно спільний технічний email
        // платформи (як s@prom.ua), а не персональний контакт продавця.
        // НЕ зберігаємо, тільки логуємо для видимості.
        $ownedByOther = User::where('email', $foundEmail)
            ->where('id', '!=', $shop->id)
            ->exists();

        if ($ownedByOther) {
            $this->warn("Знайдений email '{$foundEmail}' уже належить іншому магазину — ігнорую (схоже на спільний технічний email платформи)");
            $this->logCheck($shop->id, $shop->email, $foundEmail, false);
            return;
        }

        $oldEmail = $shop->email;
        $shop->email = $foundEmail;
        $shop->save();

        $this->warn("Email оновлено: {$oldEmail} -> {$foundEmail}");
        $this->logCheck($shop->id, $oldEmail, $foundEmail, false);
    }

    /**
     * Завантажує сторінку за URL і намагається знайти email. Повертає
     * null тихо при будь-якій помилці мережі (сторінка контактів може
     * просто не існувати за цим шляхом — це нормально, не варто зупиняти
     * весь прогін через 404 на одному з кандидатів).
     */
    protected function fetchAndExtract(string $url): ?string
    {
        try {
            $response = $this->http->get($url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (compatible; AddnewShopChecker/1.0; +https://addnew.biz)',
                ],
                'timeout' => 15,
                'verify' => false,
                'http_errors' => false,
            ]);

            if ($response->getStatusCode() >= 400) {
                return null;
            }

            return $this->extractEmail((string) $response->getBody());
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Шукає email на сторінці. Пріоритет — mailto: посилання (найнадійніше,
     * бо це свідомо позначений контактний email на самому сайті), потім
     * загальний regex-пошук по тексту як запасний варіант. Відсіює явно
     * технічні/платформні адреси (sentry, wixpress, google тощо).
     */
    protected function extractEmail(string $html): ?string
    {
        $blocklist = [
            'sentry.io', 'wixpress.com', 'google.com', 'godaddy.com',
            'example.com', 'w3.org', 'schema.org', 'prom.ua',
            'bigcart.com', 'shopify.com', 'tilda.ws', 'wix.com',
        ];

        if (preg_match_all('/mailto:([^"\'?\s]+)/i', $html, $matches)) {
            foreach ($matches[1] as $candidate) {
                $candidate = trim($candidate);
                if ($this->isValidCandidate($candidate, $blocklist)) {
                    return $candidate;
                }
            }
        }

        if (preg_match_all('/\b[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}\b/', $html, $matches)) {
            foreach ($matches[0] as $candidate) {
                if ($this->isValidCandidate($candidate, $blocklist)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    protected function isValidCandidate(string $email, array $blocklist): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Захист від хибних спрацювань на іменах файлів зображень типу
        // "footer_logo@2x.png" чи "banner@4x.jpg" — структурно валідні
        // email, але явно не email. Retina-суфікси (@1x/@2x/@3x/@4x) і
        // типові розширення зображень/шрифтів як "домен" — відсікаємо.
        if (preg_match('/@[0-9]+x\.(png|jpe?g|gif|webp|svg|ico|bmp|woff2?|ttf|eot)$/i', $email)) {
            return false;
        }
        $imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico', 'bmp', 'woff', 'woff2', 'ttf', 'eot'];
        $ext = strtolower(pathinfo($email, PATHINFO_EXTENSION));
        if (in_array($ext, $imageExtensions, true)) {
            return false;
        }

        // Захист від власного домену — якщо магазин помилково вказав
        // site_url на addnew.biz (тестові дані), не даємо йому "знайти"
        // наш власний контактний email і записати як свій.
        if (Str::contains(strtolower($email), 'addnew.biz')) {
            return false;
        }

        foreach ($blocklist as $blocked) {
            if (Str::contains(strtolower($email), $blocked)) {
                return false;
            }
        }
        return true;
    }

    protected function logCheck(int $userId, ?string $oldEmail, ?string $foundEmail, bool $matched): void
    {
        DB::table('shop_email_checks')->insert([
            'user_id' => $userId,
            'old_email' => $oldEmail,
            'found_email' => $foundEmail,
            'matched' => $matched,
            'checked_at' => now(),
        ]);
    }
}