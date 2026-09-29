@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Щоденний звіт</h4>
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
                        Пункти 1-3 рахуються щоночі за попередню добу (<code>php artisan report:daily</code>).
                        Решта пунктів заповнюють самі відповідні команди одразу після свого запуску — тому
                        заповнені лише в ті дні, коли ця конкретна команда реально спрацювала (більшість —
                        раз на тиждень); в інші дні там прочерк, це нормально й означає "не запускалась
                        сьогодні", а не помилку.
                    </p>

                    @if($reports->isEmpty())
                        <p>Звітів поки немає.</p>
                    @endif

                    @foreach($reports as $report)
                        <div class="card mb-3" style="border:1px solid #e0e0e0;">
                            <div class="card-body">
                                <h4 style="margin-bottom: 16px;">{{ $report->date->format('d.m.Y (l)') }}</h4>

                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <strong>1. Оголошень розміщено:</strong> {{ $report->ads_count }}
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <strong>2. Нових користувачів:</strong> {{ $report->new_users_count }}
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <strong>3. Нових магазинів:</strong> {{ $report->new_shops_count }}
                                    </div>
                                </div>

                                <hr>

                                <div class="mb-2">
                                    <strong>4. Семантика й контент-план:</strong>
                                    @if($report->semantics_added_count !== null)
                                        додано {{ $report->semantics_added_count }} ключів,
                                        {{ $report->topics_added_count }} тем.
                                        @if($report->clusters_summary)
                                            <br><span class="text-muted">Кластери: {{ $report->clusters_summary }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">не запускалась цього дня</span>
                                    @endif
                                </div>

                                <div class="mb-2">
                                    <strong>5. Опубліковані статті:</strong>
                                    @if($report->articles_published_count !== null)
                                        {{ $report->articles_published_count }} шт.
                                        @if($report->articles_published_summary)
                                            <br><span class="text-muted">{{ $report->articles_published_summary }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">не публікувались</span>
                                    @endif
                                </div>

                                <div class="mb-2">
                                    <strong>6. Оновлені старі статті:</strong>
                                    @if($report->articles_refreshed_count !== null)
                                        {{ $report->articles_refreshed_count }} шт.
                                        @if($report->articles_refreshed_summary)
                                            <br><span class="text-muted">{{ $report->articles_refreshed_summary }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">не оновлювались</span>
                                    @endif
                                </div>

                                <div class="mb-2">
                                    <strong>7. Перевірка індексації Google:</strong>
                                    @if($report->indexing_checked_count !== null)
                                        перевірено {{ $report->indexing_checked_count }} статей.
                                        @if($report->indexing_summary)
                                            <br><span class="text-muted">За вердиктом: {{ $report->indexing_summary }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">не запускалась цього дня</span>
                                    @endif
                                </div>

                                <div class="mb-2">
                                    <strong>8. SEO товарів магазинів:</strong>
                                    {{ $report->products_seo_optimized_count !== null ? $report->products_seo_optimized_count . ' шт.' : '—' }}
                                </div>

                                <div class="mb-2">
                                    <strong>9. SEO звичайних оголошень:</strong>
                                    {{ $report->ads_seo_optimized_count !== null ? $report->ads_seo_optimized_count . ' шт.' : '—' }}
                                </div>

                                <div class="mb-2">
                                    <strong>10. SEO тегів:</strong>
                                    @if($report->tags_seo_optimized_count !== null)
                                        {{ $report->tags_seo_optimized_count }} шт.
                                        @if($report->tags_seo_optimized_summary)
                                            <br><span class="text-muted">{{ $report->tags_seo_optimized_summary }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">не запускалась цього дня</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
