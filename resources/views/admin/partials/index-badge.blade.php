{{-- Статус індексації Google одного рядка ($row — запис seo_index_status або null) --}}
@php([$idxLabel, $idxClass, $idxHint] = \App\Services\Seo\IndexStatus::label($row ?? null))
<span class="badge badge-{{ $idxClass }}" title="{{ $idxHint }}" style="white-space: nowrap">{{ $idxLabel }}</span>
