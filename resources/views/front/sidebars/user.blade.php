<aside class="column-right">
    <h2 class="account-h2">Личный кабинет</h2>
    <ul class="account-menu">
        @is_shop_owner
            <li><a href="{{ route('profile.ads') }}">Мои товары</a></li>
            <li><a href="{{ route('profile.shop') }}">Импорт/экспорт товаров</a></li>
            <li><a href="{{ route('profile.shop.info') }}">Информация о магазине</a></li>
            <li><a href="{{ route('profile.password') }}">Изменить пароль</a></li>
        @else
            <li><a href="{{ route('profile.ads') }}">Мои объявления</a></li>
            <li><a href="{{ route('profile.index') }}">Редактировать профиль</a></li>
            <li><a href="{{ route('profile.password') }}">Изменить пароль</a></li>
        @endis_shop_owner

        <li><a href="#">Выход</a></li>
    </ul>
    <h2 class="account-h2">Информация об учётной записи</h2>
    <div class="account-author author">
        <div class="author-photo">
            <img alt="аватар профиля" src="{{ Auth::user()->image ?? 'https://secure.gravatar.com/avatar/f17c59914122f91f742418889e41b124?s=250&amp;d=mm&amp;r=g' }}" class="author-avatar" height="250" width="250">
        </div>
        <ul class="author-info">
            <li><strong>{{ Auth()->user()->email }}</strong></li>
            <li><strong>Дата регистрации:</strong> {{ Auth()->user()->created_at->format('d.m.Y H:m') }}</li>
{{--            <li><strong>Тип учетной записи:</strong> @if(Auth()->user()->is_shop_owner) магазин @else физ. лицо @endif</li>--}}
        </ul>
    </div>

    <div style="display: block;min-height: 1px;padding-top: 30px;"></div>

    <h2 class="account-h2">Статистика учётной записи</h2>
    <ul class="account-info">
        <li>Активный объявлений: <strong>{{ Auth()->user()->ads()->where('status', '1')->count() }}</strong></li>
        <li>Объявлений в ожидании: <strong>{{ Auth()->user()->ads()->where('status', '0')->count() }}</strong></li>
        <li>Неактивных объявлений: <strong>{{ Auth()->user()->ads()->where('status', '2')->count() }}</strong></li>
        <li>Всего объявлений: <strong>{{ Auth()->user()->ads()->count() }}</strong></li>
    </ul>

    <h2 class="account-h2">Для интернет-магазинов</h2>
    <div class="shop-profile-widget">
        <p>Если у Вас есть собственный интернет-магазин, можно опубликовать информацию о товарах в несколько шагов: </p>
        <ol>
            <li>Переключите тип профиля на "интернет-магазин"</li>
            <li>Загрузите прайс-лист в соответсвующем разделе личного кабинета</li>
            <li>Информация о Ваших товарах появится на addnew.biz в течении 3 рабочих дней</li>
        </ol>
        <a href="{{ url('/profile/type') }}">Изменить тип профиля</a>

    </div>

</aside>