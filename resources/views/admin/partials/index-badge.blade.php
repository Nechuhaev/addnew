{{-- Статус індексації Google одного рядка ($row — запис seo_index_status або null; $type/$id — щоб позначити «закрито нами») --}}
@php([$idxLabel, $idxClass, $idxHint] = \App\Services\Seo\IndexStatus::label($row ?? null, isset($type, $id) && \App\Services\Seo\IndexStatus::isOurs($type, (int) $id)))
<span class="badge badge-{{ $idxClass }}" title="{{ $idxHint }}" style="white-space: nowrap{{ $idxClass === 'light' ? ';border:1px solid #ccc' : '' }}">{{ $idxLabel }}</span>
