<div class="columns columns-nowrap empty-category">
    <aside class="column-left hidden-xs">
        <img src="https://cdn.pixabay.com/photo/2018/05/22/18/26/robot-3422113_960_720.png" style="max-width: 100%" alt="изображение робота">
    </aside>
    <div class="column-content">
        <p class="heading">А уже все продано!</p>
        <p>Сейчас на сайте нет товаров или услуг, соответствующих Вашему запросу.</p>
        <p>Кстати, Вы можете стать первым в этой рубрике. Просто разместите объявление.</p>
        <a class="btn btn-default" href="{{ route('ad.step.category') }}">Подать объявление</a>
    </div> <!-- column-content -->
</div>
<div class="popular-tags">
    <h2>Популярные запросы</h2>
    <p>Самые популярные запросы пользователей нашего сайта</p>
    @foreach($tags as $tag)
        @if($tag->name)
        <a href="{{ $tag->url }}">{{ $tag->name }}</a>
        @endif
    @endforeach
</div>

@if ($ads)
<div class="recent-products">
    <h2>Последние добавленые</h2>
    <p>Объявления, которые были добавлены недавно</p>

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
