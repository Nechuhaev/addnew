{{--
    Сутність сайту для Google і AI-пошуку: Organization + WebSite (з пошуком по сайту).
    Профілі в соцмережах (sameAs) — з config/seo.php (SEO_SAME_AS у .env), порожньо — не виводиться.
--}}
@php
    $ssRu = app()->getLocale() === 'ru';
    $ssHome = rtrim(url('/'), '/');
    $ssOrg = [
        '@type' => 'Organization',
        '@id' => $ssHome . '/#organization',
        'name' => 'Addnew.biz',
        'alternateName' => ['AddNew', 'addnew.biz'],
        'url' => $ssHome . '/',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => asset('assets/front/img/logo.png'),
        ],
        'email' => 'info@addnew.biz',
        'description' => $ssRu
            ? 'Addnew.biz — бесплатная доска объявлений Украины: частные объявления и товары интернет-магазинов без комиссии и платы за размещение.'
            : 'Addnew.biz — безкоштовна дошка оголошень України: приватні оголошення й товари інтернет-магазинів без комісії та плати за розміщення.',
        'areaServed' => ['@type' => 'Country', 'name' => $ssRu ? 'Украина' : 'Україна'],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'contactType' => 'customer support',
            'email' => 'info@addnew.biz',
            'url' => route($ssRu ? 'ru.contacts' : 'contacts'),
            'availableLanguage' => ['uk', 'ru'],
        ],
    ];
    if ($ssSameAs = \App\Services\SeoRules::sameAs()) {
        $ssOrg['sameAs'] = $ssSameAs;
    }
    $ssSite = [
        '@type' => 'WebSite',
        '@id' => $ssHome . '/#website',
        'url' => $ssHome . '/',
        'name' => 'Addnew.biz',
        'alternateName' => $ssRu ? 'Доска объявлений Addnew' : 'Дошка оголошень Addnew',
        'inLanguage' => $ssRu ? 'ru-UA' : 'uk-UA',
        'publisher' => ['@id' => $ssHome . '/#organization'],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => route($ssRu ? 'ru.ad.search' : 'ad.search') . '?s={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@graph' => [$ssOrg, $ssSite]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
