@if (count($breadcrumbs))



        <a itemprop="item" href="https://example.com/books">
            <span itemprop="name">Books</span></a>
        <meta itemprop="position" content="1" />


    <ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
        @foreach ($breadcrumbs as $breadcrumb)

            @if ($breadcrumb->url && !$loop->last)
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="breadcrumb-item">
                    <a itemprop="item" href="{{ $breadcrumb->url }}">
                        <span itemprop="name">{{ $breadcrumb->title }}</span>
                    </a>
                    <meta itemprop="position" content="{{ $loop->iteration }}" />
                </li>
            @else
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="breadcrumb-item active">
                    <span itemprop="name">{{ $breadcrumb->title }}</span>
                    <meta itemprop="position" content="{{ $loop->iteration }}" />
                </li>
            @endif

        @endforeach
    </ol>

@endif