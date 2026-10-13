{{-- ItemList JSON-LD для сторінок-списків: $ads — Ad::getLoopArray(), $perPage — розмір сторінки --}}
@if(!empty($ads))
    @php
        $ilPage = max(1, (int) request()->get('page'));
        $ilStart = ($ilPage - 1) * ($perPage ?? 15);
        $ilItems = [];
        foreach (array_values((array) $ads) as $ilI => $ilAd) {
            if (!empty($ilAd['url'])) {
                $ilItems[] = ['@type' => 'ListItem', 'position' => $ilStart + $ilI + 1, 'url' => $ilAd['url'], 'name' => (string) ($ilAd['name'] ?? '')];
            }
        }
    @endphp
    @if($ilItems)
        <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'ItemList', 'numberOfItems' => count($ilItems), 'itemListElement' => $ilItems], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @endif
@endif
