<?php

namespace App\Http\Controllers\Front\Page;

use App\Http\Controllers\Controller;
use App\Mail\ContactForm;
use App\Page;
use App\SeoField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    public function page($slug) {
        $page = Page::where('slug', $slug)->first();

        if ($page) {

            $data['page'] = $page;

            return view('front.page.page')->with($data);
        }

        abort(404);
    }

    public function contacts(Request $request) {

        if ($request->isMethod('post')) {
            $errors = [
                'name.*' => 'Введите Ваше имя!',
                'email.*' => 'Введите email!',
                'subject.*' => 'Выберите раздел обращения!',
                'description.required' => 'Текст обращения не может быть пустым',
                'description.min' => 'Минимальная длина текста обращения :min символов',
                'images.image' => 'Вы можете загружать только изображения',
                'images.max' => 'Максимальный размер загружаемого файла превышает :max',
                'images.mimes' => 'Для загрузки доступны только форматы :mimes',
            ];

            $request->validate([
                'name' => 'required|max:255',
                'email' => 'required|email',
                'subject' => 'required|not_in:0',
                'description' => 'required|min:10',
                'images.*' => 'image|max:1024|mimes:jpg,jpeg,bmp,png'
            ], $errors);

            Mail::send(new ContactForm($request->get('name'), $request->get('email'), $request->get('subject'), $request->get('description'), $request->allFiles()));

            return redirect(route('contacts'))->with('success', 'Форма отправлена!');
        }
        // SEO поля
        $seo_field = SeoField::where('index', 'contacts')->first();
        if ($seo_field) {
            $data['meta'] = [
                'meta_title' => $seo_field->meta_title,
                'meta_description' => $seo_field->meta_description,
                'description' => $seo_field->description
            ];
        } else {
            $data['meta'] = [
                'meta_title' => false,
                'meta_description' => false,
                'description' => false
            ];
        }
        return view('front.page.contacts')->with($data);
    }

}
