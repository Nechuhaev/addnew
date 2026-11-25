@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-12 align-self-center">
                <h4 class="page-title">AI-дзвінки: ліди</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    @include('admin.caller._nav')

    <div class="row">
        {{-- Імпорт CSV --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Імпорт із CSV</h5>
                    <form method="post" action="{{ route('admin.caller.leads.import') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <input type="file" name="file" accept=".csv,text/csv" class="form-control-file" required>
                            <small class="form-text text-muted">
                                Колонки: <code>phone, store_name, store_site, contact_name, category</code>
                                (або «телефон, назва, сайт, контакт, категорія»; роздільник «,» чи «;»).
                                Номери будь-якого формату. Пропускаються дублікати, «не дзвонити» й магазини,
                                чий сайт уже зареєстрований на addnew.biz.
                            </small>
                        </div>
                        <button class="btn btn-primary btn-block-xs"><i class="mdi mdi-upload"></i> Імпортувати</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Один лід вручну --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Додати вручну</h5>
                    <form method="post" action="{{ route('admin.caller.leads.store') }}">
                        @csrf
                        <div class="form-row">
                            <div class="col-sm-6 form-group mb-2">
                                <input name="phone" class="form-control" placeholder="Телефон *" value="{{ old('phone') }}" required>
                            </div>
                            <div class="col-sm-6 form-group mb-2">
                                <input name="store_name" class="form-control" placeholder="Назва магазину *" value="{{ old('store_name') }}" required>
                            </div>
                            <div class="col-sm-6 form-group mb-2">
                                <input name="store_site" class="form-control" placeholder="Сайт" value="{{ old('store_site') }}">
                            </div>
                            <div class="col-sm-6 form-group mb-2">
                                <input name="contact_name" class="form-control" placeholder="Ім'я контакту" value="{{ old('contact_name') }}">
                            </div>
                        </div>
                        <button class="btn btn-outline-primary btn-block-xs"><i class="mdi mdi-plus"></i> Додати</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            {{-- Фільтри --}}
            <form method="get" class="form-row align-items-end mb-3">
                <div class="col-12 col-md-4 mb-2">
                    <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Пошук: назва, сайт, email, телефон">
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <select name="status" class="form-control">
                        <option value="">Усі статуси</option>
                        @foreach($statuses as $k => $v)
                            <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <select name="outcome" class="form-control">
                        <option value="">Усі результати</option>
                        @foreach($outcomes as $k => $v)
                            <option value="{{ $k }}" {{ request('outcome') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <div class="form-check">
                        <input type="checkbox" name="registered" value="1" class="form-check-input" id="f-reg" {{ request('registered') ? 'checked' : '' }}>
                        <label class="form-check-label" for="f-reg">Зареєструвалися</label>
                    </div>
                </div>
                <div class="col-6 col-md-2 mb-2 d-flex">
                    <button class="btn btn-secondary mr-1 flex-fill">Знайти</button>
                    <a href="{{ route('admin.caller.leads.export') }}" class="btn btn-outline-secondary" title="Експорт у CSV"><i class="mdi mdi-download"></i></a>
                </div>
            </form>

            <p class="text-muted small mb-2">Знайдено: {{ $leads->total() }}</p>

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                    <tr>
                        <th>Магазин</th>
                        <th class="d-none d-md-table-cell">Телефон</th>
                        <th>Статус</th>
                        <th class="d-none d-sm-table-cell">Результат</th>
                        <th class="d-none d-lg-table-cell">Email</th>
                        <th class="text-center d-none d-md-table-cell">Спроб</th>
                        <th class="d-none d-lg-table-cell">Наступний дзвінок</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($leads as $lead)
                        <tr>
                            <td>
                                <a href="{{ route('admin.caller.leads.show', $lead->id) }}"><b>{{ $lead->store_name }}</b></a>
                                @if($lead->registered_user_id)
                                    <span class="badge badge-success" title="Зареєструвався на сайті">✓ на сайті</span>
                                @endif
                                <br><small class="text-muted text-break">{{ $lead->store_site }}</small>
                                <small class="d-md-none text-muted"><br>{{ $lead->phone }}</small>
                            </td>
                            <td class="d-none d-md-table-cell text-nowrap">{{ $lead->phone }}</td>
                            <td><span class="badge badge-{{ $lead->statusBadge() }}">{{ $lead->statusLabel() }}</span></td>
                            <td class="d-none d-sm-table-cell">{{ $lead->outcomeLabel() }}</td>
                            <td class="d-none d-lg-table-cell text-break">{{ $lead->email ?: '—' }}</td>
                            <td class="text-center d-none d-md-table-cell">{{ $lead->attempts }}</td>
                            <td class="d-none d-lg-table-cell text-nowrap">{{ $lead->next_call_at ? $lead->next_call_at->format('d.m H:i') : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">Лідів немає. Імпортуйте CSV вище (приклад формату — <code>resources/caller/leads.example.csv</code>).</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $leads->links() }}
        </div>
    </div>
@endsection
