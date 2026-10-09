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
        '/kontakty', '/kontakti', '/kontakty.html', '/about', '/about-us', '/o-nas', '/pro-nas',
        '/ua/contacts', '/ua/kontakty', '/uk/contacts', '/ru/contacts', '/ru/kontakty',
        '/page/contacts', '/info/contacts',
    ];

    /** Скільки посилань «Контакти»/«Про нас» із головної перевіряти до типових шляхів */
    const MAX_DISCOVERED_LINKS = 4;

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
            return ['status' => 'unreachable', 'email' => null, 'emails' => [], 'page' => null];
        }

        // Корінь сайту (без шляху), щоб типові шляхи не дописувались до підсторінки
        $parts = parse_url($siteUrl);
        $origin = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '') . (isset($parts['port']) ? ':' . $parts['port'] : '');
        $siteDomain = preg_replace('/^www\./', '', strtolower($parts['host'] ?? ''));

        $found = []; // email => сторінка, де знайдено вперше
        $collect = function (string $html, string $page) use (&$found) {
            foreach ($this->extractEmails($html) as $email) {
                if (!isset($found[$email])) {
                    $found[$email] = $page;
                }
            }
        };

        $collect($homeBody, '/');
        $links = $this->discoverContactLinks($homeBody, $origin);

        // Якщо вказано підсторінку — головну сайту теж перевіряємо
        if (rtrim($siteUrl, '/') !== $origin && ($rootBody = $this->fetchBody($origin . '/')) !== null) {
            $collect($rootBody, '/');
            $links = array_merge($links, $this->discoverContactLinks($rootBody, $origin));
        }

        // Сторінки «Контакти»/«Про нас», на які посилається сам сайт, — завжди:
        // на головній часто стоїть шаблонний або загальний email, а справжній — у контактах
        foreach (array_unique($links) as $url) {
            if (($body = $this->fetchBody($url)) !== null) {
                $collect($body, parse_url($url, PHP_URL_PATH) ?: '/');
            }
            usleep(200000);
        }

        // Типові шляхи — лише якщо нічого не знайшли
        if (!$found) {
            foreach (self::CONTACT_PATHS as $path) {
                if (($body = $this->fetchBody($origin . $path)) !== null) {
                    $collect($body, $path);
                    if ($found) {
                        break;
                    }
                }
                usleep(200000);
            }
        }

        if (!$found) {
            return ['status' => 'not_found', 'email' => null, 'emails' => [], 'page' => null];
        }

        // Найкращий — на домені самого магазину, далі в порядку знаходження
        $emails = array_keys($found);
        usort($emails, function ($a, $b) use ($siteDomain, $emails) {
            $aOwn = $siteDomain && substr($a, -strlen('@' . $siteDomain)) === '@' . $siteDomain;
            $bOwn = $siteDomain && substr($b, -strlen('@' . $siteDomain)) === '@' . $siteDomain;
            if ($aOwn !== $bOwn) {
                return $aOwn ? -1 : 1;
            }
            return array_search($a, $emails) <=> array_search($b, $emails);
        });

        return ['status' => 'ok', 'email' => $emails[0], 'emails' => array_slice($emails, 0, 5), 'page' => $found[$emails[0]]];
    }

    /**
     * Посилання з головної на сторінки контактів того ж сайту — за адресою
     * (contact, kontakt, about, o-nas…) або текстом («Контакти», «Про нас»…)
     */
    protected function discoverContactLinks(string $html, string $origin): array
    {
        $host = parse_url($origin, PHP_URL_HOST);
        $found = [];

        if (!preg_match_all('/<a\b[^>]*href=["\']([^"\'#]+)["\'][^>]*>(.*?)<\/a>/isu', $html, $m, PREG_SET_ORDER)) {
            return [];
        }

        foreach ($m as $link) {
            $href = html_entity_decode(trim($link[1]), ENT_QUOTES, 'UTF-8');
            $text = mb_strtolower(trim(strip_tags($link[2])), 'UTF-8');

            $looksLikeContacts = preg_match('~(contact|kontakt|about|o-nas|pro-nas|feedback|zvorot|zvyaz)~i', $href)
                || preg_match('~(контакт|contact|зв.яз|связ|про нас|о нас|about)~u', $text);
            if (!$looksLikeContacts || preg_match('~^(mailto|tel|javascript):~i', $href)) {
                continue;
            }

            if (strpos($href, '//') === 0) {
                $href = parse_url($origin, PHP_URL_SCHEME) . ':' . $href;
            } elseif (!preg_match('~^https?://~i', $href)) {
                $href = $origin . '/' . ltrim($href, '/');
            }

            $linkHost = preg_replace('/^www\./', '', strtolower((string) parse_url($href, PHP_URL_HOST)));
            if ($linkHost !== preg_replace('/^www\./', '', strtolower((string) $host))) {
                continue; // посилання на інший сайт (соцмережі, маркетплейси)
            }

            $found[] = $href;
            if (count($found) >= self::MAX_DISCOVERED_LINKS) {
                break;
            }
        }

        return array_values(array_unique($found));
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
        return $this->extractEmails($html)[0] ?? null;
    }

    /**
     * Усі придатні email на сторінці: спершу з mailto:, потім із тексту
     * (без дублікатів, технічних адрес і шаблонних заглушок)
     */
    public function extractEmails(string $html): array
    {
        $html = $this->deobfuscate($html);

        $blocklist = [
            'sentry.io', 'wixpress.com', 'google.com', 'godaddy.com',
            'example.com', 'w3.org', 'schema.org', 'prom.ua',
            'bigcart.com', 'shopify.com', 'tilda.ws', 'wix.com',
        ];

        $candidates = [];
        if (preg_match_all('/mailto:([^"\'?\s<]+)/i', $html, $matches)) {
            foreach ($matches[1] as $candidate) {
                $candidates[] = trim(rawurldecode($candidate));
            }
        }
        if (preg_match_all('/\b[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}\b/', $html, $matches)) {
            $candidates = array_merge($candidates, $matches[0]);
        }

        $result = [];
        foreach ($candidates as $candidate) {
            $email = strtolower($candidate);
            if (!isset($result[$email]) && $this->isValidCandidate($email, $blocklist) && !$this->isPlaceholder($email)) {
                $result[$email] = $email;
            }
        }

        return array_values($result);
    }

    /**
     * Шаблонні адреси, які платформи підставляють за замовчуванням
     * (Horoshop, OpenCart, теми WordPress): mail@mail.com, info@example.com…
     */
    protected function isPlaceholder(string $email): bool
    {
        [$local, $domain] = explode('@', $email, 2) + [1 => ''];

        if (in_array($email, [
            'mail@mail.com', 'email@email.com', 'test@test.com', 'admin@admin.com',
            'user@user.com', 'info@info.com', 'name@name.com', 'mail@mail.ru', 'email@mail.com',
        ], true)) {
            return true;
        }
        if (preg_match('/^(your|yoursite|yourdomain|yourcompany|yourstore|domain|site|website|mysite|example|sample|test|company)\.(com|ua|com\.ua|net|org|ru)$/', $domain)) {
            return true;
        }
        if (preg_match('/^(your|youremail|your\.?email|yourname|name|username|email|e-?mail)$/', $local)
            && preg_match('/^(email|mail|domain|example|site|gmail)\.(com|ua|ru)$/', $domain)) {
            return true;
        }

        return false;
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

    /**
     * Розкриває типові способи сховати email від ботів:
     * Cloudflare Email Protection (data-cfemail / #email-protection-…),
     * HTML-сутності (&#64;, &commat;), «info [at] shop [dot] ua».
     */
    protected function deobfuscate(string $html): string
    {
        $decodeCf = function (string $hex): string {
            if (!preg_match('/^[0-9a-f]{4,}$/i', $hex) || strlen($hex) % 2) {
                return '';
            }
            $key = hexdec(substr($hex, 0, 2));
            $email = '';
            for ($i = 2; $i < strlen($hex); $i += 2) {
                $email .= chr(hexdec(substr($hex, $i, 2)) ^ $key);
            }
            return $email;
        };

        $html = preg_replace_callback('/data-cfemail=["\']([0-9a-f]+)["\']/i', function ($m) use ($decodeCf) {
            return 'data-cfemail="" ' . $decodeCf($m[1]) . ' ';
        }, $html);
        $html = preg_replace_callback('~/cdn-cgi/l/email-protection#([0-9a-f]+)~i', function ($m) use ($decodeCf) {
            return 'mailto:' . $decodeCf($m[1]);
        }, $html);

        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // «info [at] shop [dot] com.ua», «info(at)shop.ua», «info {at} shop.ua»
        $html = preg_replace('/\s*[\[\(\{]\s*(?:at|собака)\s*[\]\)\}]\s*/iu', '@', $html);
        $html = preg_replace('/\s*[\[\(\{]\s*(?:dot|крапка|точка)\s*[\]\)\}]\s*/iu', '.', $html);

        return $html;
    }
}
