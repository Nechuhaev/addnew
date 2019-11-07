@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">{{ $ad->name ?? 'Новое объявление'}}</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ (isset($ad)) ? Breadcrumbs::render('admin.ad.edit', $ad) : Breadcrumbs::render('admin.ad') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <form action="{{ $action }}" method="POST" class="category-form">
        @csrf
        @if(isset($ad))
            <input type="hidden" name="ad_id" value="{{ $ad->id }}">
        @endif
        <div class="row">
            <div class="col-8">
                <div class="card">
                    <div class="card-heading">
                        Основная информация
                    </div>
                    <div class="card-body">

                        <div class="form-group">
                            <label for="name">Название</label>
                            <div>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       value="{{ old('name') ?? $ad->name ?? '' }}"
                                       placeholder="Название объявления"
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <div>
                                <input type="text"
                                       name="slug"
                                       id="slug"
                                       value="{{ old('slug') ?? $ad->slug ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="price">Цена</label>
                            <div>
                                <div class="input-group">
                                    <input type="text"
                                           name="price"
                                           id="price"
                                           value="{{ old('price') ?? $ad->price ?? '' }}"
                                           placeholder="Цена услуги / товара, грн"
                                           class="form-control form-control-line">
                                    <select name="currency" class="form-control">
                                        <option value="1">UAH</option>
                                        <option value="2">EUR</option>
                                        <option value="3">USD</option>
                                    </select>
                                </div>

                            </div>
                        </div>

                        <div class="">
                            <label for="content">Описание</label>
                            <div>
                                <textarea name="content"
                                          id="content"
                                          class="content form-control form-control-line">{{ old('content') ?? $ad->content ?? '' }}</textarea>

                            </div>
                        </div>

                    </div>
                </div>

                <div class="card">
                    <div class="card-heading">
                        Дополнительная информация
                    </div>
                    <div class="card-body">

                        <div class="form-group">
                            <label for="ad_user">Автор</label>
                            <div>
                                <input type="text"
                                       name="ad_user"
                                       id="ad_user"
                                       value="{{ old('ad_user') ?? $ad->user->name ?? '' }}"
                                       placeholder="Начните вводить имя автора"
                                       class="form-control form-control-line">
                                <input type="hidden" id="ad_user_id" name="user_id" value="{{ old('user_id') ?? $ad->user->id ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="telephone">Телефон</label>
                            <div>
                                <input type="text"
                                       name="telephone"
                                       id="telephone"
                                       value="{{ old('telephone') ?? $ad->telephone ?? '' }}"
                                       placeholder="Номер телефона для связи"
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="">
                            <label for="email">Email</label>
                            <div>
                                <input type="text"
                                       name="email"
                                       id="email"
                                       value="{{ old('email') ?? $ad->email ?? '' }}"
                                       placeholder="Email для связи"
                                       class="form-control form-control-line">
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card">
                    <div class="card-heading">
                        SEO
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="meta_title">Список меток</label>
                            <div>
                                <input type="text"
                                       name="tags"
                                       id="tags"
                                       data-json="{{ json_encode(['tag1', 'tag2']) }}"
                                       value="{{ old('meta_title') ?? $ad->meta_title ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="meta_title">Meta-тег title</label>
                            <div>
                                <input type="text"
                                       name="meta_title"
                                       id="meta_title"
                                       value="{{ old('meta_title') ?? $ad->meta_title ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="">
                            <label for="meta_description">Meta-тег description</label>
                            <div>
                                <textarea name="meta_description"
                                          rows="5"
                                          id="meta_description"
                                          class="form-control form-control-line">{{ old('meta_description') ?? $ad->meta_description ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">

                            <div class="row">
                                <div class="col-5 align-self-center">
                                    <span class="ad-form-label-desc">Дата публикации</span>
                                </div>
                                <div class="col-7">
                                    <input type="text" class="form-control datepicker">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-5 align-self-center">
                                    <span class="ad-form-label-desc">Статус</span>
                                </div>
                                <div class="col-7">
                                    <select name="status" class="form-control" id="">
                                        <option value="1">Активно</option>
                                        <option value="0" {{ (old('status') == 0 || (isset($ad) && $ad->status == 0)) ? 'selected' : '' }}>В архиве</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <button class="btn btn-success btn-block">Сохранить</button>
                    </div>
                </div>
                <div class="card">
                    <div class="card-heading">
                        Связи
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="ad_category">Категория</label>
                            <div>
                                <input type="text"
                                       name="category"
                                       id="ad_category"
                                       value="{{ old('category') ?? $ad->category->name ?? '' }}"
                                       placeholder="Введите название категории"
                                       class="form-control form-control-line">
                                <input type="hidden" id="ad_category_id" name="category_id" value="{{ old('category_id') ?? $ad->category->id ?? '' }}">
                            </div>
                        </div>
                        <div class="">
                            <label for="ad_invalid">Гоpод</label>
                            <div>
                                <input type="text"
                                       name="invalid"
                                       id="ad_invalid"
                                       value="{{ old('city') ?? $ad->city->name ?? '' }}"
                                       placeholder="Введите название города"
                                       class="form-control form-control-line">
                                <input type="hidden" id="ad_city_id" name="city_id" value="{{ old('city_id') ?? $ad->city->id ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-heading">
                        Изображения
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="ad-image">
                                    <i class="mdi mdi-24px mdi-delete text-danger"></i>
                                    <img src="{{ old('image') ?? $ad->image ?? 'http://placehold.it/300x200' }}" class="img-fluid filepicker">
                                    <input type="hidden" name="image" value="{{ old('image') ?? $ad->image ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-6">
                                <div class="ad-image">
                                    <i class="mdi mdi-24px mdi-delete text-danger"></i>
                                    <img src="http://placehold.it/300x200" class="img-fluid filepicker">
                                    <input type="hidden" name="images[2]">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="ad-image">
                                    <i class="mdi mdi-24px mdi-delete text-danger"></i>
                                    <img src="http://placehold.it/300x200" class="img-fluid filepicker">
                                    <input type="hidden" name="images[3]">
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-6">
                                <div class="ad-image">
                                    <i class="mdi mdi-24px mdi-delete text-danger"></i>
                                    <img src="http://placehold.it/300x200" class="img-fluid filepicker">
                                    <input type="hidden" name="images[4]">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="ad-image">
                                    <i class="mdi mdi-24px mdi-delete text-danger"></i>
                                    <img src="http://placehold.it/300x200" class="img-fluid filepicker">
                                    <input type="hidden" name="images[5]">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">


                            <div class="row">
                                <div class="col-9">
                                    <button class="btn btn-warning">Переместить в архив</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn btn-danger btn-block"><i class="mdi mdi-15px mdi-delete"></i></button>
                                </div>
                            </div>


                    </div>
                </div>


            </div>

        </div>
    </form>

@endsection