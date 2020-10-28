<?php

namespace App\Http\Controllers\Front\User;

use App\AdCountry;
use App\Http\Controllers\Admin\Seo\AdCity;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Список магазинов
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Список стран где есть товары магазинов
        $countries = AdCountry::whereHas('regions', function ($query) {
            $query->whereHas('cities', function ($query) {
                $query->whereHas('ads', function ($query) {
                    $query->products();
                });
            });
        })->get();


        $selected_country = AdCountry::with('cities')
            ->where('slug', $request->get('country'))
            ->first();

//        dd($selected_country->cities->pluck('id')->toArray());

        // Пользователи
        if ($selected_country) {
            $filter_cities = $selected_country->cities->pluck('id')->toArray();
        } else {
            $filter_cities = null;
        }
        $shop_users = User::withCount('ads')
            ->whereHas('ads', function ($query) use ($filter_cities) {
                if ($filter_cities) {
                    $query->products()->whereIn('city_id', $filter_cities);
                } else {
                    $query->products();
                }
            })
            ->orderBy('created_at', 'desc')->paginate(15);


        return view('front.store.store_list')->with([
            'countries' => $countries,
            'shop_users' => $shop_users,
            'selected_country' => $selected_country
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
