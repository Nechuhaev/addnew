{{-- Заголовок колонки з сортуванням. $route, $col, $label, $sort, $dir, $class --}}
@php
    $isSorted = $sort === $col;
    $q = array_merge(request()->except('page'), ['sort' => $col, 'dir' => $isSorted && $dir === 'asc' ? 'desc' : 'asc']);
@endphp
<th class="{{ $class ?? '' }}">
    <a href="{{ route($route) . '?' . http_build_query($q) }}" style="color: {{ $isSorted ? '#19346c; font-weight:700' : 'inherit' }};">{{ $label }}</a>
    @if($isSorted)<i class="mdi mdi-arrow-{{ $dir === 'asc' ? 'up' : 'down' }}"></i>@endif
</th>
