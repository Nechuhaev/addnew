{{--
    hreflang uk / ru / x-default. Підключається з front.layout (після og-partial).

    Логіка та сама, що в мовному перемикачі шапки: назва поточного маршруту
    ('author' ↔ 'ru.author') і його параметри. Адреси проходять через те саме
    правило 'r/ukraina/' → '', що й canonical у layout, щоб hreflang збігався
    з canonical. Службові й приватні сторінки (вхід, профіль, чат, пошук,
    подача оголошення) hreflang не отримують. Будь-яка помилка генерації
    адреси просто вимикає теги, сторінка не страждає.
--}}
@php
    $hlRoute = \Route::currentRouteName();
    $hlParams = request()->route() ? request()->route()->parameters() : [];
    $hlIsRu = $hlRoute && starts_with($hlRoute, 'ru.');
    $hlUk = $hlIsRu ? substr($hlRoute, 3) : $hlRoute;
    $hlRu = $hlUk ? 'ru.' . $hlUk : null;

    $hlSkip = ['login', 'register', 'password', 'profile', 'chat', 'logout', 'search', 'ad.step', 'ad.edit', 'ad.create', 'ad.delete', 'shop.review', 'api', 'verification'];
    $hlBlocked = !$hlUk;
    foreach ($hlSkip as $hlPrefix) {
        if ($hlUk && starts_with($hlUk, $hlPrefix)) {
            $hlBlocked = true;
            break;
        }
    }

    $hlUkUrl = null;
    $hlRuUrl = null;
    if (!$hlBlocked && \Route::has($hlUk) && \Route::has($hlRu)) {
        try {
            $hlUkUrl = str_replace('r/ukraina/', '', route($hlUk, $hlParams));
            $hlRuUrl = str_replace('r/ukraina/', '', route($hlRu, $hlParams));
        } catch (\Throwable $hlErr) {
            $hlUkUrl = null;
            $hlRuUrl = null;
        }
    }
@endphp
@if($hlUkUrl && $hlRuUrl)
<link rel="alternate" hreflang="uk" href="{{ $hlUkUrl }}">
<link rel="alternate" hreflang="ru" href="{{ $hlRuUrl }}">
<link rel="alternate" hreflang="x-default" href="{{ $hlUkUrl }}">
@endif
