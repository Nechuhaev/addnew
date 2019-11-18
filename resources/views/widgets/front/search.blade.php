<div class="header-search">
    <div class="container">
        <form class="search">
            <div class="form-group">
                <input type="text" class="form-control" name="s" placeholder="Что ищем?">
            </div>
            <div class="search-mob">
                <div class="form-group">
                    <select class="form-control" name="cat_id" id="main_category" style="width: 100%;">
                        <option value="0">Выберите категорию</option>
                        @foreach($config['categories'] as $category)
                        <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <select class="form-control" class="" name="sub_cat_id" id="hub_sub_category" style="width: 100%;">
                        <option value="0">Искать во всей категории</option>
                        <option value="880">Строительные материалы</option>
                        <option value="881">Отделочные и облицовочные материалы</option>
                        <option value="882">Окна</option>
                        <option value="883">Двери</option>
                        <option value="884">Замки и фурнитура</option>
                        <option value="885">Балконы</option>
                        <option value="886">Лестницы</option>
                        <option value="887">Ворота и заборы</option>
                        <option value="888">Сантехника</option>
                        <option value="889">Отопление</option>
                        <option value="890">Электрика</option>
                        <option value="892">Насосы</option>
                        <option value="891">Вентиляционные системы</option>
                        <option value="893">Готовые конструкции</option>
                        <option value="894">Металлоконструкции</option>
                        <option value="895">Инструменты</option>
                        <option value="896">Другое</option>
                        <option value="897">Аренда</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" id="autocomplete_cities" placeholder="Город для поиска">
                </div>
            </div>
            <div class="search-button">
                <button type="button" class="btn btn-search">Поиск</button>
                <a href="#" class="view-more">Уточнить поиск</a>
            </div>
        </form>
    </div>
</div>