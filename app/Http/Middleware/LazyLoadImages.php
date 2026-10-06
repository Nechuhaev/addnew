<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Додає loading="lazy" decoding="async" до <img> у HTML-відповідях, крім
 * службової графіки (будь-який src зі шляхом /assets/: логотип, прапорці,
 * іконки) і перших SKIP_FIRST контентних зображень (картки, фото: кандидати
 * на LCP, їх лишаємо як є).
 *
 * Навіщо: головна віддає ≈3,5 МБ зображень (58 файлів, оригінали 800–1600 px
 * під мініатюри ≈150 px) і вантажить усе одразу, без ледачого завантаження.
 * mod_pagespeed підставляє заглушки лише деяким клієнтам (не Lighthouse і не
 * ботам), тож власне нативне ледаче завантаження прибирає цю нерівність.
 *
 * Не чіпає: не-GET, AJAX, адмінку, не-HTML, не-200, теги з уже заданим
 * loading і теги з непарними лапками (захист від розірваного атрибута).
 *
 * Відкат: прибрати рядок \App\Http\Middleware\LazyLoadImages::class з групи
 * 'web' у app/Http/Kernel.php.
 */
class LazyLoadImages
{
    const SKIP_FIRST = 4;

    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (!$request->isMethod('GET') || $request->ajax() || $request->is('admin*')) {
            return $response;
        }
        if (!method_exists($response, 'getContent') || $response->getStatusCode() !== 200) {
            return $response;
        }
        if (stripos((string) $response->headers->get('Content-Type'), 'text/html') === false) {
            return $response;
        }

        $html = $response->getContent();
        if (!is_string($html) || stripos($html, '<img') === false) {
            return $response;
        }

        $n = 0;   // лічильник КОНТЕНТНИХ зображень (службова графіка не рахується)
        $new = preg_replace_callback('/<img\b[^>]*>/i', function ($m) use (&$n) {
            $tag = $m[0];
            if (!preg_match('/\ssrc\s*=\s*["\']([^"\']*)/i', $tag, $sm)) {
                return $tag;
            }
            if (strpos($sm[1], '/assets/') !== false || strpos($sm[1], 'data:') === 0) {
                return $tag;   // логотип, прапорці, іконки: крихітні, лишаємо як є
            }
            $n++;
            if ($n <= self::SKIP_FIRST || preg_match('/\sloading\s*=/i', $tag)) {
                return $tag;
            }
            if (substr_count($tag, '"') % 2 !== 0 || substr_count($tag, "'") % 2 !== 0) {
                return $tag;
            }
            $selfClosed = substr($tag, -2) === '/>';

            return substr($tag, 0, $selfClosed ? -2 : -1) . ' loading="lazy" decoding="async"' . ($selfClosed ? ' />' : '>');
        }, $html);

        if (is_string($new) && $new !== $html) {
            $response->setContent($new);
        }

        return $response;
    }
}
