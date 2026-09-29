<?php

namespace App\Http\Controllers\Admin\Report;

use App\DailyReport;
use App\Http\Controllers\Controller;

class DailyReportController extends Controller
{
    public function index()
    {
        $reports = DailyReport::orderBy('date', 'desc')->paginate(30);

        return view('admin.reports.daily', ['reports' => $reports]);
    }
}
