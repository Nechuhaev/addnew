<?php

return [
    // Профілі бренду в соцмережах для schema.org sameAs (через кому в .env: SEO_SAME_AS=https://facebook.com/addnew,https://t.me/addnew)
    'same_as' => array_values(array_filter(array_map('trim', explode(',', (string) env('SEO_SAME_AS', ''))))),

    // Країни, чиї гео-сторінки (області, міста, «категорія + регіон») закриваються noindex, follow
    // і не потрапляють у sitemap. Через кому в .env: SEO_NOINDEX_COUNTRIES=rossiya,belarus
    'noindex_countries' => array_values(array_filter(array_map('trim', explode(',', (string) env('SEO_NOINDEX_COUNTRIES', 'rossiya'))))),
];
