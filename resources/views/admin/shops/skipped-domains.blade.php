@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Виключені домени (моніторинг цін)</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">
                        Домени в цьому списку пропускаються командою моніторингу цін/наявності
                        (<code>products:monitor-prices</code>) — товари з посиланнями на ці сайти
                        не перевіряються й не призупиняються автоматично. Додавайте сюди сайти,
                        які захищені від автоматичних запитів (Cloudflare, JS-виклик тощо) і тому
                        не можуть бути перевірені технічно.
                    </p>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <hr>
                    <h4>Додати домен</h4>
                    <form action="{{ route('admin.shops.skippedDomains.store') }}" method="post" class="form-inline mb-4">
                        @csrf
                        <input type="text" name="domain" class="form-control mr-2" placeholder="Наприклад: tuna-shop.com.ua" style="min-width:250px;" required>
                        <input type="text" name="note" class="form-control mr-2" placeholder="Примітка (необов'язково)" style="min-width:250px;">
                        <button class="btn btn-success">Додати</button>
                    </form>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul style="margin:0;padding-left:15px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <hr>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Домен</th>
                                    <th>Примітка</th>
                                    <th>Додано</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($domains as $domain)
                                    <tr>
                                        <td><code>{{ $domain->domain }}</code></td>
                                        <td>{{ $domain->note ?: '—' }}</td>
                                        <td>{{ $domain->created_at->format('d.m.Y H:i') }}</td>
                                        <td>
                                            <form action="{{ route('admin.shops.skippedDomains.destroy', $domain->id) }}" method="post" style="display:inline;"
                                                  onsubmit="return confirm('Видалити «{{ $domain->domain }}» зі списку виключених? Моніторинг для цього сайту знову почне спрацьовувати.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Видалити</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">Список поки порожній.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection