{{-- Пагінація + «на сторінці». $paginator, $perPageOptions --}}
<div class="st-pager">
    <div>{{ $paginator->links() }}</div>
    <form method="get" action="{{ url()->current() }}" class="d-flex align-items-center" style="gap:6px;">
        @foreach(request()->except(['page', 'per_page']) as $name => $value)
            @if(is_array($value))
                @foreach($value as $k => $v)<input type="hidden" name="{{ $name }}[{{ $k }}]" value="{{ $v }}">@endforeach
            @else
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endif
        @endforeach
        <span class="text-muted" style="font-size:13px;">Показано {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} з {{ $paginator->total() }} · на сторінці</span>
        <select name="per_page" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
            @foreach($perPageOptions as $n)
                <option value="{{ $n }}" {{ $paginator->perPage() === $n ? 'selected' : '' }}>{{ $n }}</option>
            @endforeach
        </select>
    </form>
</div>
