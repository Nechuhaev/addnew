@php
    $hasFeed = $hasMerchantFeed === 'yes';
@endphp
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0; padding:0; background:#f5f6f8;">
<div style="max-width:600px; margin:0 auto; padding:24px 16px; font-family:Arial,Helvetica,sans-serif; font-size:15px; line-height:1.55; color:#222;">
    <div style="background:#fff; border-radius:8px; padding:24px;">
        <p style="margin-top:0;">{{ $contactName !== '' ? "Добрий день, {$contactName}!" : 'Добрий день!' }}</p>

        <p>Дякуємо за розмову з нашою віртуальною асистенткою. Як і домовлялися, надсилаємо коротку інструкцію,
            як безкоштовно розмістити товари магазину «{{ $storeName }}» на дошці оголошень Addnew.biz.
            Для магазинів це завжди безкоштовно — без прихованих платежів і платних ТОПів.</p>

        <ol style="padding-left:20px;">
            <li style="margin-bottom:8px;">Зареєструйте магазин: <a href="https://addnew.biz/business-register">addnew.biz/business-register</a>.
                Потрібні email і пароль, за бажанням — назва магазину й посилання на сайт.</li>
            <li style="margin-bottom:8px;">В особистому кабінеті, у розділі «Інформація про магазин», додайте логотип, банер і опис.</li>
            @if($hasFeed)
                <li style="margin-bottom:8px;">У розділі «Імпорт товарів» вставте посилання на ваш фід для Google Merchant і підтвердіть імпорт.
                    Щоб оновити ціни й наявність, достатньо повторити імпорт.</li>
            @else
                <li style="margin-bottom:8px;">У розділі «Імпорт товарів» завантажте файл CSV або XML (формат Google Merchant Center) з полями:
                    ID товару, назва, опис, ціна з валютою, наявність, посилання на головне фото.</li>
            @endif
            <li style="margin-bottom:8px;">Перевірте статистику імпорту (скільки товарів додасться й оновиться) і підтвердіть.</li>
        </ol>

        @unless($hasFeed)
            <p style="background:#f0f7ff; border-radius:6px; padding:12px;">Якщо фіду немає, а товари ведуться в Excel чи Google Sheets — надішліть таблицю
                у відповідь на цей лист{{ $replyToAddress ? " ({$replyToAddress})" : '' }}, і ми допоможемо зробити з неї файл для імпорту.</p>
        @endunless

        <p style="text-align:center; margin:24px 0;">
            <a href="https://addnew.biz/business-register"
               style="display:inline-block; background:#1e88e5; color:#fff; text-decoration:none; padding:12px 24px; border-radius:6px; font-weight:bold;">
                Зареєструвати магазин
            </a>
        </p>

        <p>Детальна інструкція з картинками: <a href="https://addnew.biz/page/magazinam">addnew.biz/page/magazinam</a>.
            Приклади вже розміщених магазинів: <a href="https://addnew.biz/stores">addnew.biz/stores</a>.
            Якщо виникнуть запитання — просто відповідайте на цей лист.</p>

        <p style="margin-bottom:0;">З повагою,<br>команда Addnew.biz</p>
    </div>
    <p style="font-size:12px; color:#888; padding:12px 8px;">
        Ви отримали цей лист, бо під час телефонної розмови погодилися отримати інструкцію. Якщо він потрапив до вас помилково,
        відповідайте «відписатися» — і ми більше не писатимемо й не телефонуватимемо.
    </p>
</div>
</body>
</html>
