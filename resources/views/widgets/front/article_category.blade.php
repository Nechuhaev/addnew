@if($config['categories'])
<div class="blog-categories">
    <h2 class="blog-h2">Рубрики блога</h2>

    <ul class="blog-list-category">
        @foreach($config['categories'] as $category)
        <li class="">
            <a href="{{ $category['href'] }}">{{ $category['name'] }}</a> ({{ $category['posts_count'] }})
        </li>
        @endforeach
    </ul>
</div>
@endif