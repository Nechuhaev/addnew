<?php

namespace App\Http\Controllers\Front\Ad;

use App\AdCategory;
use App\AdCity;
use App\AdCountry;
use App\AdCurrency;
use App\AdRegion;
use App\AdTag;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Front\User\Auth\RegisterController;
use App\Mail\AdDetails;
use App\Mail\UserPasswordDetails;
use App\SeoField;
use App\User;
use App\BlockedEmail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Ad extends Controller
{
    /**
     * Страница объявления
     * @param $ad
     * @return $this
     */
    public function page($ad) {
        $ad = \App\Ad::where('slug', '=', $ad)->first();
        if (!$ad) abort(404);

        $user = User::where('id', '=', $ad['user_id'])->first();
        if (!$user) abort(404);

        $allowed_country_ids = [62];
        if (!in_array($ad->city->region->country->id, $allowed_country_ids)) {
            return redirect('/', 301);
        }

        // Обновляем счетчик просмотров объявлений
        // Просмотры сегодня
        if(!Carbon::now()->isSameAs('d.m.Y', $ad->updated_at)) {
            $ad->today_views = 1; // Обнулим если сегодня новый день
        } else {
            $ad->today_views++;
        }
        // Просмотры всего
        $ad->total_views++;

        $ad->save();

        // SEO поля
        if ($ad->is_product) {
            $seo_field = SeoField::where('index', 'ad-product')->first();
        } else {
            $seo_field = SeoField::where('index', 'ad')->first();
        }

        if ($seo_field) {
            $entity_values = [
                '---name---'                => $ad->name ?? '',
                '---price---'               => $ad->formatted_price ?? '',
                '---city_name---'           => $ad->city->name ?? '',
                '---region_name---'         => $ad->city->region->name ?? '',
                '---country_name---'        => $ad->city->region->country->name ?? '',
                '---user_name---'           => $ad->user->username ?? '',
                '---user_description---'    => $ad->content ?? '',
                '---user_email---'          => $ad->email ?? '',
                '---user_telephone---'      => $ad->telephone ?? '',
                '---created_at---'          => $ad->created_at->format('d.m.Y H:m') ?? '',
                '---updated_at---'          => $ad->updated_at->format('d.m.Y H:m') ?? '',
            ];
            $meta = [
                'meta_title' => ((bool)$ad->meta_title) ? $ad->meta_title : strtr($seo_field->meta_title, $entity_values),
                'meta_description' => ((bool)$ad->meta_description) ? $ad->meta_description : strtr($seo_field->meta_description, $entity_values),
                'description' => strtr($seo_field->description, $entity_values)
            ];

        } else {
            $meta = [
                'meta_title' => $ad->meta_title,
                'meta_description' => $ad->meta_description,
                'description' => '',
            ];
        }
        // Стоимость
        $currencies = AdCurrency::all();
        $price = 0;
        if ($ad['price']) {
            foreach ($currencies as $currency) {
                if ($currency->id == $ad['currency_id']) {
                    $price = (float)$ad['price'] * (float)$currency->rate;
                }
            }
        }


        $prices = [];
        if ($price) {
            foreach ($currencies as $currency) {
                if ($price) {
                    $prices[] = [
                        'currency' => $currency['code'],
                        'symbol' => $currency['symbol'],
                        'value' => (int) ($price / $currency['rate']),
                        'selected' => ($currency->id == $ad['currency_id'])
                    ];
                }
            }
        }

        $related_ads = \App\Ad::with(['user', 'currency'])
            ->where('category_id', $ad->category_id)
            ->where('id', '<', $ad->id)
            ->orderBy('created_at', 'desc')
            ->groupBy('user_id')
            ->take(6)
            ->get();

        if ($ad->is_product && !empty($ad->code) && !empty($ad->brand)) {
            $same_products = \App\Ad::where([
                'code' => $ad->code,
                'brand' => $ad->brand,
                ])->get();
        } else {
            $same_products = collect([]);

        }
        return view('front.ad.ad')->with([
            'ad' => $ad,
            'same_products' => $same_products,
            'related' => $related_ads,
            'prices' => $prices,
            'meta' => $meta
        ]);
    }

    /**
     * Шаг 1 добавления объявления
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function create_step_category(Request $request) {

        // Валидация, сохранение данных и переход к следующему шагу
        if ($request->method() == 'POST') {
            $errors = [
                'category_id.required' => "Категория не выбрана",
                'category_id.exists' => "Выберите категорию!",
            ];

            $request->validate([
                'category_id' => 'required|integer|exists:ad_categories,id',
            ], $errors);

            $request->session()->put('ad.category_id', $request->get('category_id'));

            return redirect(route('ad.step.details'));
        }

        //$request->session()->has('ad');
        $data['parent_categories'] = AdCategory::select(['id', 'name'])
            ->where('parent_id', 0)
            ->get()
            ->toArray();


        $data['selected_parent_id'] = null;
        $data['children_categories'] = null;
        $data['selected_child_id'] = null;

        if($request->session()->has('ad.category_id')) {
            $selected_category = AdCategory::find($request->session()->get('ad.category_id'));

            if ($selected_category->parent) {
                $data['selected_parent_id'] = $selected_category->parent->id;
                $data['children_categories'] = $selected_category->parent->children->toArray();
                $data['selected_child_id'] = $selected_category->id;
            } else {
                $data['selected_parent_id'] = $selected_category->id;
            }

        }

        return view('front.ad.create_step_1')->with($data);
    }

    /**
     * Шаг 2 Добавления объявления
     * @param Request $request
     * @return $this
     */
    public function  create_step_details(Request $request) {

        // Значения по умолчанию
        // Категория
        if (!$request->session()->has('ad.category_id')) {
            return redirect(route('ad.step.category'));
        }

        if ($request->method() == 'POST') {

            Validator::extend('not_from_block_list',function($attribute, $value, $parameters){
                $emails = BlockedEmail::all();
                $mailbox = stristr($value, '@');
                foreach ($emails as $email) {
                    if ('@'.$email->mailbox === $mailbox) {
                        return false;
                    }
                }
                return true;
            }, "Почтовые адреса этого сервиса не поддерживается нашим сайтом. Пожалуйста, воспользуйтесь другим почтовым сервисом.");

            Validator::extend('not_stop_word', function ($attribute, $value, $parameters) {
                return \App\StopWord::findMatchIn($value) === null;
            }, "Текст содержит запрещенное слово и не может быть опубликован.");

            $errors = [
                'author.required' => 'Введите имя автора объявления',
                'author.min' => 'Имя автора не может быть короче :min символов',
                'telephone.required' => 'Введите номер телефона',
                'telephone.min' => 'Номер телефона не может быть короче :min символов',
                'city_id.required' => 'Выберите страну, регион и город!',
                'city_id.exists' => 'Ошибка выбора города.',
                'email.required' => 'Введите свой email!',
                'email.email' => 'Введите свой email!',
                'name.required' => 'Введите название объявления!',
                'name.min' => 'Минимальная длина названия объявления не может быть короче :min символов',
                'name.not_stop_word' => 'Название объявления содержит запрещенное слово.',
                'content.required' => 'Введите описание объявления!',
                'content.min' => 'Минимальная длина описания не может быть короче :min символов',
                'content.not_stop_word' => 'Описание объявления содержит запрещенное слово.',
                'tags.required' => 'Введите метки объявления!',
                'tags.min' => 'Минимальная длина метки не может быть короче :min символов',
                'image.required' => 'Выберите минимум одно изображение!',
                'image.*.image' => 'Недопустимый формат изображения!',
                'image.*.mimes' => 'Недопустимый формат изображения!',
                'image.*.max' => 'Недопустимый размер файла. Максимально доступный размер :min байт',
                'price.*' => 'Введите цену товара / услуги или установите 0, если оно бесплатно!',
                'currency_id.*' => 'Выберите валюту из списка!',
            ];

            $request->validate([
                'author' => 'sometimes|required|min:3',
                'telephone' => 'required|min:6',
                'city_id' => 'required|exists:ad_cities,id',
                'email' => 'sometimes|required|email|not_from_block_list',
                'name' => 'required|min:6|not_stop_word',
                'content' => 'required|min:70|not_stop_word',
                'tags' => 'required|min:3',
                'image' => 'required',
                'image.*' => 'image|max:1024|mimes:jpg,jpeg,bmp,png',
                'price' => 'required|numeric',
                'currency_id' => 'required|integer|exists:ad_currencies,id',
            ], $errors);

            $request->session()->put('ad.author', $request->get('author'));
            $request->session()->put('ad.telephone', $request->get('telephone'));
            $request->session()->put('ad.email', $request->get('email'));
            $request->session()->put('ad.city_id', $request->get('city_id'));
            $request->session()->put('ad.name', $request->get('name'));
            $request->session()->put('ad.tags', $request->get('tags'));
            $request->session()->put('ad.content', $request->get('content'));
            $request->session()->put('ad.tags', $request->get('tags'));
            $request->session()->put('ad.price', $request->get('price'));
            $request->session()->put('ad.currency_id', $request->get('currency_id'));

            if ($request->hasFile('image')) {
                if ($request->session()->has('ad.images')) {
                    $request->session()->remove('ad.images');
                }
                $dir = 'ads/' . time();
                foreach ($request->file('image') as $key => $image) {
                    $filename = $key . '.' . $image->getClientOriginalExtension();
                    $filepath = $dir . '/' . $filename;
                    $image->storeAs('public', $filepath);
                    $request->session()->push('ad.images', $filepath);
                }
            }

            return redirect(route('ad.step.preview'));
        }

        $data['category'] = AdCategory::find($request->session()->get('ad.category_id'));
        //dd($request->get('author'));
        if ($request->session()->has('ad.author')) {
            $data['author'] = $request->session()->get('ad.author');
        } elseif (old('author')) {
            $data['author'] = old('author');
        } elseif (Auth::check()) {
            $data['author'] = Auth::user()->username;
        } else {
            $data['author'] = '';
        }

        if ($request->session()->has('ad.telephone')) {
            $data['telephone'] = $request->session()->get('ad.telephone');
        } elseif (old('telephone')) {
            $data['telephone'] = old('telephone');
        } elseif (Auth::check()) {
            $data['telephone'] = Auth::user()->telephone;
        } else {
            $data['telephone'] = '';
        }


        if ($request->session()->has('ad.email')) {
            $data['email'] = $request->session()->get('ad.email');
        } elseif (old('email')) {
            $data['email'] = old('email');
        } elseif (Auth::check()) {
            $data['email'] = Auth::user()->email;
        } else {
            $data['email'] = '';
        }


        if ($request->session()->has('ad.name')) {
            $data['name'] = $request->session()->get('ad.name');
        } elseif (old('name')) {
            $data['name'] = old('name');
        } else {
            $data['name'] = '';
        }

        if ($request->session()->has('ad.tags')) {
            $tags = json_encode(explode(',', $request->session()->get('ad.tags')));
        } elseif (old('tags')) {
            $tags = json_encode(explode(',', old('tags')));
        } else {
            $tags = '';
        }

        $data['tags'] = $tags;

        if ($request->session()->has('ad.content')) {
            $data['content'] = $request->session()->get('ad.content');
        } elseif (old('content')) {
            $data['content'] = old('content');
        } else {
            $data['content'] = '';
        }

        if ($request->session()->has('ad.price')) {
            $data['price'] = $request->session()->get('ad.price');
        } elseif (old('price')) {
            $data['price'] = old('price');
        } else {
            $data['price'] = '';
        }

        if ($request->session()->has('ad.currency_id')) {
            $data['currency_id'] = $request->session()->get('ad.currency_id');
        } elseif (old('currency_id')) {
            $data['currency_id'] = old('currency_id');
        } else {
            $data['currency_id'] = '';
        }

        $data['currencies'] = AdCurrency::select(['id', 'code'])->get()->toArray();

        $data['countries'] = AdCountry::select(['id', 'name'])->get()->toArray();

        $city = null;
        $city_id = 0;

        if ($request->session()->get('ad.city_id') || old('city_id')) {
            $city_id = $request->session()->get('ad.city_id') ?? old('city_id') ?? 9474; // по умолчанию - киев
            $city = AdCity::find($city_id);
            //dd($city);
            $country_id = $city->region->country->id;
        } else {
            $country_id = old('country_id') ?? 62; // По умолчанию - украина
        }

        $data['country_id'] = $country_id;

        if ($country_id) {
            $data['regions'] = AdRegion::select(['id', 'name'])->where('country_id', $country_id)->get()->toArray();
        } else {
            $data['regions'] = null;
        }

        if ($city) {
            $region_id = $city->region->id;
        } else {
            $region_id = old('region_id') ?? 642; // по умолчанию - киевская обл
        }

        $data['region_id'] = $region_id;

        if ($region_id) {
            $data['cities'] = AdCity::select(['id', 'name'])->where('region_id', $region_id)->get()->toArray();
        } else {
            $data['cities'] = null;
        }

        $data['city_id'] = $city_id;

        return view('front.ad.create_step_2')->with($data);
    }

    /**
     * Шаг 3 Добавления объявления
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function  create_step_preview(Request $request)
    {
        if (!$request->session()->has('ad')) {
            return redirect(route('ad.step.details'));
        }

        $ad = $request->session()->get('ad');

        $data['preview']['name'] = $ad['name'];
        $data['preview']['author'] = (Auth::check()) ? Auth::user()->username : $ad['author'];
        $data['preview']['telephone'] = $ad['telephone'];
        $data['preview']['email'] = (Auth::check()) ? Auth::user()->email : $ad['email'];
        $data['preview']['content'] = $ad['content'];
        $data['preview']['city'] = AdCity::find($ad['city_id']);


        $currencies = AdCurrency::all();


        $data['preview']['currencies'] = $currencies;

        $price = 0;
        if ($ad['price']) {
            foreach ($currencies as $currency) {
                if ($currency->id == $ad['currency_id']) {
                    $price = (float)$ad['price'] * (float)$currency->rate;
                }
            }
        }


        $data['preview']['prices'] = [];
        if ($price) {
            foreach ($currencies as $currency) {
                if ($price) {
                    $data['preview']['prices'][] = [
                        'currency' => $currency['code'],
                        'symbol' => $currency['symbol'],
                        'value' => (int) ($price / $currency['rate']),
                        'selected' => ($currency->id == $ad['currency_id'])
                    ];
                }
            }
        }


        $data['preview']['image'] = '';
        $data['preview']['images'] = [];

        foreach ($ad['images'] as $key => $image) {
            if ($key == 0) {
                $data['preview']['image'] = asset('storage/' . $image);
            } else {
                $data['preview']['images'][] = asset('storage/' . $image);
            }
        }

        return view('front.ad.create_step_3')->with($data);
    }

    /**
     * Шаг 4 Добавления объявления
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function  create_step_success(Request $request) {
        $data['ad'] = \App\Ad::find($request->session()->get('ad_id'))->first();
        return view('front.ad.create_step_4')->with($data);
    }

    /**
     * Обработчик формы создания объявления
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function add(Request $request) {
        if (!$request->session()->has('ad')) {
            return redirect(route('ad.step.details'));
        }


        $ad = $request->session()->get('ad');
        $ad['email'] = (Auth::check()) ? Auth::user()->email : $ad['email'];
        $ad['author'] = (Auth::check()) ? Auth::user()->username : $ad['author'];


        $errors = [
            'author.required' => 'Введите имя автора объявления',
            'author.min' => 'Имя автора не может быть короче :min символов',
            'telephone.required' => 'Введите номер телефона',
            'telephone.min' => 'Номер телефона не может быть короче :min символов',
            'city_id.required' => 'Выберите страну, регион и город!',
            'city_id.exists' => 'Ошибка выбора города.',
            'email.required' => 'Введите свой email!',
            'email.email' => 'Введите свой email!',
            'name.required' => 'Введите название объявления!',
            'name.min' => 'Минимальная длина названия объявления не может быть короче :min символов',
            'name.not_stop_word' => 'Название объявления содержит запрещенное слово.',
            'content.required' => 'Введите описание объявления!',
            'content.min' => 'Минимальная длина описания не может быть короче :min символов',
            'content.not_stop_word' => 'Описание объявления содержит запрещенное слово.',
            'image.required' => 'Выберите минимум одно изображение!',
            'image.*.image' => 'Недопустимый формат изображения!',
            'image.*.mimes' => 'Недопустимый формат изображения!',
            'image.*.max' => 'Недопустимый размер файла. Максимально доступный размер :min байт',
            'price.*' => 'Введите цену товара / услуги или установите 0, если оно бесплатно!',
            'currency_id.*' => 'Выберите валюту из списка!',
        ];

        Validator::extend('not_from_block_list',function($attribute, $value, $parameters){
            $emails = BlockedEmail::all();
            $mailbox = stristr($value, '@');
            foreach ($emails as $email) {
                if ('@'.$email->mailbox === $mailbox) {
                    return false;
                }
            }
            return true;
        }, "Почтовые адреса этого сервиса не поддерживается нашим сайтом. Пожалуйста, воспользуйтесь другим почтовым сервисом.");

        Validator::extend('not_stop_word', function ($attribute, $value, $parameters) {
            return \App\StopWord::findMatchIn($value) === null;
        }, "Текст содержит запрещенное слово и не может быть опубликован.");

        $validator = Validator::make($ad, [
            'category_id' => 'required|integer|exists:ad_categories,id',
            'author' => 'sometimes|required|min:3',
            'telephone' => 'required|min:6',
            'city_id' => 'required|exists:ad_cities,id',
            'email' => 'required|required|email|not_from_block_list',
            'name' => 'required|min:6|not_stop_word',
            'content' => 'required|min:70|not_stop_word',
            'images' => 'required',
            'price' => 'required|numeric',
            'currency_id' => 'required|integer|exists:ad_currencies,id',
        ], $errors);

        if (!$validator->fails()) {
            // Зарегистрируем юзера
            if (!Auth::check()) {
                $user = User::where('email', $ad['email'])->first();
                if (!$user) {
                    // User password
                    $custom_password = Str::random(8);

                    // Notify user
                    Mail::to($ad['email'])->send(new UserPasswordDetails($ad['email'], $custom_password));

                    // Register user
                    $user = User::create([
                        'email' => $ad['email'],
                        'username' => $ad['author'],
                        'password' => Hash::make($custom_password),
                    ]);

                    Auth::login($user);
                } else {
                    return response()->json([
                        'auth' => 'required',
                    ]);
                }
            } else {
                $user = Auth::user();
            }

            // Добавить в MailChimp
            $email = $ad['email'];

            $apiKey = '94492d7246f58de6bdc22950014e9744-us19';
            $listId = 'b435fcadb5';

            $memberId = md5(strtolower($email));
            $dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
            $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberId;
            //dd($url);

            $json = json_encode([
                'email_address' => $email,
                'status'        => 'subscribed',
            ]);

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Добавить объявление
            $ad['user_id'] = $user->id;
            $ad['name'] = strip_tags($ad['name']);
            $ad['slug'] = null;
            $ad['content'] = strip_tags($ad['content']);
            $ad['price'] = (float)$ad['price'];
            $ad['telephone'] = strip_tags($ad['telephone']);
            $ad['email'] = strip_tags($ad['email']);

            $disk = Storage::disk('s3');

            $images = [];
            foreach ($ad['images'] as $key => $image) {
                //ошибка
                if (!$disk->exists($image)) {
                    $disk->put($image, Storage::get('public/'. $image), 'public');
                }

                if ($key == 0) {
                    $ad['image'] = $disk->url($image);
                } else {
                    $images[] = $disk->url($image);;
                }
            }

            $ad['images'] = $images;

            $ad_model = \App\Ad::create($ad);

            // Сохранить теги
            $all_tags = array_unique(array_map('trim', explode(',', $ad['tags'])));
            $not_existing_tags = $all_tags;
            $existing_tags = AdTag::whereIn('name', $all_tags)->get();

            foreach ($existing_tags as $existing_tag) {
                if (($key = array_search($existing_tag->name, $not_existing_tags)) !== false) {
                    unset($not_existing_tags[$key]);
                }
            }

            foreach ($not_existing_tags as $not_existing_tag) {
                AdTag::create(['name' => $not_existing_tag, 'slug' => null]);
            }

            $tags_to_attach = AdTag::whereIn('name', $all_tags)->pluck('id')->toArray();

            if ($tags_to_attach) {
                $ad_model->tags()->attach($tags_to_attach);
            }

            $request->session()->remove('ad');

            $request->session()->push('ad_id', $ad_model->id);

            return response()->json(['redirect' => route('ad.step.success')]);

        } else {
            return response()->json(['errors' => $validator->errors()]);
        }
    }


    /**
     * Обработчик для отправки сообщения пользователю
     * @param $slug
     * @param Request $request
     * @return $this
     */
    public function message($slug, Request $request) {

        $errors = [
            'name.required' => 'Для отправки сообщения автору - введите свое имя.',
            'name.min' => 'Минимальная длина для поля имени составлеят :min символа',
            'message.required' => 'Текст сообщения не может быть пустым',
            'message.min' => 'Минимальная длина для поля сообщения составлеят :min символа',
            'email.*' => 'Введите email!',
        ];

        $request->validate([
            'name' => 'required|min:3',
            'message' => 'required|min:30',
            'email' => 'required|email',
        ], $errors);

        $ad = \App\Ad::where('slug', '=', $slug)->first();

        $data = [
            'ad_name' => $ad->name,
            'email' => $request->get('email'),
            'message' => $request->get('message'),
            'name' => $request->get('name'),
        ];

        Mail::to($ad->email)->send(new AdDetails($data));

        return redirect()->back()->with('success', 'Сообщение отправлено автору!');
    }

    /**
     * Изменить статус объявления
     * @param $ad_id
     * @param $status_id
     * @return $this
     */
    public function changeStatus($ad_id, $status_id) {
        //dd($ad_id);
        if ($ad_id) {
            $ad = Auth::user()->ads()->whereId($ad_id)->first();
            if ($ad) {
                $ad->date_active = date('Y-m-d H:i:s');
                $ad->status = (int)$status_id;
                $ad->save();
                return redirect()->back()->with('success', 'Статус объявления изменен.');
            }
        }

        return redirect()->back()->with('error', 'Не удалось изменить статус :(');
    }

    public function edit($id) {
        $ad = Auth::user()->ads()->where('id', $id)->first();
        if (!$ad) return redirect()->back()->with('error', 'Не удалось найти объявление. Повторите попытку позже!');

        $tags = (old('tags')) ? json_encode(explode(',', old('tags'))) : json_encode($ad->tags()->pluck('name'));

        $data['id'] = $ad->id;
        $data['name'] = old('name') ?? $ad->name;
        $data['email'] = old('email') ?? $ad->email;
        $data['telephone'] = old('telephon') ?? $ad->telephone;
        $data['tags'] = $tags;
        $data['content'] = old('content') ?? $ad->content;
        $data['price'] = old('price') ?? $ad->price;
        $data['currency_id'] = old('currency_id') ?? $ad->currency_id;


        $data['currencies'] = AdCurrency::select(['id', 'code'])->get()->toArray();

        return view('front.ad.ad-edit')->with($data);
    }

    public function update($ad_id, Request $request) {

        Validator::extend('not_stop_word', function ($attribute, $value, $parameters) {
            return \App\StopWord::findMatchIn($value) === null;
        }, "Текст содержит запрещенное слово и не может быть опубликован.");

        $errors = [
            'telephone.required' => 'Введите номер телефона',
            'telephone.min' => 'Номер телефона не может быть короче :min символов',
            'email.required' => 'Введите свой email!',
            'email.email' => 'Введите свой email!',
            'name.required' => 'Введите название объявления!',
            'name.min' => 'Минимальная длина названия объявления не может быть короче :min символов',
            'name.not_stop_word' => 'Название объявления содержит запрещенное слово.',
            'content.required' => 'Введите описание объявления!',
            'content.min' => 'Минимальная длина описания не может быть короче :min символов',
            'content.not_stop_word' => 'Описание объявления содержит запрещенное слово.',
            'image.*.image' => 'Недопустимый формат изображения!',
            'image.*.mimes' => 'Недопустимый формат изображения!',
            'image.*.max' => 'Недопустимый размер файла. Максимально доступный размер :min байт',
            'price.*' => 'Введите цену товара / услуги или установите 0, если оно бесплатно!',
            'currency_id.*' => 'Выберите валюту из списка!',
        ];

        $request->validate([
            'telephone' => 'required|min:6',
            'email' => 'required|email',
            'name' => 'required|min:6|not_stop_word',
            'content' => 'required|min:70|not_stop_word',
            'image.*' => 'nullable|sometimes|image|max:1024|mimes:jpg,jpeg,bmp,png',
            'price' => 'required|numeric',
            'currency_id' => 'required|integer|exists:ad_currencies,id',
        ], $errors);



        $ad = Auth::user()->ads()->where('id', $ad_id)->first();

        if (!$ad) return redirect()->back()->with('error', 'Не удалось найти объявление. Повторите попытку позже!');

        $ad->name = $request->get('name');
        $ad->telephone = $request->get('telephone');
        $ad->email = $request->get('email');
        $ad->content = $request->get('content');
        $ad->price = $request->get('price');
        $ad->currency_id = $request->get('currency_id');


        $ad->tags()->detach();
        $all_tags = array_unique(array_map('trim', explode(',', $request->get('tags'))));
        //dd($all_tags);
        $not_existing_tags = $all_tags;
        $existing_tags = AdTag::whereIn('name', $all_tags)->get();

        foreach ($existing_tags as $existing_tag) {
            if (($key = array_search($existing_tag->name, $not_existing_tags)) !== false) {
                unset($not_existing_tags[$key]);
            }
        }

        foreach ($not_existing_tags as $not_existing_tag) {
            AdTag::create(['name' => $not_existing_tag, 'slug' => null]);
        }

        $tags_to_attach = AdTag::whereIn('name', $all_tags)->pluck('id')->toArray();


        if ($tags_to_attach) {
            $ad->tags()->attach($tags_to_attach);
        }


        // Сохраняем изображения
        if ($request->hasFile('image')) {
            $disk = Storage::disk('s3');

            if ($disk->exists(parse_url($ad->image)['path'])) {
                $disk->delete(parse_url($ad->image)['path']);
            }


            foreach ($ad->images as $_image) {
                if ($disk->exists(parse_url($_image)['path'])) {
                    $disk->delete(parse_url($_image)['path']);
                }
            }

            $images = [];

            $dir = 'ads/' . time();
            foreach ($request->file('image') as $key => $image) {

                if (!$disk->exists($image)) {
                    $filename = $key . '.' . $image->getClientOriginalExtension();
                    $filepath = $dir.'/'.$filename;

                    $disk->putFileAs($dir, $image, $filename, 'public');
                }

                if ($key == 0) {
                    $ad->image = $disk->url($filepath);
                } else {
                    $images[] = $disk->url($filepath);;
                }
            }

            $ad->images = $images;

        }

        $ad->save();

        return redirect(route('profile.ads'))->with('success', 'Объявление отредактировано!');
    }

    /**
     * Удаление объявления
     * @param null $id
     * @return $this
     */
    public function delete($id = null) {
        if ($id) {
            $ad = Auth::user()->ads()->whereId($id)->first();
            if ($ad) {
                $ad->delete();
                return redirect()->back()->with('success', 'Объявление удалено.');
            }
        }

        return redirect()->back()->with('error', 'Ошибка удаления объявления!');
    }
}