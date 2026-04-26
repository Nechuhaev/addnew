<?php

namespace App\Services;

use App\Ad;
use App\User;
use Illuminate\Support\Facades\Storage;

class ProductExportService
{
    /**
     * Визначення полів для експорту в Google Merchant Center CSV.
     * Щоб додати нове поле — достатньо додати новий елемент масиву.
     */
    protected function fieldDefinitions(): array
    {
        return [
            [
                'header'      => 'id',
                'label'       => 'ID товара',
                'description' => 'Уникальный числовой идентификатор товара на сайте.',
                'resolve'     => fn(Ad $ad) => $ad->id,
            ],
            [
                'header'      => 'title',
                'label'       => 'Название',
                'description' => 'Название товара (до 150 символов).',
                'resolve'     => fn(Ad $ad) => $ad->name,
            ],
            [
                'header'      => 'description',
                'label'       => 'Описание',
                'description' => 'Текстовое описание товара без HTML-тегов.',
                'resolve'     => fn(Ad $ad) => html_entity_decode(strip_tags($ad->content ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            ],
            [
                'header'      => 'link',
                'label'       => 'Ссылка',
                'description' => 'Полный URL-адрес страницы товара на сайте.',
                'resolve'     => fn(Ad $ad) => $ad->full_url,
            ],
            [
                'header'      => 'image_link',
                'label'       => 'Изображение',
                'description' => 'Полный URL-адрес главного изображения товара.',
                'resolve'     => function (Ad $ad) {
                    $image = $ad->image;
                    if (!$image) {
                        return '';
                    }
                    return strpos($image, 'http') === 0 ? $image : asset($image);
                },
            ],
            [
                'header'      => 'price',
                'label'       => 'Цена',
                'description' => 'Цена товара с ISO-кодом валюты (напр. 100.00 UAH).',
                'resolve'     => function (Ad $ad) {
                    $amount = number_format((float) $ad->price, 2, '.', '');
                    $code   = $ad->currency ? strtoupper($ad->currency->code) : 'UAH';
                    return $amount . ' ' . $code;
                },
            ],
            [
                'header'      => 'availability',
                'label'       => 'Наличие',
                'description' => 'Наличие: in_stock (есть на складе) или out_of_stock.',
                'resolve'     => function (Ad $ad) {
                    $stock = $ad->stock;
                    if ($stock === 'out_of_stock') return 'out_of_stock';
                    return 'in_stock';
                },
            ],
            [
                'header'      => 'condition',
                'label'       => 'Состояние',
                'description' => 'Состояние товара: new (новый), used (б/у), refurbished (восстановленный).',
                'resolve'     => function (Ad $ad) {
                    $condition = $ad->condition ?? '';
                    if ($condition === 'used') return 'used';
                    if ($condition === 'refurbished') return 'refurbished';
                    return 'new';
                },
            ],
            [
                'header'      => 'brand',
                'label'       => 'Бренд',
                'description' => 'Название бренда или производителя товара.',
                'resolve'     => fn(Ad $ad) => $ad->brand ?? '',
            ],
            [
                'header'      => 'mpn',
                'label'       => 'Артикул',
                'description' => 'Артикул или код товара (MPN — Manufacturer Part Number).',
                'resolve'     => fn(Ad $ad) => $ad->code ?? '',
            ],
            [
                'header'      => 'additional_image_link',
                'label'       => 'Додаткові зображення',
                'description' => 'URL-адреси додаткових зображень товару (через кому).',
                'resolve'     => function (Ad $ad) {
                    $images = array_filter((array) $ad->images);
                    $urls = array_map(
                        fn($img) => strpos($img, 'http') === 0 ? $img : asset($img),
                        $images
                    );
                    return implode(',', array_values($urls));
                },
            ],
        ];
    }

    /**
     * Повертає поля для відображення на сторінці (без resolve-замикань).
     */
    public function getFields(): array
    {
        return array_map(
            fn($field) => [
                'header'      => $field['header'],
                'label'       => $field['label'],
                'description' => $field['description'],
            ],
            $this->fieldDefinitions()
        );
    }

    /**
     * Генерує CSV-файл для поточного користувача та зберігає його у public storage.
     */
    public function generate(User $user): void
    {
        $products = Ad::where('user_id', $user->id)
            ->products()
            ->with('currency')
            ->get();

        $fields = $this->fieldDefinitions();

        $handle = fopen('php://temp', 'r+');

        // UTF-8 BOM для коректного відображення кирилиці в Excel / Google Sheets
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, array_column($fields, 'header'));

        foreach ($products as $product) {
            $row = array_map(fn($field) => ($field['resolve'])($product), $fields);
            fputcsv($handle, $row);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        Storage::disk('public')->put($this->storagePath($user), $contents);
    }

    /**
     * Повертає публічний URL до файлу або null, якщо файл не існує.
     */
    public function getFileUrl(User $user): ?string
    {
        if (Storage::disk('public')->exists($this->storagePath($user))) {
            return asset('storage/' . $this->storagePath($user));
        }

        return null;
    }

    /**
     * Повертає дату останньої генерації або null.
     */
    public function getLastGeneratedAt(User $user): ?string
    {
        $path = $this->storagePath($user);

        if (Storage::disk('public')->exists($path)) {
            return date('d.m.Y H:i', Storage::disk('public')->lastModified($path));
        }

        return null;
    }

    /**
     * Повертає true, якщо після останньої генерації були зміни в товарах.
     * Якщо файл ще не створено — завжди true.
     */
    public function hasChangedSinceExport(User $user): bool
    {
        $path = $this->storagePath($user);

        if (!Storage::disk('public')->exists($path)) {
            return true;
        }

        $exportedAt = Storage::disk('public')->lastModified($path);

        $lastProductChange = Ad::where('user_id', $user->id)
            ->products()
            ->max('updated_at');

        if (!$lastProductChange) {
            return false;
        }

        return strtotime($lastProductChange) > $exportedAt;
    }

    protected function storagePath(User $user): string
    {
        return 'exports/shop-export-' . $user->id . '.csv';
    }
}
