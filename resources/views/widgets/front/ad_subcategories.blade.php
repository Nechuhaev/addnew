<h1 class="category-title">
    <img src="{{ $config['parent']->image }}" alt="{{ $config['parent']->name }}" class="category-img">
    <span>{{ $config['parent']->name }}</span>
</h1>
<ul class="category-submenu">
    @foreach($config['categories'] as $category)
        <li><a href="{{ $category['url'] }}">{{ $category['name'] }}</a></li>
    @endforeach
</ul>