<?php

return [
    // Профілі бренду в соцмережах для schema.org sameAs (через кому в .env: SEO_SAME_AS=https://facebook.com/addnew,https://t.me/addnew)
    'same_as' => array_values(array_filter(array_map('trim', explode(',', (string) env('SEO_SAME_AS', ''))))),

    // Країни, чиї гео-сторінки (області, міста, «категорія + регіон») закриваються noindex, follow
    // і не потрапляють у sitemap. Через кому в .env: SEO_NOINDEX_COUNTRIES=rossiya,belarus
    // Категорії (слаги), які разом із підкатегоріями, оголошеннями й сторінками «категорія + регіон»
    // закриваються noindex, не йдуть у sitemap і показуються без AdSense. Через кому: SEO_NOINDEX_CATEGORIES
    'noindex_categories' => array_values(array_filter(array_map('trim', explode(',', (string) env('SEO_NOINDEX_CATEGORIES', 'znakomstva-i-kontaktyi'))))),

    'noindex_countries' => array_values(array_filter(array_map('trim', explode(',', (string) env('SEO_NOINDEX_COUNTRIES', 'rossiya'))))),
];
