<?php
namespace App\Http\Controllers\Admin\Seo;
use App\Http\Controllers\Controller;
use App\SeoField;
use Illuminate\Http\Request;
class Seo extends Controller
{
    /**
     * Список типов СЕО полей
     * key = код, совпадает с index в БД и роутом admin.seo.{key}
     * @var string[]
     */
    public $fieldTypes = [
        'index' => 'Главная',
        'countries' => 'Список стран',
        'contacts' => 'Контактная информация',
        'ad' => 'Объявление',
        'ad-product' => 'Товар',
        'ad-category' => 'Категория объявления',
        'ad-tag' => 'Тег',
        'ad-country' => 'Страна',
        'ad-region' => 'Область',
        'ad-city' => 'Город',
        'search' => 'Поиск',
        'ad-user' => 'Список объявлений пользователя',
        'shop-list' => 'Список интернет-магазинов',
    ];
    /**
     * Типы страниц с СЕО полями по умолчанию
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function page() {
        $items = [];
        /**
         * С
         */
        $items = SeoField::get()->map(function ($seo_field) {
            $index = $seo_field->index;
            return [
                'name' => $this->fieldTypes[$index] ?? null,
                'description' => (bool)$seo_field->getOriginal('description'),
                'meta_title' => (bool)$seo_field->getOriginal('meta_title'),
                'meta_description' => (bool)$seo_field->getOriginal('meta_description'),
                'description_uk' => (bool)$seo_field->getOriginal('description_uk'),
                'meta_title_uk' => (bool)$seo_field->getOriginal('meta_title_uk'),
                'meta_description_uk' => (bool)$seo_field->getOriginal('meta_description_uk'),
                'action' => route("admin.seo.{$index}")
            ];
        });
        return view('admin.seo.list')->with(['items' => $items]);
    }
    public function update(Request $request) {
        //dd($request->get('index'));
        if ($request->has('index')) {
            $errors = [
                'meta_title.max' => 'Максимальная длина поля meta title :max символов',
                'meta_description.max' => 'Максимальная длина поля meta description :max символов',
                'meta_title_uk.max' => 'Максимальна довжина поля meta title :max символів',
                'meta_description_uk.max' => 'Максимальна довжина поля meta description :max символів',
            ];
            $request->validate([
                'meta_title' => 'max:255',
                'meta_description' => 'max:255',
                'meta_title_uk' => 'max:255',
                'meta_description_uk' => 'max:255',
            ], $errors);
            $index = $request->get('index');
            $seo_field = SeoField::where('index', $index)->first();
            if (!$seo_field) {
                $seo_field = new SeoField();
            }
            $seo_field->index = $index;
            $seo_field->meta_title = $request->get('meta_title') ?? $seo_field->getOriginal('meta_title');
            $seo_field->meta_description = $request->get('meta_description') ?? $seo_field->getOriginal('meta_description');
            $seo_field->description = $request->get('description') ?? $seo_field->getOriginal('description');
            $seo_field->meta_title_uk = $request->get('meta_title_uk') ?? $seo_field->getOriginal('meta_title_uk');
            $seo_field->meta_description_uk = $request->get('meta_description_uk') ?? $seo_field->getOriginal('meta_description_uk');
            $seo_field->description_uk = $request->get('description_uk') ?? $seo_field->getOriginal('description_uk');
            $seo_field->save();
            return redirect(route('admin.seo'))->with('success', 'Ваши данные обновлены');
        }
    }
}