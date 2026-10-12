{{-- Заголовок колонки з сортуванням. $col, $label, $sort, $dir, $class --}}
@php
    $isSorted = $sort === $col;
    $nextDir = $isSorted && $dir === 'desc' ? 'asc' : 'desc';
    $q = array_merge(request()->except('page'), ['sort' => $col, 'dir' => $nextDir]);
@endphp
<th class="{{ $class ?? '' }}" @if(!empty($title)) title="{{ $title }}" @endif @if($isSorted) aria-sort="{{ $dir === 'asc' ? 'ascending' : 'descending' }}" @endif>
    <a href="{{ url()->current() . '?' . http_build_query($q) }}" class="{{ $isSorted ? 'is-sorted' : '' }}">{{ $label }}@if($isSorted) {!! $dir === 'asc' ? '&#9650;' : '&#9660;' !!}@endif</a>
</th>
