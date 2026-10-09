{{-- Графік історії ціни. $chart — з App\AdPriceHistory::chartFor() --}}
@php
    $ru = app()->getLocale() === 'ru';
    $fmt = function ($v) { return number_format($v, $v == floor($v) ? 0 : 2, '.', ' '); };
    $dfmt = function ($d) { return \Carbon\Carbon::parse($d)->format('d.m.Y'); };
    $t0 = strtotime($chart['from']); $t1 = max(strtotime($chart['to']), $t0 + 86400);
    $span = $chart['max'] - $chart['min'];
    $lo = max(0, $chart['min'] - $span * 0.12); $hi = $chart['max'] + $span * 0.12;
    // Координати у відсотках області графіка: лінії малює SVG (preserveAspectRatio=none),
    // підписи й точки — HTML, тож текст не масштабується разом із графіком.
    $x = function ($d) use ($t0, $t1) { return round((strtotime($d) - $t0) / ($t1 - $t0) * 100, 2); };
    $y = function ($v) use ($lo, $hi) { return round(($hi - $v) / ($hi - $lo) * 100, 2); };
    $path = ''; $markers = [];
    foreach ($chart['points'] as $i => $p) {
        $px = $x($p[0]); $py = $y($p[1]);
        $path .= $i === 0 ? "M{$px},{$py}" : " H{$px} V{$py}";
        $markers[] = [$px, $py];
    }
    $path .= ' H100';
    $ticks = [$chart['max'], ($chart['min'] + $chart['max']) / 2, $chart['min']];
    $sym = $chart['symbol'];
    $jsPoints = array_map(function ($p) use ($x, $y, $dfmt, $fmt, $sym, $ru) {
        return ['x' => $x($p[0]), 'y' => $y($p[1]), 'd' => ($ru ? 'с ' : 'з ') . $dfmt($p[0]), 'v' => $fmt($p[1]) . ' ' . $sym];
    }, $chart['points']);
@endphp
<section class="price-history" aria-labelledby="price-history-h">
    <div class="price-history__head">
        <h2 id="price-history-h" class="adv-h">{{ $ru ? 'История цены' : 'Історія ціни' }}</h2>
        @if($chart['is_lowest_90'])
            <span class="price-history__badge">{{ $ru ? 'Самая низкая цена за 90 дней' : 'Найнижча ціна за 90 днів' }}</span>
        @endif
    </div>
    <ul class="price-history__stats">
        <li><span>{{ $ru ? 'Сейчас' : 'Зараз' }}</span><strong>{{ $fmt($chart['current']) }} {{ $sym }}</strong></li>
        <li><span>{{ $ru ? 'Минимум' : 'Мінімум' }}</span><strong>{{ $fmt($chart['min']) }} {{ $sym }}</strong></li>
        <li><span>{{ $ru ? 'Максимум' : 'Максимум' }}</span><strong>{{ $fmt($chart['max']) }} {{ $sym }}</strong></li>
    </ul>
    <div class="price-history__chart">
        <div class="ph-y" aria-hidden="true">
            @foreach($ticks as $tick)
                <span style="top:{{ $y($tick) }}%">{{ $fmt(round($tick)) }}</span>
            @endforeach
        </div>
        <div class="ph-plot" tabindex="0" role="img"
             aria-label="{{ ($ru ? 'График цены с ' : 'Графік ціни з ') . $dfmt($chart['from']) }}: {{ $fmt($chart['min']) }}–{{ $fmt($chart['max']) }} {{ $sym }}"
             data-points='@json($jsPoints)'>
            <svg viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                @foreach($ticks as $tick)
                    <line x1="0" x2="100" y1="{{ $y($tick) }}" y2="{{ $y($tick) }}" class="ph-grid" vector-effect="non-scaling-stroke"/>
                @endforeach
                <path d="{{ $path }}" class="ph-line" vector-effect="non-scaling-stroke"/>
            </svg>
            @foreach($markers as $m)
                <i class="ph-dot" style="left:{{ $m[0] }}%;top:{{ $m[1] }}%"></i>
            @endforeach
            <i class="ph-cross" hidden></i>
            <i class="ph-dot ph-dot--active" hidden></i>
            <div class="price-history__tip" role="status" aria-live="polite" hidden><strong></strong><span></span></div>
        </div>
        <div class="ph-x" aria-hidden="true">
            <span>{{ $dfmt($chart['from']) }}</span>
            <span>{{ $ru ? 'сегодня' : 'сьогодні' }}</span>
        </div>
    </div>
    <details class="price-history__table">
        <summary>{{ $ru ? 'Все изменения цены' : 'Усі зміни ціни' }}</summary>
        <table>
            <thead><tr><th>{{ $ru ? 'Дата' : 'Дата' }}</th><th>{{ $ru ? 'Цена' : 'Ціна' }}</th></tr></thead>
            <tbody>
                @foreach(array_reverse($chart['points']) as $p)
                    <tr><td>{{ $dfmt($p[0]) }}</td><td>{{ $fmt($p[1]) }} {{ $sym }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </details>
</section>
<script>
(function () {
    var plot = document.currentScript.previousElementSibling.querySelector('.ph-plot');
    var tip = plot.querySelector('.price-history__tip'), cross = plot.querySelector('.ph-cross');
    var dot = plot.querySelector('.ph-dot--active');
    var pts = JSON.parse(plot.getAttribute('data-points')), idx = pts.length - 1;

    function show(i) {
        idx = i;
        var p = pts[i];
        cross.style.left = dot.style.left = p.x + '%';
        dot.style.top = p.y + '%';
        cross.hidden = dot.hidden = tip.hidden = false;
        tip.querySelector('strong').textContent = p.v;
        tip.querySelector('span').textContent = p.d;
        var w = plot.clientWidth, left = p.x / 100 * w - tip.offsetWidth / 2;
        tip.style.left = Math.max(0, Math.min(w - tip.offsetWidth, left)) + 'px';
    }
    function hide() { cross.hidden = dot.hidden = tip.hidden = true; }
    function at(clientX) {
        var r = plot.getBoundingClientRect(), x = (clientX - r.left) / r.width * 100, i = 0;
        for (var k = 0; k < pts.length; k++) { if (pts[k].x <= x) i = k; }
        return i;
    }
    plot.addEventListener('pointermove', function (e) { show(at(e.clientX)); });
    plot.addEventListener('pointerdown', function (e) { show(at(e.clientX)); });
    plot.addEventListener('pointerleave', function (e) { if (e.pointerType === 'mouse') hide(); });
    plot.addEventListener('focus', function () { show(idx); });
    plot.addEventListener('blur', hide);
    plot.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft' && idx > 0) { show(idx - 1); e.preventDefault(); }
        if (e.key === 'ArrowRight' && idx < pts.length - 1) { show(idx + 1); e.preventDefault(); }
    });
})();
</script>
