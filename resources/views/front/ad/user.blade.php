@extends('front.layout')

@section('meta_title', $meta['meta_title'] ?? $entity->name)

@section('meta_description', $meta['meta_description'] ?? $entity->content)

@section('og_image', (string) ($entity->image ?? ''))
@section('og_card', !empty($entity->image) ? 'summary' : '')

@section('style')
    @if(empty($ads) || $term !== '' || $listingFilters->isActive())
        <meta name="robots" content="noindex, follow" />
    @endif
@endsection

@section('content')

    <main class="category-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render($breadcrumbs, $entity) }}

            <h1 style="font-size: 20px; font-weight: 900; margin: 1em 0;">{{ __('front.all_ads_by_user') }} {{ $entity->username }}</h1>
            @auth
                @if(auth()->id() != $entity->id)
                    <form action="{{ route('chat.start', $entity->id) }}" method="POST" style="display:inline-block; margin-bottom:10px;">
                        @csrf
                        <button type="submit" class="btn btn-success">Написати продавцю</button>
                    </form>
                @endif
            @endauth
            

            <div class="author" style="display:flex; flex-wrap:wrap; gap: 20px; align-items:flex-start;">
                <div class="author-photo"><img alt="Пользователь {{ $entity->username }}" src="{{ $entity->image ?? asset('assets/front/img/placeholder.png') }}" class="author-avatar" height="250" width="250"></div>
                <div class="author-details" style="flex: 1 1 250px; min-width: 250px;">
                    @if($is_shop)
                        <div class="author-info"><strong>{{ __('front.offers_count') }} </strong> {{ $entity->ads()->count() }}</div>
                        <div class="author-info">
                            <strong>Рейтинг:</strong>
                            @if($avgRating)
                                <span style="color:#f5a623;">{{ str_repeat('★', round($avgRating)) }}{{ str_repeat('☆', 5 - round($avgRating)) }}</span>
                                {{ $avgRating }} / 5 ({{ $reviews->count() }} {{ $reviews->count() == 1 ? 'відгук' : 'відгуків' }})
                            @else
                                <span style="color:#888;">немає відгуків</span>
                            @endif
                        </div>
                        @if($entity->telephone)
                        <div class="author-info"><strong>{{ __('front.phone_label') }} </strong> {{ $entity->telephone }}</div>
                        @endif
                        @if($entity->email)
                            <div class="author-info">
                                <strong>{{ __('front.email_label') }}</strong> {{ $entity->email }}
                                @if($emailVerified)
                                    <span style="display:inline-block; background-color:#28a745; color:#fff; font-weight:700; font-size:12px; padding:3px 10px; border-radius:4px; margin-left:8px; vertical-align:middle; white-space:nowrap;">&#10003; Підтверджено</span>
                                @endif
                            </div>
                        @endif
                        @if($entity->site_url)
                            <div class="author-info"><strong>{{ __('front.site_label') }} </strong> {{ $entity->site_url }}</div>
                        @endif

                    @else
                    <div class="author-info"><strong>{{ __('front.registration_date_label') }}</strong> {{ $entity->created_at }}</div>
                    <div class="author-info"><strong>{{ __('front.total_ads_by_author') }}</strong> {{ $entity->ads()->count() }}</div>
                    @endif
                    @if ($entity->info)
                        <div class="author-description">
                            <h3>{{ __('front.description_heading') }}</h3>
                            <div class="show-more show-more--desktop-full">
                                <section class="show-more__text">
                                    <p>{{ $entity->info }}</p>
                                </section>
                                <div class="show-more__shadow"></div>
                                <span class="show-more__btn btn-show">{{ app()->getLocale() === 'ru' ? 'Показать' : 'Показати' }}</span>
                            </div>
                        </div>
                    @endif
                </div>
                @if($is_shop && $entity->banner)
                    @php
                        $bannerUrl = strpos($entity->banner, 'http') === 0 ? $entity->banner : asset($entity->banner);
                    @endphp
                    <div class="author-banner" style="flex: 1 1 320px; max-width: 420px; height: 150px; overflow:hidden; border-radius:6px;">
                        <img src="{{ $bannerUrl }}" alt="Баннер магазина {{ $entity->username }}" style="width:100%; height:100%; object-fit:cover; display:block;">
                    </div>
                @endif
            </div>

            @if($is_shop && ($shopDelivery = \App\Services\ShopDelivery::forShop($entity)))
                @include('front.partials.shop-delivery', ['delivery' => $shopDelivery])
            @endif

            <hr>

            <div class="columns columns-nowrap">
                @if($microdata)
                    <div itemtype="http://schema.org/AggregateOffer" itemscope itemprop="offers">
                        <meta content="{{ $microdata->ads_count }}" itemprop="offerCount">
                        <meta content="{{ $microdata->max }}" itemprop="highPrice">
                        <meta content="{{ $microdata->min }}" itemprop="lowPrice">
                        <meta content="UAH" itemprop="priceCurrency">
                    </div>
                @endif
                <div class="column-content">
                    <div class="banner">
                        @include('front.adsense.top-listing')
                    </div>

                    @php($ru = app()->getLocale() === 'ru')
                    <form class="in-shop-search" method="get" action="{{ url()->current() }}" role="search">
                        <label for="in-shop-q" class="in-shop-search__label">{{ $is_shop ? ($ru ? 'Поиск в товарах магазина' : 'Пошук у товарах магазину') : ($ru ? 'Поиск в объявлениях автора' : 'Пошук в оголошеннях автора') }}</label>
                        <div class="in-shop-search__row">
                            <input type="search" id="in-shop-q" name="s" value="{{ $term }}" maxlength="100" placeholder="{{ $ru ? 'Название, бренд или артикул' : 'Назва, бренд або артикул' }}">
                            <button type="submit" class="btn">{{ $ru ? 'Найти' : 'Знайти' }}</button>
                        </div>
                        @if($term !== '')
                            <a href="{{ url()->current() }}" class="in-shop-search__reset">{{ $ru ? 'Сбросить поиск' : 'Скинути пошук' }}</a>
                        @endif
                    </form>

                    @include('front.widgets.listing-filters', ['hideSeller' => true])

                    @if($total === 0 && $term !== '')
                        @include('front.partials.search-empty', ['listingFilters' => $listingFilters])
                    @endif

                    @include('front.loop.ads', ['ads' => $ads])

                    @if($is_shop)
                        <div style="margin: 25px 0; padding: 20px; background:#f9f9f9; border-radius:8px; box-sizing:border-box;">
                            <h3 style="margin-top:0;">Рейтинг магазину</h3>
                            @if($avgRating)
                                <div style="font-size:22px; color:#f5a623; font-weight:700; margin-bottom:5px;">
                                    {{ str_repeat('★', round($avgRating)) }}{{ str_repeat('☆', 5 - round($avgRating)) }}
                                    <span style="font-size:16px; color:#555; font-weight:400;">{{ $avgRating }} / 5 ({{ $reviews->count() }} {{ $reviews->count() == 1 ? 'відгук' : 'відгуків' }})</span>
                                </div>
                            @else
                                <p style="color:#888; margin-bottom:0;">Поки що немає відгуків</p>
                            @endif

                            @auth
                                @if(auth()->id() != $entity->id)
                                    <div style="margin-top:20px; padding:15px; background:#fff; border:1px solid #e0e0e0; border-radius:6px; box-sizing:border-box;">
                                        <strong>{{ $myReview ? 'Змінити ваш відгук' : 'Залишити відгук' }}</strong>
                                        <form action="{{ route('shop.review.store', $entity->id) }}" method="POST" style="margin-top:12px;">
                                            @csrf
                                            <div style="margin-bottom:12px;">
                                                <select name="rating" class="form-control" required style="width:100%; max-width:220px; box-sizing:border-box;">
                                                    <option value="">Оцінка</option>
                                                    @for($i = 5; $i >= 1; $i--)
                                                        <option value="{{ $i }}" {{ optional($myReview)->rating == $i ? 'selected' : '' }}>{{ $i }} {{ $i == 1 ? 'зірка' : ($i < 5 ? 'зірки' : 'зірок') }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <textarea name="comment" rows="3" class="form-control" placeholder="Коментар (необов'язково)" style="width:100%; box-sizing:border-box; margin-bottom:12px;">{{ old('comment', optional($myReview)->comment) }}</textarea>
                                            <button type="submit" class="btn btn-success" style="width:100%; max-width:220px;">{{ $myReview ? 'Оновити відгук' : 'Надіслати відгук' }}</button>
                                        </form>
                                    </div>
                                @endif
                            @else
                                <p style="margin-top:15px; margin-bottom:0;"><a href="{{ route('login') }}">Увійдіть</a>, щоб залишити відгук.</p>
                            @endauth

                            @if($reviews->isNotEmpty())
                                <div style="margin-top:25px;">
                                    <h4>Відгуки ({{ $reviews->count() }})</h4>
                                    @foreach($reviews as $review)
                                        <div style="border-bottom:1px solid #e5e5e5; padding:12px 0;">
                                            <div style="display:flex; flex-wrap:wrap; align-items:center; gap:8px;">
                                                <strong>{{ optional($review->reviewer)->username ?? 'Користувач' }}</strong>
                                                <span style="color:#f5a623;">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                                <span style="color:#999; font-size:13px;">{{ $review->created_at->format('d.m.Y') }}</span>
                                            </div>
                                            @if($review->comment)
                                                <p style="margin:8px 0 0; word-break:break-word;">{{ $review->comment }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    {!!  $links  !!}

                    <div class="banner">
                        @include('front.adsense.bottom-listing')
                    </div>


                    @if($meta['description'])
                        <div class="show-more">
                            <section class="show-more__text">
                                {!! $meta['description']  !!}
                            </section>
                            <div class="show-more__shadow"></div>
                            <span class="show-more__btn btn-show">{{ app()->getLocale() === 'ru' ? 'Показать' : 'Показати' }}</span>
                        </div>
                    @endif

                </div>


                <aside class="column-right">
                    @widget('front.adCategories', ['heading' => $entity->name, 'filter' => $entity->slug])
                    <div class="banner">
                        @include('front.adsense.category-right')
                    </div>
                    @if(isset($tags))
                        @widget('front.adTags', ['tags' => $tags])
                    @endif
                </aside>
            </div>

        </div>

    </main>


@endsection