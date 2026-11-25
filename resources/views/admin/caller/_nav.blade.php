{{-- Спільна навігація розділу «AI-дзвінки»: на телефоні прокручується горизонтально --}}
<style>
    .caller-nav { display:flex; flex-wrap:nowrap; overflow-x:auto; -webkit-overflow-scrolling:touch; gap:4px; margin-bottom:16px; }
    .caller-nav .nav-link { white-space:nowrap; border-radius:6px; }
    .caller-nav .nav-link.active { background:#1e88e5; color:#fff; }
    .alert { white-space:pre-line; word-break:break-word; }
    .caller-stat { font-size:26px; font-weight:600; line-height:1.1; }
    .caller-stat-label { font-size:13px; color:#6c757d; }
    @media (max-width: 575.98px) {
        .caller-stat { font-size:22px; }
        .table td, .table th { padding:.5rem .4rem; font-size:13px; }
        .page-breadcrumb .page-title { font-size:18px; }
        .btn-block-xs { display:block; width:100%; margin-bottom:6px; }
    }
</style>
@php $r = Route::currentRouteName(); @endphp
<nav class="nav nav-pills caller-nav">
    <a class="nav-link {{ $r === 'admin.caller' ? 'active' : '' }}" href="{{ route('admin.caller') }}"><i class="mdi mdi-view-dashboard"></i> Огляд</a>
    <a class="nav-link {{ strpos($r, 'admin.caller.leads') === 0 ? 'active' : '' }}" href="{{ route('admin.caller.leads') }}"><i class="mdi mdi-format-list-bulleted"></i> Ліди</a>
    <a class="nav-link {{ strpos($r, 'admin.caller.emails') === 0 ? 'active' : '' }}" href="{{ route('admin.caller.emails') }}"><i class="mdi mdi-email-outline"></i> Листи</a>
    <a class="nav-link {{ strpos($r, 'admin.caller.simulator') === 0 ? 'active' : '' }}" href="{{ route('admin.caller.simulator') }}"><i class="mdi mdi-message-text-outline"></i> Тренажер</a>
    <a class="nav-link {{ strpos($r, 'admin.caller.settings') === 0 ? 'active' : '' }}" href="{{ route('admin.caller.settings') }}"><i class="mdi mdi-settings"></i> Налаштування</a>
</nav>
