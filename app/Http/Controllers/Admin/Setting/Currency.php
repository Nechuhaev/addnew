<?php

namespace App\Http\Controllers\Admin\Setting;

use App\AdCurrency;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Currency extends Controller
{
    public function showForm($currency_id = null)
    {
        $currencies = AdCurrency::all();

        if ($currency_id)
        {

        }

    }

    public function create(Request $request) {

    }

    public function update(Request $request)
    {

    }

    public function delete($currency_id)
    {

    }
}
