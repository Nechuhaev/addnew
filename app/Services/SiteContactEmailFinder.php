<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Str;

/**
 * Шукає контактний email на сайті магазину: головна сторінка, потім типові
 * сторінки контактів. Використовують shops:check-email і сторінка
 * «Кандидати в магазини» в адмінці.
 */
class SiteContactEmailFinder
{
    /**
     * Типові шляхи сторінки контактів — пробуємо по черзі, якщо
     * на головній сторінці email знайти не вдалось.
     */
    const CONTACT_PATHS = [
        '/contacts', '/contact', '/contact-us', '/contacts.html',
        '/kontakty', '/kontakti', '/about', '/about-us', '/o-nas',
    ];

    /** @var Client */
    protected $http;

    public function __construct(?Client $http = null)
    {
        $this->http = $http ?: new Client();
    }

    /**
     * @return array{status: string, email: string|null, page: string|null}
     *         status: ok | not_found | unreachable; page — шлях, де знайдено ('/' — головна)
     */
    public function find(string $siteUrl): array
    {
        $homeBody = $this->fetchBody($siteUrl);
        if ($homeBody === null) {
            return ['status' => 'unreachable', 'email' => null, 'page' => null];
        }

        if ($email = $this->extractEmail($homeBody)) {
            return ['status' => 'ok', 'email' => $email, 'page' => '/'];
        }

        $base = rtrim($siteUrl, '/');
        foreach (self::CONTACT_PATHS as $path) {
            $pageBody = $this->fetchBody($base . $path);
            if ($pageBody !== null && ($email = $this->extractEmail($pageBody))) {
                return ['status' => 'ok', 'email' => $email, 'page' => $path];
            }
            usleep(200000);
        }

        return ['status' => 'not_found', 'email' => null, 'page' => null];
    }

    /**
     * Завантажує сторінку за URL, повертає тіло відповіді. Будь-яка
     * HTTP-відповідь (навіть 404/500) означає, що домен резолвиться і
     * сервер відповідає — тіло повертаємо в будь-якому разі (навіть
     * сторінка помилки може містити email у спільному футері шаблону).
     * null повертається ЛИШЕ при справжньому мережевому збої (DNS,
     * timeout, відмова з'єднання, SSL) — саме це і є ознакою "домен
     * недоступний" для checkOne().
     */
    public function fetchBody(string $url): ?string
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

            return (string) $response->getBody();
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
    public function extractEmail(string $html): ?string
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

}
