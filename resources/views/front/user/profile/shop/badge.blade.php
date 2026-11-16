{{-- Бейдж «Ми на Addnew.biz» для сайту магазину: посилання на сторінку магазину + код для вставки --}}
@php
    $ru = app()->getLocale() === 'ru';
    $badgeLang = $ru ? 'ru' : 'uk';
    // Код вставляють на чужі сайти — завжди https, незалежно від того, як прийшов поточний запит
    $https = function ($url) { return preg_replace('~^http://~', 'https://', $url); };
    $shopUrl = $https(route('author', ['id' => auth()->id()]));
    $badgeAlt = $ru ? 'Мы на Addnew.biz — бесплатная доска объявлений Украины' : 'Ми на Addnew.biz — безкоштовна дошка оголошень України';
    $badgeCode = function ($theme) use ($shopUrl, $badgeLang, $badgeAlt, $https) {
        return '<a href="' . $shopUrl . '" target="_blank" rel="noopener" title="' . e($badgeAlt) . '">'
            . '<img src="' . $https(asset('assets/front/img/badge/addnew-' . $badgeLang . '-' . $theme . '.svg')) . '" alt="' . e($badgeAlt) . '" width="180" height="50" loading="lazy"></a>';
    };
@endphp
<div class="shop-badge-card" style="border:1px solid #e3e4e7;border-radius:8px;padding:16px;margin:20px 0;background:#fff">
    <h2 style="margin:0 0 6px;font-size:18px">{{ $ru ? 'Получите больше покупателей: добавьте бейдж на свой сайт' : 'Отримайте більше покупців: додайте бейдж на свій сайт' }}</h2>
    <p style="margin:0 0 12px;color:#5a6478;line-height:1.5">
        {{ $ru
            ? 'Бейдж ведёт на страницу вашего магазина на Addnew.biz с отзывами и всеми товарами. Он показывает покупателям, что вас можно найти на доске объявлений, и помогает вашей странице подниматься в поиске. Вставьте код в подвал или на страницу «Контакты» своего сайта.'
            : 'Бейдж веде на сторінку вашого магазину на Addnew.biz з відгуками й усіма товарами. Він показує покупцям, що вас можна знайти на дошці оголошень, і допомагає вашій сторінці підніматися в пошуку. Вставте код у підвал або на сторінку «Контакти» свого сайту.' }}
    </p>
    @foreach(['light' => $ru ? 'Светлый' : 'Світлий', 'dark' => $ru ? 'Тёмный' : 'Темний'] as $theme => $label)
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:12px">
            <div style="flex:0 0 auto;padding:8px;background:{{ $theme === 'dark' ? '#f4f5f7' : '#f4f5f7' }};border-radius:6px">{!! $badgeCode($theme) !!}</div>
            <div style="flex:1 1 260px;min-width:0">
                <label style="display:block;font-size:13px;color:#5a6478;margin-bottom:4px">{{ $label }} — {{ $ru ? 'код для вставки' : 'код для вставки' }}</label>
                <textarea readonly rows="3" onclick="this.select()" style="width:100%;font-family:monospace;font-size:12px;resize:vertical;box-sizing:border-box">{{ $badgeCode($theme) }}</textarea>
                <button type="button" class="btn-form" style="margin-top:6px;padding:6px 12px"
                        onclick="var t=this.previousElementSibling;t.select();try{navigator.clipboard?navigator.clipboard.writeText(t.value):document.execCommand('copy')}catch(e){document.execCommand('copy')}this.textContent='{{ $ru ? 'Скопировано' : 'Скопійовано' }}'">{{ $ru ? 'Скопировать код' : 'Скопіювати код' }}</button>
            </div>
        </div>
    @endforeach
</div>
