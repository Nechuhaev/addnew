<div class="category">
    @if($ads)
        @foreach($ads as $ad)
            <div class="category-item">
                <div class="category-count">{{ $loop->iteration }}</div>
                <div class="category-img">
                    <a href="{{ $ad['url'] }}" title="{{ $ad['name'] }}" class="preview" data-rel="{{ $ad['image'] }}">
                        <img width="250" height="250" src="{{ $ad['image'] }}" class="attachment-ad-medium size-ad-medium" alt="{{ $ad['name'] }}">
                    </a>
                </div>
                <div class="category-caption">
                    <a href="{{ $ad['url'] }}">{{ $ad['name'] }}</a>
                    <p class="category-description">{{ $ad['content'] }}</p>
                    <p class="category-meta">
                        <i class="st-1"><strong>Размещено:</strong><span class="st-1">{{ $ad['date_active'] }}</span></i>
                        <i class="st-1"><strong>Страна:</strong><span class="st-1"><a href="{{ $ad['country_url'] }}">{{ $ad['country'] }}</a></span></i>
                        <i class="st-1"><strong>Город:</strong><span class="st-1"><a href="{{ $ad['city_url'] }}">{{ $ad['city'] }}</a></span></i>
                    </p>
                </div>
                <div class="category-price">
                    <strong>{{ $ad['price'] }}</strong>
                </div>
            </div>
        @endforeach
    @else
        <p>Объявлений не найдено</p>
    @endif
</div>
