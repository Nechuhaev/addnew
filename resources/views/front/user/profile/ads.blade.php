@extends('front.layout')
@section('meta_title', "Мои объявления | Доска объявлений addnew.biz")
@section('meta_description', "Мои объявления | Доска объявлений addnew.biz")
@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>
            {{ Breadcrumbs::render('profile.ads') }}
            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>{{ __('profile.my_ads_heading') }}</h1>
                    @if(session()->has('success'))
                        <div class="alert success"> <!-- success -->
                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/warning.svg') }}" />
                            {{ session()->get('success') }}
                        </div>
                    @endif
                    @if(session()->has('error'))
                        <div class="alert"> <!-- success -->
                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/warning.svg') }}" />
                            {{ session()->get('error') }}
                        </div>
                    @endif
                    <p>{{ __('profile.my_ads_intro') }}</p>
                    @if($ads)
                    <table class="table-account">
                        <thead>
                        <tr>
                            <th class="" data-class="">&nbsp;</th>
                            <th class="th-adv">{{ __('ad_create.name_label') }}</th>
                            <th class="hidden-xs" data-hide="phone">{{ __('profile.th_views') }}</th>
                            <th class="hidden-xs" data-hide="phone">{{ __('profile.th_status') }}</th>
                            <th class="hidden-xs" data-hide="phone">{{ __('profile.th_options') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($ads as $ad)
                        <tr>
                            <td class="td-number"><span class="btn-table-toggle"><i class="icon icon-plus"></i></span>{{ $loop->iteration }}.</td>
                            <td class="td-adv">
                                <h3><a href="{{ $ad->url }}">{{ $ad->name }}</a></h3>
                                <p class="td-meta">
                                    <span class="meta-tag"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/editor-ul.svg') }}" />&nbsp;<a href="{{ $ad->category->url }}" rel="tag" class="">{{ $ad->category->path }}</a></span>
                                    <span class="meta-date"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/clock.svg') }}" />&nbsp;<span>{{ $ad->date_start }}</span></span>
                                </p>
                            </td>
                            <td class="hidden-xs">{{ $ad->total_views }}</td>
                            <td class="hidden-xs">
                                <span class="status">{{ __('user/ads.status_' . $ad->status) }}</span>
                            </td>
                            <td class="hidden-xs">
                                <ul class="td-actions">
                                    <li>
                                        <a title="{{ __('ad_create.edit_ad_button') }}" href="{{ route('ad.edit', ['id' => $ad->id]) }}" class="edit">
                                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/edit.svg') }}" />
                                        </a>
                                        <a title="{{ __('profile.delete_ad_title') }}" href="{{ route('ad.delete', ['id' => $ad->id]) }}" onclick="return confirm('{{ __('profile.delete_confirm') }}');" class="delete">
                                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/trash.svg') }}" />
                                        </a>
                                        @if ($ad->status == 'active')
                                        <a title="{{ __('profile.pause_ad_title') }}" href="{{ route('ad.changeStatus', ['ad_id' => $ad->id, 'status_id' => 0]) }}" class="restart">
                                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/controls-pause.svg') }}" />
                                        </a>
                                        @else
                                        <a title="{{ __('profile.resume_ad_title') }}" href="{{ route('ad.changeStatus', ['ad_id' => $ad->id, 'status_id' => 1]) }}" class="restart">
                                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/update.svg') }}" />
                                        </a>
                                        @endif
                                    </li>
                                    @if ($ad->status != 'archive')
                                        <li><a title="{{ __('profile.mark_outdated_title') }}" href="{{ route('ad.changeStatus', ['ad_id' => $ad->id, 'status_id' => 2]) }}">{{ __('profile.mark_outdated_title') }}</a></li>
                                    @endif
                                </ul>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                        {{ $ads->links('front.widgets.paginate') }}
                    @endif
                </div>
                @include('front.sidebars.user')
            </div>
        </div>
    </main>
@endsection