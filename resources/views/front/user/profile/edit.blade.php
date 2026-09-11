@extends('front.layout')
@section('meta_title', "Редактировать профиль {$user->email}")
@section('meta_description', "Редактировать профиль {$user->email}")
@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>
            {{ Breadcrumbs::render('profile.edit') }}
            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1 style="display:flex; align-items:center; justify-content:space-between;">
                        {{ __('profile.edit_heading') }}
                        <a href="{{ route('admin.index') }}" class="btn" style="font-size:14px;">ТЕСТ_БЕЗ_УМОВИ</a>
                    </h1>
                    @if(session()->has('success'))
                        <div class="alert success">
                            {{ session()->get('success') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert danger">
                            <ul style="padding: 0 0 0 10px;margin: 0;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form class="form-account" action="{{ $action }}" method="post">
                        @csrf
                        <div class="account-author author-edit">
                            <div class="author-photo">
                                <img alt="{{ __('profile.avatar_alt') }}" src="{{ $user->image ?? 'https://secure.gravatar.com/avatar/f17c59914122f91f742418889e41b124?s=250&amp;d=mm&amp;r=g' }}" class="author-avatar" height="250" width="250">
                            </div>
                            <div class="upload-file upload-avatar">
                                <label>{{ __('profile.avatar_label') }}</label><br>
                                <label class="upload-label">
                                    <input name="image" type="file" class="input-file"  />
                                    <div>{{ __('profile.avatar_upload_button') }}</div>
                                    <input class="input-file-name" type="text" id="input-file-name" value="{{ __('ad_create.no_file_selected') }}" disabled />
                                </label>
                                <span class="form-help">{{ __('profile.avatar_help') }}</span>
                            </div>
                        </div>
                        <div class="columns">
                            <div class="col-2">
                                <div class="form-group">
                                    <label for="firstname">{{ __('profile.firstname_label') }}</label>
                                    <input type="text"
                                           id="firstname"
                                           name="firstname"
                                           value="{{ $user->firstname ?? old('firstname') }}"
                                           class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="email">{{ __('ad_page.mail_field_label') }}</label>
                                    <input disabled
                                           type="email"
                                           name="email"
                                           id="email"
                                           class="form-control"
                                           value="{{ $user->email ?? old('email') }}">
                                </div>
                                <div class="form-group">
                                    <label for="site_url">{{ __('profile.site_label') }}</label>
                                    <input type="text"
                                           id="site_url"
                                           name="site_url"
                                           value="{{ $user->site_url ?? old('site_url') }}"
                                           class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="telegram_url">Telegram</label>
                                    <input type="text"
                                           id="telegram_url"
                                           name="telegram_url"
                                           value="{{ $user->telegram_url ?? old('telegram_url') }}"
                                           class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="twitter_url">{{ __('profile.twitter_label') }}</label>
                                    <input type="text"
                                           id="twitter_url"
                                           name="twitter_url"
                                           value="{{ $user->twitter_url ?? old('twitter_url') }}"
                                           class="form-control">
                                    <span class="form-help">{{ __('profile.twitter_help') }}</span>
                                </div>
                                <div class="form-group">
                                    <label for="facebook_url">{{ __('profile.facebook_label') }}</label>
                                    <input type="text"
                                           id="facebook_url"
                                           name="facebook_url"
                                           value="{{ $user->facebook_url ?? old('facebook_url') }}"
                                           class="form-control">
                                    <span class="form-help">{!! __('profile.facebook_help') !!}</span>
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label for="lastname">{{ __('profile.lastname_label') }}</label>
                                    <input type="text"
                                           id="lastname"
                                           name="lastname"
                                           value="{{ $user->lastname ?? old('lastname') }}"
                                           class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="telephone">{{ __('profile.telephone_full_label') }}</label>
                                    <input type="tel"
                                           name="telephone"
                                           id="telephone"
                                           value="{{ $user->telephone ?? old('telephone') }}"
                                           class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="instagram_url">Instagram</label>
                                    <input type="text"
                                           name="instagram_url"
                                           id="instagram_url"
                                           value="{{ $user->instagram_url ?? old('instagram_url') }}"
                                           class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="info">{{ __('profile.about_me_label') }}</label>
                                    <textarea rows="8"
                                              id="info"
                                              name="info"
                                              class="form-control">{{ $user->info ?? old('info') }}</textarea>
                                </div>
                            </div>
                            {{--<div class="col-2">--}}
                                {{--<div class="form-group">--}}
                                    {{--<label>Новый пароль</label>--}}
                                    {{--<input type="text" class="form-control" placeholder="">--}}
                                    {{--<span class="form-help">Пароль должен быть минимум из семи символов.</span>--}}
                                    {{--<button class="btn">Сгенерировать пароль</button>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                        </div>
                        <div class="form-action">
                            <input type="submit" class="btn btn-edit-profile" value="{{ __('profile.update_button') }}">
                        </div>
                    </form>
                </div>
                @include('front.sidebars.user')
            </div>
        </div>
    </main>
@endsection
