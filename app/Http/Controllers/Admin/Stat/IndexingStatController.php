<?php

namespace App\Http\Controllers\Admin\Stat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexingStatController extends Controller
{
    public function index(Request $request)
    {
        $rows = DB::table('articles')
            ->leftJoin('article_index_status', 'article_index_status.article_id', '=', 'articles.id')
            ->select([
                'articles.id',
                'articles.name',
                'articles.slug',
                'article_index_status.verdict',
                'article_index_status.coverage_state',
                'article_index_status.indexing_state',
                'article_index_status.last_crawl_time',
                'article_index_status.checked_at',
            ])
            ->orderByRaw("
                CASE
                    WHEN article_index_status.checked_at IS NULL THEN 0
                    WHEN article_index_status.verdict != 'PASS' THEN 1
                    ELSE 2
                END
            ")
            ->orderBy('article_index_status.checked_at', 'asc')
            ->get();

        $data = [];
        $data['rows'] = $rows;
        $data['summary'] = [
            'total' => $rows->count(),
            'indexed' => $rows->where('verdict', 'PASS')->count(),
            'not_indexed' => $rows->whereNotNull('checked_at')->where('verdict', '!=', 'PASS')->count(),
            'never_checked' => $rows->whereNull('checked_at')->count(),
        ];

        return view('admin.stat.indexing')->with($data);
    }
}