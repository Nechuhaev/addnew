<?php
namespace App\Http\Requests\Shop;
use Illuminate\Foundation\Http\FormRequest;
class UpdateShopInfoRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->is_shop_owner;
    }
    public function rules()
    {
        return [
            'firstname' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:100',
            'email' => 'required|email|max:255',
            'info' => 'nullable|string|max:5000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
        ];
    }
    public function messages()
    {
        return [
            'logo.image' => 'Недопустимый формат файла.',
            'logo.mimes' => 'Недопустимый формат файла.',
            'logo.max' => 'Максимальный размер файла: 2 МБ.',
            'banner.image' => 'Недопустимый формат файла.',
            'banner.mimes' => 'Недопустимый формат файла.',
            'banner.max' => 'Максимальный размер файла: 3 МБ.',
        ];
    }
}