@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Відгуки: {{ $shop->username }}</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <a href="{{ route('admin.shops.edit', $shop->id) }}" class="btn btn-sm btn-secondary mb-3">&larr; До магазину</a>

                    @if($reviews->isEmpty())
                        <p>У цього магазину поки немає відгуків.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Дата</th>
                                        <th>Користувач</th>
                                        <th>IP</th>
                                        <th>Оцінка</th>
                                        <th>Коментар</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reviews as $review)
                                        <tr style="{{ $review->suspicious_ip ? 'background-color:#fff3cd;' : '' }}">
                                            <td style="white-space: nowrap;">{{ $review->created_at->format('d.m.Y H:i') }}</td>
                                            <td>
                                                {{ optional($review->reviewer)->username ?? '— (видалений акаунт)' }}
                                                @if($review->reviewer_user_id)
                                                    <br><small class="text-muted">ID={{ $review->reviewer_user_id }}</small>
                                                @endif
                                            </td>
                                            <td style="white-space: nowrap;">
                                                {{ $review->ip_address ?? '—' }}
                                                @if($review->suspicious_ip)
                                                    <br><span class="badge badge-warning" title="Ця IP залишила більше одного відгуку цьому магазину">⚠ підозріло</span>
                                                @endif
                                            </td>
                                            <td style="white-space: nowrap;">
                                                <span style="color:#f5a623;">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                            </td>
                                            <td>{{ $review->comment ?? '—' }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('admin.shops.reviews.delete', [$shop->id, $review->id]) }}" method="POST"
                                                      onsubmit="return confirm('Видалити цей відгук назавжди?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Видалити</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $reviews->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
