{{-- Пагінація + «на сторінці». $paginator, $route, $perPageOptions --}}
<div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:10px;">
    <div>{{ $paginator->links() }}</div>
    <form method="get" action="{{ route($route) }}" class="d-flex align-items-center" style="gap:6px;">
        @foreach(request()->except(['page', 'per_page']) as $name => $value)
            @if(is_array($value))
                @foreach($value as $k => $v)<input type="hidden" name="{{ $name }}[{{ $k }}]" value="{{ $v }}">@endforeach
            @elseif($value !== null && $value !== '')
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endif
        @endforeach
        <span class="text-muted" style="font-size:13px;">на сторінці</span>
        <select name="per_page" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
            @foreach($perPageOptions as $n)
                <option value="{{ $n }}" {{ $paginator->perPage() === $n ? 'selected' : '' }}>{{ $n }}</option>
            @endforeach
        </select>
    </form>
</div>
<style>
    .geo-filter th { background:#f8f9fa; padding:.3rem .4rem; }
    .geo-filter input, .geo-filter select { width:100%; min-width:55px; height:28px; padding:2px 6px; font-size:12px; }
    .geo-count a { font-weight:600; }
</style>
<script>
    (function () {
        var f = document.getElementById('geo-filters');
        if (f) f.addEventListener('submit', function () {
            this.querySelectorAll('input, select').forEach(function (el) { if (el.name && el.value === '') el.disabled = true; });
        });
    })();
</script>
