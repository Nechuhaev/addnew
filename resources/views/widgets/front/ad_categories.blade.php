@if($config['categories'])
<div class="categories">
    <h2 class="category-title">{{ $config['heading'] }}</h2>
    <ul class="category-list">
        @foreach($config['categories'] as $category)
        <li><a href="{{ $category['url'] }}">
                <img src="{{ $category['image'] }}" alt="{{ $category['name'] }}" class="catalog-img"> <span>{{ $category['name'] }}</span>
            </a>
        </li>
        @endforeach
    </ul>
</div>
@endif