<?php

namespace App\Http\Controllers\Admin\Ad;

use App\AdCurrency;

use App\AdTag;
use App\Http\Controllers\Controller;
use App\TempProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class Uploader extends Controller
{
    public function index() {
        return view('admin.ad.uploader')->with([
            'ads' => TempProduct::with('currency')->paginate(50)
        ]);
    }

    public function uploadFile(Request $request) {
        set_time_limit(300);

        // temp


        $request->validate([
            'file_url' => 'required',
            'user_id' => 'required',
            'city_id' => 'required',
            'delimiter' => 'required'
        ]);


        //$csv = "https://surikat.ua/google_merchant.csv";
        $csv = $request->input('file_url');
        $delimiter = $request->input('delimiter');

        $csv_content = file_get_contents($csv);

        // TODO загружаем данные
        if ($csv_content) {
            try {
                $reader = Reader::createFromString($csv_content);

                $reader->setDelimiter($delimiter);
                $reader->setHeaderOffset(0);

                foreach ($reader->getRecords() as $offset => $raw_record) {

                    $record = array_map(function ($value) {
                        $breaks = array("<br />","<br>","<br/>");
                        $replaced_value = str_ireplace($breaks, "\r\n", $value);
                        return strip_tags($replaced_value);
                    }, $raw_record);



                    //condition
                    if (isset($record['brand'])) {
                        $brand = strtolower($record['brand']);
                    } else {
                        $brand = '';
                    }

                    //condition
                    if (isset($record['condition'])) {
                        $condition = strtolower($record['condition']);
                    } else {
                        $condition = 'new';
                    }
                    //stock
                    if (isset($record['availability'])) {
                        $stock = strtolower($record['availability']);
                    } else {
                        $stock = 'in_stock';
                    }

                    //code
                    if (isset($record['ean'])) {
                        $code = strtolower($record['ean']);
                    } elseif (isset($record['mpn'])) {
                        $code = strtolower($record['mpn']);
                    } else {
                        $code = '';
                    }

                    //currency
                    $currency = strtoupper(substr($record['price'], -3, 3));
                    $currency_obj = AdCurrency::where('code', $currency)->first();
                    if (!$currency_obj) {
                        throw new \Exception("Неподдерживаемая валюта в файле");
                    }
                    //price
                    $price = substr($record['price'], 0, -3);

                    //images
                    $images = [];
                    if (isset($record['image'])) {
                        $images[] = trim($record['image']);
                    }

                    if (isset($record['image_link'])) {
                        $images[] = trim($record['image_link']);
                    }



                    if (isset($record['additional_image_link'])) {
                        $additional_images = explode(',', $record['additional_image_link']);
                        if (count($additional_images) >= 1) {
                            foreach ($additional_images as $additional_image) {
                                $images[] = trim($additional_image);
                            }
                        }
                    }

                    $data = [
                        'currency_id' => $currency_obj->id,
                        'images' => json_encode($images),
                        'name' => $record['title'],
                        'content' => $record['description'],
                        'price' => (int)$price,
                        'url' => $record['link'],
                        'brand' => $brand,
                        'code' => $code,
                        'stock' => $stock,
                        'condition' => $condition,
                    ];

                    TempProduct::updateOrCreate([
                        'source_id' => $record['id'],
                        'user_id' => $request->input('user_id'),
                        'city_id' => $request->input('city_id'),
                    ], $data);
                }
            } catch (\Exception $exception) {
                return redirect()->back()->withErrors(['Ошибка обработки файла (возможно, разделитель выбран не верно.)']);
            }

        }

        return redirect()->back()->with(['success', 'Данные добавлены']);

    }

    public function deleteSelected(Request $request) {
        $request->validate([
            'products' => 'required|min:1'
        ]);
        $products = TempProduct::find($request->get('products'));
        $products->each(function (TempProduct $product) {
            $product->delete();
        });
        return redirect()->back()->with(['success' => 'Выбранные позиции удалены']);
    }

    public function publish(Request $request) {
        $request->validate([
            'products' => 'required|min:1',
            'category_id' => 'required|exists:ad_categories,id'
        ]);

        $category_id = $request->input('category_id');

        TempProduct::with('user')->find($request->get('products'))->each(function (TempProduct $product) use ($category_id) {
            // Загрузка изображений

            $images = [];

            try {
                if ($product->images) {
                    $disk = Storage::disk('s3');
                    foreach ($product->images as $key => $image) {
                        if (filter_var($image, FILTER_VALIDATE_URL)) {
                            $filename = basename($image);

                            $storage_path = 'public/user-'.$product->user_id . '/' . $filename;

                            if (!$disk->exists($storage_path)) {
                                $disk->put($storage_path, file_get_contents($image), 'public');
                            }

                            $images[] = $disk->url($storage_path);
                        }

                    }
                }
            } catch (\Exception $exception) {
                $product->delete();
            }


            // Создание объявлений
            $ad_identify_data = [
                'user_id' => $product->user_id,
                'category_id' => $category_id,
                'city_id' => $product->city_id,
                'currency_id' => $product->currency_id,
                //'source_id' => $product->source_id,
                'code' => $product->code,
                'slug' => str_slug($product->name),
            ];
            $ad_data = [
                'name' => $product->name,
                'content' => $product->content,
                'price' => $product->price,
                'brand' => $product->brand,
                'stock' => $product->stock,
                'condition' => $product->condition,
                'url' => $product->url,
                'is_product' => 1,
                'image' => array_shift($images),
                'images' => $images,
                'telephone' => $product->user->telephone ?? '',
                'email' => $product->user->email,
            ];

            try {
                //dd($ad_data);
                $ad = \App\Ad::updateOrCreate($ad_identify_data, $ad_data);

                // Создание тегов для объявления
                $tag = AdTag::updateOrCreate(['name' => $product->brand, 'slug' => str_slug($product->brand)]);

                //dd($tag->id);
                $ad->tags()->attach($tag->id);

                // Удаление временного объявления
                $product->delete();
            } catch (\Exception $exception) {
                $product->delete();
            }

        });


        return redirect()->back()->with(['success' => 'Выбранные позиции опубликованы и удалены с текущей очереди']);
    }
}
