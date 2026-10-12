{{-- Періоди статистики + кількість на сторінці. $period (StatsPeriod), $perPageOptions --}}
@php
    $periodUrl = function ($key) {
        $q = request()->except(['page', 'period', 'from', 'to']);
        if ($key !== \App\Services\StatsPeriod::DEFAULT) { $q['period'] = $key; }
        return url()->current() . ($q ? '?' . http_build_query($q) : '');
    };
@endphp
<div class="st-toolbar">
    <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Період">
        @foreach(\App\Services\StatsPeriod::PRESETS as $key => $label)
            <a href="{{ $periodUrl((string) $key) }}" class="btn {{ $period->key === (string) $key ? 'btn-primary' : 'btn-outline-primary' }}">{{ $label }}</a>
        @endforeach
    </div>
    <form method="get" action="{{ url()->current() }}" class="st-range">
        @foreach(request()->except(['page', 'period', 'from', 'to']) as $name => $value)
            @if(is_array($value))
                @foreach($value as $k => $v)<input type="hidden" name="{{ $name }}[{{ $k }}]" value="{{ $v }}">@endforeach
            @else
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endif
        @endforeach
        <input type="date" name="from" value="{{ $period->key === 'custom' ? optional($period->from)->toDateString() : '' }}" class="form-control form-control-sm" aria-label="З дати">
        <span>—</span>
        <input type="date" name="to" value="{{ $period->key === 'custom' ? optional($period->to)->toDateString() : '' }}" class="form-control form-control-sm" aria-label="По дату">
        <button type="submit" class="btn btn-sm btn-secondary">Показати</button>
    </form>
    <span class="st-period-label text-muted">Період: <b>{{ $period->label() }}</b></span>
</div>
