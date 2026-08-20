@extends('front.layout')
@section('meta_title', $meta['meta_title'] ?? "Контактная информация")
@section('meta_description', $meta['meta_description'] ?? "Контактная информация addnew.biz")
@section('style')
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script>
        function onSubmit(token) {
            document.getElementById("contact-form").submit();
        }
    </script>
@endsection
@section('content')
    <main class="contact-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>
            <ul class="breadcrumb">
                <li><a href="{{ route('index') }}">{{ __('front.home') }}</a></li>
                <li><span>{{ __('front.contacts') }}</span></li>
            </ul>
            <h1>{{ __('front.contacts') }}</h1>
            <div class="columns columns-nowrap">
                <aside class="column-left hidden-xs">
                    <div class="notice-wrap">
                        <div class="notice">
                            <i class="icon icon-note"></i>
                            <div>
                                <p>{{ __('contacts.intro_notice') }}</p>
                            </div>
                        </div>
                    </div>
                </aside>
                <div class="column-content">
                    <div class="notice-wrap visible-xs">
                        <div class="notice">
                            <i class="icon icon-note"></i>
                            <div>
                                <p>{{ __('contacts.intro_notice') }}</p>
                            </div>
                        </div>
                    </div>
                    <form class="form-contact"
                          id="contact-form"
                          action="{{ route('contacts.submit') }}"
                          method="post"
                          enctype="multipart/form-data" >
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul style="padding: 0 0 0 10px;margin: 0;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if(session()->has('success'))
                            <div class="alert alert-success">
                                {{ session()->get('success') }}
                            </div>
                        @endif
                        <div class="form-group">
                            <label>{{ __('contacts.name_label') }} <span class="star">*</span>:</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ __('contacts.email_label') }} <span class="star">*</span>:</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ __('contacts.subject_label') }} <span class="star">*</span>:</label>
                            <select name="subject" class="form-control" style="width: 100%;">
                                <option value="0">{{ __('ad_create.select_dash_placeholder') }}</option>
                                {{-- value="" лишаємо оригінальним російським текстом навмисно —
                                     можливо, десь у бекенді чи листах адміну звіряється саме
                                     ця строка; перекладаємо тільки видимий текст. --}}
                                <option value="Подключение магазина">{{ __('contacts.subject_shop') }}</option>
                                <option value="Проблемы регистрации и входа">{{ __('contacts.subject_auth') }}</option>
                                <option value="Объявление на модерации">{{ __('contacts.subject_moderation') }}</option>
                                <option value="У меня не работает...">{{ __('contacts.subject_broken') }}</option>
                                <option value="Профиль, управление объявлениями">{{ __('contacts.subject_profile') }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ __('contacts.description_label') }} <span class="star">*</span></label>
                            <textarea name="description" rows="8" class="form-control">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>{{ __('contacts.images_label') }}</label>
                            <input type="file" multiple name="images">
                            <p class="help">{{ __('contacts.images_help') }}</p>
                        </div>
                        <div class="form-action">
                            <input type="submit" class="btn btn-contact g-recaptcha"
                                   data-sitekey="6LcqmP4ZAAAAAOoQscpUmczUD25MFl3tk_CT2C_i"
                                   data-callback='onSubmit'
                                   data-action='submit'
                                   value="{{ __('contacts.send_button') }}">
                        </div>
                    </form>
                </div>
            </div>
            <div class="show-more">
                <section class="show-more__text">
                    {!! $meta['description']  !!}
                </section>
                <div class="show-more__shadow"></div>
                <span class="show-more__btn btn-show">{{ __('front.show_more') }}</span>
            </div>
        </div>
    </main>
@endsection