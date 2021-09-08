<div class="header-search">
    <div class="container">
        <form class="search" method="get" action="{{ $config['action'] }}">
            <div class="form-group">
                <input type="text" id="autocomplete_i" class="form-control" name="s" value="{{ $config['search_term'] }}" placeholder="Что ищем?">
            </div>
            <!-- <div class="search-mob">
                <div class="form-group">
                    <select class="form-control" name="cat_id" id="search_category" style="width: 100%;">
                        <option value="0">Выберите категорию</option>
                        @foreach($config['categories'] as $category)
                        <option {{ ($config['category_id'] == $category['id']) ? 'selected' : '' }} value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <select class="form-control" class="" name="sub_cat_id" id="search_sub_category" style="width: 100%;">
                        <option value="0">Искать во всей категории</option>
                        @if ($config['subcategories'])
                            @foreach($config['subcategories'] as $subcategory)
                                <option {{ ($config['subcategory_id'] == $subcategory['id']) ? 'selected' : '' }} value="{{ $subcategory['id'] }}">{{ $subcategory['name'] }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" id="autocomplete_c" value="{{ $config['city'] }}" placeholder="Горoд для поиска">
                    <input type="hidden" name="city_id" id="search_city_id" value="{{ $config['city_id'] }}">
                </div>
            </div> -->
            <div class="search-button">
                <button class="btn btn-search">Поиск</button>
                <a href="#" class="view-more">Уточнить поиск</a>
            </div>
        </form>
    </div>
</div>