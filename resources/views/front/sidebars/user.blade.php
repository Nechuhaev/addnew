<aside class="column-right">
    <h2 class="account-h2">{{ __('sidebar.account_heading') }}</h2>
    <ul class="account-menu">
        @is_shop_owner
            <li><a href="{{ route('profile.shop.dashboard') }}">{{ __('front.my_shop') }}</a></li>
            <li><a href="{{ route('profile.shop') }}">{{ __('shop.import_export_heading') }}</a></li>
            <li><a href="{{ route('profile.shop.info') }}">{{ __('shop.info_heading') }}</a></li>
            <li><a href="{{ route('profile.password') }}">{{ __('profile.password_heading') }}</a></li>
        @else
            <li><a href="{{ route('profile.ads') }}">{{ __('profile.my_ads_heading') }}</a></li>
            <li><a href="{{ route('profile.index') }}">{{ __('profile.edit_heading') }}</a></li>
            <li><a href="{{ route('profile.password') }}">{{ __('profile.password_heading') }}</a></li>
        @endis_shop_owner
        <li>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" style="background:none;border:none;padding:0;cursor:pointer;font:inherit;color:inherit;">{{ __('sidebar.logout_button') }}</button>
            </form>
        </li>
    </ul>
    <h2 class="account-h2">{{ __('sidebar.account_info_heading') }}</h2>
    <div class="account-author author">
        <div class="author-photo">
            <img alt="{{ __('sidebar.avatar_alt') }}" src="{{ Auth::user()->image ?? 'https://secure.gravatar.com/avatar/f17c59914122f91f742418889e41b124?s=250&amp;d=mm&amp;r=g' }}" class="author-avatar" height="250" width="250">
        </div>
        <ul class="author-info">
            <li><strong>{{ Auth()->user()->email }}</strong></li>
            <li><strong>{{ __('sidebar.registration_date_label') }}</strong> {{ Auth()->user()->created_at->format('d.m.Y H:m') }}</li>
{{--            <li><strong>Тип учетной записи:</strong> @if(Auth()->user()->is_shop_owner) магазин @else физ. лицо @endif</li>--}}
        </ul>
    </div>
    <div style="display: block;min-height: 1px;padding-top: 30px;"></div>
    <h2 class="account-h2">{{ __('sidebar.account_stats_heading') }}</h2>
    <ul class="account-info">
        <li>{{ __('sidebar.active_ads_label') }} <strong>{{ Auth()->user()->ads()->where('status', '1')->count() }}</strong></li>
        <li>{{ __('sidebar.pending_ads_label') }} <strong>{{ Auth()->user()->ads()->where('status', '0')->count() }}</strong></li>
        <li>{{ __('sidebar.inactive_ads_label') }} <strong>{{ Auth()->user()->ads()->where('status', '2')->count() }}</strong></li>
        <li>{{ __('sidebar.total_ads_label') }} <strong>{{ Auth()->user()->ads()->count() }}</strong></li>
    </ul>
    <h2 class="account-h2">{{ __('sidebar.for_shops_heading') }}</h2>
    <div class="shop-profile-widget">
        <p>{{ __('sidebar.shop_widget_intro') }}</p>
        <ol>
            <li>{{ __('sidebar.shop_widget_step1') }}</li>
            <li>{{ __('sidebar.shop_widget_step2') }}</li>
            <li>{{ __('sidebar.shop_widget_step3') }}</li>
        </ol>
        <a href="{{ url('/profile/type') }}">{{ __('sidebar.change_profile_type_link') }}</a>
    </div>
</aside>