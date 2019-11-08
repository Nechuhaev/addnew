<?php

namespace App\Http\Controllers\Admin\Ad;

use App\AdCurrency;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Currency extends Controller
{
    private $per_page = 20;

    public function showForm($currency_id = null)
    {
        $currencies = AdCurrency::orderBy('name', 'asc')
            ->paginate($this->per_page);

        $currency = null;

        if ($currency_id)
        {
            $currency = AdCurrency::find($currency_id);
            $action = route('admin.adCurrencies.update');
        } else {
            $action = route('admin.adCurrencies.create');
        }
        return view('admin.ad.currency')->with([
            'action' => $action,
            'currency' => $currency,
            'currencies' => $currencies,
        ]);
    }


    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'rate' => 'required|numeric',
            'code' => 'required|string|max:3|unique:ad_currencies',
            'symbol' => 'required|string|max:10',

        ]);

        AdCurrency::create($request->all());

        return redirect(route('admin.adCurrencies'))->with('success', 'Валюта добавлена');
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'rate' => 'required|numeric',
            'code' => 'required|string|max:3|unique:ad_currencies,code,' . $request->get('currency_id'),
            'symbol' => 'required|string|max:10',
        ]);

        $currency = AdCurrency::find($request->get('currency_id'));

        if ($currency) {
            $currency->fill($request->all())->save();
        }

        return redirect(route('admin.adCurrencies'))->with('success', 'Данные валюты обновлены');
    }

    public function delete($currency_id)
    {

        if ($currency_id) {
            $currency = AdCurrency::find($currency_id);

            $currency->delete();
            $data['success'] = 'Информация о валюте удалена';

//            if (!$currency->regions->count()) {
//
//            } else {
//                $data['error'] = 'Удалить страну можно только есль с ней не связан ни один регион.';
//            }

        } else {
            $data['error'] = 'Ошибка удаления валюты.';
        }
        return redirect(route('admin.adCurrencies'))->with($data);
    }
}
