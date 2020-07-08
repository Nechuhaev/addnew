<ul id="sidebarnav">
    <li class="sidebar-item">
        <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('admin.index') }}" aria-expanded="false">
            <i class="mdi mdi-av-timer"></i>
            <span class="hide-menu">Главная</span>
        </a>
    </li>
    <li class="sidebar-item">
        <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('admin.users') }}" aria-expanded="false">
            <i class="mdi mdi-face"></i>
            <span class="hide-menu">Пользователи</span>
        </a>
    </li>

    <li class="sidebar-item">
        <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
            <i class="mdi mdi-map-marker"></i>
            <span class="hide-menu">Объявления</span>
        </a>
        <ul aria-expanded="false" class="collapse first-level">
            <li class="sidebar-item">
                <a href="{{ route('admin.ads') }}" class="sidebar-link">
                    <i class="mdi mdi-format-list-bulleted"></i>
                    <span class="hide-menu">Список объявлений</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.adCategories') }}" class="sidebar-link">
                    <i class="mdi mdi-label"></i>
                    <span class="hide-menu">Категории</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.adTags') }}" class="sidebar-link">
                    <i class="mdi mdi-label-outline"></i>
                    <span class="hide-menu">Теги</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.adCountries') }}" class="sidebar-link">
                    <i class="mdi mdi-google-maps"></i>
                    <span class="hide-menu">Страны</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.adRegions') }}" class="sidebar-link">
                    <i class="mdi mdi-google-maps"></i>
                    <span class="hide-menu">Области / Регионы</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.adCities') }}" class="sidebar-link">
                    <i class="mdi mdi-city"></i>
                    <span class="hide-menu">Города</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.adCurrencies') }}" class="sidebar-link">
                    <i class="mdi mdi-currency-usd"></i>
                    <span class="hide-menu">Валюты</span>
                </a>
            </li>
        </ul>
    </li>

    <li class="sidebar-item">
        <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('admin.pages') }}" aria-expanded="false">
            <i class="mdi mdi-book-open-page-variant"></i>
            <span class="hide-menu">Страницы</span>
        </a>
    </li>
    <li class="sidebar-item">
        <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
            <i class="mdi mdi-receipt"></i>
            <span class="hide-menu">Статьи</span>
        </a>
        <ul aria-expanded="false" class="collapse first-level">
            <li class="sidebar-item">
                <a href="{{ route('admin.articles') }}" class="sidebar-link">
                    <i class="mdi mdi-format-list-bulleted"></i>
                    <span class="hide-menu">Список статей</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.article.category.index') }}" class="sidebar-link">
                    <i class="mdi mdi-label"></i>
                    <span class="hide-menu">Категории статей</span>
                </a>
            </li>

        </ul>
    </li>
    <li class="sidebar-item">
        <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('admin.seo') }}" aria-expanded="false">
            <i class="mdi mdi-textbox"></i>
            <span class="hide-menu">SEO тексты</span>
        </a>
    </li>
    <li class="sidebar-item">
        <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('admin.ad.uploader') }}" aria-expanded="false">
            <i class="mdi mdi-file-import"></i>
            <span class="hide-menu">Импорт файлов</span>
        </a>
    </li>

</ul>