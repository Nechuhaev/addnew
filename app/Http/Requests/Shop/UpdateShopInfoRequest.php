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
            'delivery_methods' => 'nullable|array',
            'delivery_methods.*' => 'in:' . implode(',', array_keys(\App\Services\ShopDelivery::DELIVERY)),
            'payment_methods' => 'nullable|array',
            'payment_methods.*' => 'in:' . implode(',', array_keys(\App\Services\ShopDelivery::PAYMENT)),
            'free_delivery_from' => 'nullable|integer|min:1|max:10000000',
            'delivery_note' => 'nullable|string|max:500',
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
        ] + (app()->getLocale() === 'ru' ? [
            'free_delivery_from.*' => 'Сумма бесплатной доставки — целое число больше 0.',
            'delivery_note.max' => 'Примечание к доставке — не больше 500 символов.',
            'delivery_methods.*' => 'Выбран неизвестный способ доставки.',
            'payment_methods.*' => 'Выбран неизвестный способ оплаты.',
        ] : [
            'free_delivery_from.*' => 'Сума безкоштовної доставки — ціле число, більше 0.',
            'delivery_note.max' => 'Примітка до доставки — не більше 500 символів.',
            'delivery_methods.*' => 'Вибрано невідомий спосіб доставки.',
            'payment_methods.*' => 'Вибрано невідомий спосіб оплати.',
        ]);
    }
}