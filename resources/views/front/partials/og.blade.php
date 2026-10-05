{{--
    Open Graph + Twitter Cards. Підключається з front.layout перед </head>.

    За замовчуванням бере тексти з секцій meta_title / meta_description, які
    сторінки вже задають для звичайних мета-тегів. Сторінка може перевизначити:
      @section('og_title', '...')        @section('og_description', '...')
      @section('og_image', '...')        @section('og_type', 'article')
      @section('og_url', '...')          @section('og_card', 'summary')
    і додати власні теги через @push('og_extra') ... @endpush.

    УВАГА: у @section('x', вираз) вираз НЕ повинен давати null — Blade сприйме
    це як початок блокової секції. Для необов'язкових значень пишіть (string) ($v ?? '').
--}}
@php
    $ogLocale = app()->getLocale() === 'ru' ? 'ru_RU' : 'uk_UA';
    $ogLocaleAlt = $ogLocale === 'ru_RU' ? 'uk_UA' : 'ru_RU';
    $ogLang = $ogLocale === 'ru_RU' ? 'ru' : 'uk';

    // Очищення тексту для превʼю: без HTML, емодзі, китайських дужок і зайвих пробілів.
    $ogClean = function ($text, $limit) {
        $text = html_entity_decode((string) $text, ENT_QUOTES, 'UTF-8');
        $text = strip_tags($text);
        $text = preg_replace('/[\x{1F000}-\x{1FFFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{200D}\x{FE0F}\x{203C}\x{2049}\x{3010}\x{3011}]/u', '', $text);
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        return \Illuminate\Support\Str::limit($text, $limit, '…');
    };

    // Абсолютний URL, придатний для краулерів Facebook/Telegram/Viber:
    // відносний шлях → повний; пробіли й кирилиця в шляху → %-кодування.
    // Вже закодовані послідовності (напр. %2520 у ключах S3) не чіпаємо.
    $ogAbsolute = function ($url) {
        $url = trim(html_entity_decode((string) $url, ENT_QUOTES, 'UTF-8'));
        if ($url === '') {
            return null;
        }
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        }
        if (!preg_match('~^https?://~i', $url)) {
            $url = url($url);
        }
        return preg_replace_callback('/[^\x21-\x7E]/', function ($m) {
            return rawurlencode($m[0]);
        }, $url);
    };

    $ogTitle = $ogClean($__env->yieldContent('og_title') ?: $__env->yieldContent('meta_title'), 95);
    if ($ogTitle === '') {
        $ogTitle = $ogLang === 'ru' ? 'AddNew.biz — доска бесплатных объявлений' : 'AddNew.biz — дошка безкоштовних оголошень';
    }

    $ogDesc = $ogClean($__env->yieldContent('og_description') ?: $__env->yieldContent('meta_description'), 200);
    if ($ogDesc === '') {
        $ogDesc = $ogLang === 'ru'
            ? 'Бесплатные объявления в Украине: товары, услуги, работа, недвижимость.'
            : 'Безкоштовні оголошення в Україні: товари, послуги, робота, нерухомість.';
    }

    $ogPageImage = $ogAbsolute($__env->yieldContent('og_image'));
    $ogDefaultRel = 'assets/front/img/og-default-' . $ogLang . '.png';
    $ogHasDefault = file_exists(public_path($ogDefaultRel));
    if ($ogPageImage) {
        $ogImage = $ogPageImage;
        $ogCard = trim($__env->yieldContent('og_card')) ?: 'summary_large_image';
    } elseif ($ogHasDefault) {
        $ogImage = asset($ogDefaultRel);
        $ogCard = 'summary_large_image';
    } else {
        $ogImage = asset('assets/front/img/logo.png');
        $ogCard = 'summary';
    }

    $ogUrl = $ogAbsolute($__env->yieldContent('og_url')) ?: url()->current();
    $ogType = trim($__env->yieldContent('og_type')) ?: 'website';
@endphp
<meta property="og:site_name" content="AddNew.biz">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:locale" content="{{ $ogLocale }}">
<meta property="og:locale:alternate" content="{{ $ogLocaleAlt }}">
<meta property="og:url" content="{{ $ogUrl }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDesc }}">
<meta property="og:image" content="{{ $ogImage }}">
@if(!$ogPageImage && $ogHasDefault)
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
@endif
<meta property="og:image:alt" content="{{ $ogTitle }}">
<meta name="twitter:card" content="{{ $ogCard }}">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDesc }}">
<meta name="twitter:image" content="{{ $ogImage }}">
@stack('og_extra')
