<div class="columns columns-nowrap empty-category">
    <aside class="column-left hidden-xs">
        <img src="https://cdn.pixabay.com/photo/2018/05/22/18/26/robot-3422113_960_720.png" style="max-width: 100%" alt="{{ __('front.robot_image_alt') }}">
    </aside>
    <div class="column-content">
        <p class="heading">{{ __('front.empty_category_heading') }}</p>
        <p>{{ __('front.empty_category_text1') }}</p>
        <p>{{ __('front.empty_category_text2') }}</p>
        <a class="btn btn-default" href="{{ route('ad.step.category') }}">{{ __('front.post_ad') }}</a>
    </div> <!-- column-content -->
</div>
<div class="popular-tags">
    <h2>{{ __('front.popular_tags_heading') }}</h2>
    <p>{{ __('front.popular_tags_subtitle') }}</p>
    @foreach($tags as $tag)
        @if($tag->name)
        <a href="{{ $tag->url }}">{{ $tag->name }}</a>
        @endif
    @endforeach
</div>
@if ($ads)
<div class="recent-products">
    <h2>{{ __('front.recent_ads_heading') }}</h2>
    <p>{{ __('front.recent_ads_subtitle') }}</p>
    @foreach($ads as $group)
        <div class="last-advs" {!!  ($loop->iteration != 1) ? 'style="border:none;"' : ''  !!}>
            @foreach($group as $ad)
                <a href="{{ $ad['url'] }}">
                        <span class="last-adv-title">
                            <img src="{{ $ad['image'] }}" alt="{{ $ad['name'] }}">
                            <strong> {{ $ad['name'] }}</strong>
                        </span>
                    <span class="last-adv-price"> {{ $ad['price'] }} </span>
                </a>
            @endforeach
        </div>
    @endforeach
</div>
@endif