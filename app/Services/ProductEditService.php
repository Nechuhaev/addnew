<?php

namespace App\Services;

use App\Ad;
use App\AdCurrency;
use Illuminate\Support\Facades\Storage;

class ProductEditService
{
    /**
     * Знайти товар користувача для редагування.
     */
    public function findProductForEdit(int $id, $user): ?Ad
    {
        return $user->ads()
            ->where('id', $id)
            ->where('is_product', 1)
            ->first();
    }

    /**
     * Отримати список валют для форми.
     */
    public function getCurrencies(): array
    {
        return AdCurrency::select(['id', 'code'])->get()->toArray();
    }

    /**
     * Правила валідації для форми редагування товару.
     */
    public function validationRules(): array
    {
        return [
            'name' => 'required|min:3|max:255',
            'content' => 'required|min:10',
            'price' => 'required|numeric',
            'currency_id' => 'required|integer|exists:ad_currencies,id',
            'brand' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'competitor_url' => 'nullable|url|max:500',
            'stock' => 'required|in:in_stock,out_of_stock',
            'condition' => 'required|in:new,used,refurbished',
            'image.*' => 'nullable|sometimes|image|max:1024|mimes:jpg,jpeg,bmp,png',
        ];
    }

    /**
     * Повідомлення помилок валідації.
     */
    public function validationMessages(): array
    {
        return [
            'name.required' => 'Введіть назву товару',
            'name.min' => 'Мінімальна довжина назви товару — :min символів',
            'content.required' => 'Введіть опис товару',
            'content.min' => 'Мінімальна довжина опису — :min символів',
            'price.required' => 'Введіть ціну товару',
            'price.numeric' => 'Ціна має бути числом',
            'currency_id.required' => 'Виберіть валюту',
            'currency_id.exists' => 'Виберіть валюту зі списку',
            'competitor_url.url' => 'Введіть коректне посилання (https://...)',
            'stock.required' => 'Виберіть статус наявності',
            'condition.required' => 'Виберіть стан товару',
            'image.*.image' => 'Недопустимий формат зображення',
            'image.*.mimes' => 'Недопустимий формат зображення',
            'image.*.max' => 'Недопустимий розмір файлу. Максимальний розмір: :max КБ',
        ];
    }

    /**
     * Оновити товар даними з форми.
     */
    public function updateProduct(Ad $product, array $data, ?array $imageSlots = []): Ad
    {
        $product->name = strip_tags($data['name']);
        $product->content = strip_tags($data['content']);
        $product->price = (float) $data['price'];
        $product->currency_id = $data['currency_id'];
        $product->brand = $data['brand'] ?? null;
        $product->code = $data['code'] ?? null;
        $product->competitor_url = $data['competitor_url'] ?? null;
        $product->stock = $data['stock'];
        $product->condition = $data['condition'];

        $this->processImageSlots($product, $imageSlots, $data['delete_image_slots'] ?? []);

        $product->save();

        return $product;
    }

    /**
     * Обробити слоти зображень.
     *
     * @param Ad $product
     * @param array $imageSlots ['0' => [UploadedFile, ...], '1' => [...], ...]
     * @param array $deleteSlots ['1' => 1, '3' => 1] - індекси слотів для видалення
     */
    protected function processImageSlots(Ad $product, array $imageSlots, array $deleteSlots): void
    {
        if (empty($imageSlots) && empty($deleteSlots)) {
            return;
        }

        $disk = Storage::cloud();
        $currentImages = $this->getCurrentImages($product);

        foreach ($deleteSlots as $slotIndex => $value) {
            if ($value && !empty($currentImages[$slotIndex])) {
                $this->deleteImageFromStorage($disk, $currentImages[$slotIndex]);
                $currentImages[$slotIndex] = null;
            }
        }

        foreach ($imageSlots as $slotIndex => $files) {
            if (empty($files)) continue;

            if (!empty($currentImages[$slotIndex])) {
                $this->deleteImageFromStorage($disk, $currentImages[$slotIndex]);
            }

            $uploaded = $this->uploadSlotImages($disk, $files);

            $currentImages[$slotIndex] = $uploaded['main'];

            $fillIndex = $slotIndex + 1;
            foreach ($uploaded['additional'] as $addFile) {
                while ($fillIndex < 5 && !empty($currentImages[$fillIndex])) {
                    $fillIndex++;
                }
                if ($fillIndex < 5) {
                    $currentImages[$fillIndex] = $addFile;
                    $fillIndex++;
                }
            }
        }

        $currentImages = array_values(array_filter($currentImages));

        $product->image = $currentImages[0] ?? null;
        $product->images = array_slice($currentImages, 1);
    }

    /**
     * Отримати поточні зображення у форматі [0 => main, 1 => add1, 2 => add2, ...]
     */
    protected function getCurrentImages(Ad $product): array
    {
        $images = [];
        $images[0] = $product->image;

        foreach ($product->images as $img) {
            if ($img) {
                $images[] = $img;
            }
        }

        return $images;
    }

    /**
     * Видалити зображення з S3.
     */
    protected function deleteImageFromStorage($disk, string $url): void
    {
        $baseUrl = rtrim($disk->url(''), '/') . '/';
        if (strpos($url, $baseUrl) === 0) {
            $path = substr($url, strlen($baseUrl));
        } else {
            $path = ltrim(parse_url($url, PHP_URL_PATH), '/');
        }

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    /**
     * Завантажити файли для слота.
     *
     * @param UploadedFile[] $files
     * @return array ['main' => string|null, 'additional' => array]
     */
    protected function uploadSlotImages($disk, array $files): array
    {
        $dir = 'ads/' . time();

        $main = null;
        $additional = [];

        foreach ($files as $key => $file) {
            $filename = $key . '.' . $file->getClientOriginalExtension();
            $filepath = $dir . '/' . $filename;

            $disk->putFileAs($dir, $file, $filename, 'public');
            $url = $disk->url($filepath);

            if ($key === 0) {
                $main = $url;
            } else {
                $additional[] = $url;
            }
        }

        return [
            'main' => $main,
            'additional' => $additional,
        ];
    }
}