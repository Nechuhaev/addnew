<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    protected $fillable = [
        'date',
        'ads_count',
        'new_users_count',
        'new_shops_count',
        'semantics_added_count',
        'topics_added_count',
        'clusters_summary',
        'articles_published_count',
        'articles_published_summary',
        'articles_refreshed_count',
        'articles_refreshed_summary',
        'indexing_checked_count',
        'indexing_summary',
        'products_seo_optimized_count',
        'ads_seo_optimized_count',
        'tags_seo_optimized_count',
        'tags_seo_optimized_summary',
    ];

    protected $dates = ['date'];
}
