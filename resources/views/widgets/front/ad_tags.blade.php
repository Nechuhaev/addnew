@if($config['tags'] && $config['output'])

<div class="widget-tag-cloud">
    <p class="tag-h">{{ $config['heading'] }}</p>
    <div>
        @foreach($config['output'] as $tag)
        <a href="{{ $tag['url'] }}" class="tag-cloud-link tag-link-position-{{ $loop->iteration }} cp-fixed-color" style="font-size: 13pt;" aria-label="метка {{ $tag['name'] }}">{{ $tag['name'] }}</a>
        @endforeach
    </div>
</div>
@endif