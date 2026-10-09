<?php

namespace App\Services\Import;

use App\Ad;
use App\AdCurrency;
use App\Import;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Запис нормалізованих записів імпорту (AbstractImportFormat::normalizeRecord)
 * у товари магазину. Спільний для ручного завантаження (ProcessImportBatch)
 * і автооновлення фіда (ShopFeedSync).
 *
 * Фото перезавантажуються лише тоді, коли змінився їхній список у фіді
 * (ads.import_images_hash) — інакше щоденне оновлення тисяч товарів щоразу
 * качало б і заливало на S3 всі фото наново.
 */
class ImportRecordProcessor
{
    /** @var \Illuminate\Support\Collection|null */
    protected $currencies;

    /**
     * @return array [new, update, error]
     */
    public function process(Import $import, array $records): array
    {
        $this->currencies = $this->currencies ?: AdCurrency::all()->keyBy('code');

        $importKeys = [];
        foreach ($records as $record) {
            if (!empty($record['source_id'])) {
                $importKeys[] = 'exp_' . $record['source_id'];
            }
        }

        $existingProducts = collect();
        if ($importKeys) {
            $existingProducts = Ad::where('user_id', $import->user_id)
                ->where('is_product', 1)
                ->whereIn('import_key', $importKeys)
                ->get()
                ->keyBy('import_key');
        }

        $new = $update = $error = 0;

        foreach ($records as $i => $record) {
            // Скасування імпорту користувачем — перевіряємо кожні 10 записів
            if ($i > 0 && $i % 10 === 0 && Import::where('id', $import->id)->value('status') === Import::STATUS_CANCELLED) {
                break;
            }
            try {
                if (empty($record['source_id'])) {
                    $error++;
                    continue;
                }
                $currency = $this->currencies->get($record['currency_code']);
                if (!$currency) {
                    $error++;
                    continue;
                }

                $importKey = 'exp_' . $record['source_id'];
                $existing = $existingProducts->get($importKey);
                $imagesHash = md5(json_encode(array_values($record['images'])));

                if ($existing) {
                    if ($existing->import_images_hash !== $imagesHash || !$existing->image) {
                        $images = $this->downloadAndUploadImages($record['images'], $import->user_id, $importKey);
                        if ($images) {
                            $this->deleteOldImages($existing);
                            $existing->image = $images[0];
                            $existing->images = array_slice($images, 1);
                            $existing->import_images_hash = $imagesHash;
                        }
                    }

                    $existing->name = $record['title'];
                    $existing->content = $record['description'];
                    $existing->price = $record['price'];
                    $existing->currency_id = $currency->id;
                    $existing->brand = $record['brand'];
                    $existing->code = $record['code'];
                    $existing->gtin = $record['gtin'];
                    $existing->stock = $record['stock'];
                    $existing->condition = $record['condition'];
                    $existing->url = $record['link'];
                    $existing->save();

                    $update++;
                    continue;
                }

                // Новий товар: без категорії й міста створити не можна (обов'язкові поля)
                if (!$import->category_id || !$import->city_id) {
                    $error++;
                    continue;
                }

                $images = $this->downloadAndUploadImages($record['images'], $import->user_id, $importKey);
                if (!$images) {
                    $error++;
                    continue;
                }

                Ad::create([
                    'user_id' => $import->user_id,
                    'category_id' => $import->category_id,
                    'city_id' => $import->city_id,
                    'source_id' => $record['source_id'],
                    'import_key' => $importKey,
                    'name' => $record['title'],
                    'content' => $record['description'],
                    'price' => $record['price'],
                    'currency_id' => $currency->id,
                    'brand' => $record['brand'],
                    'code' => $record['code'],
                    'gtin' => $record['gtin'],
                    'stock' => $record['stock'],
                    'condition' => $record['condition'],
                    'url' => $record['link'],
                    'is_product' => 1,
                    'image' => $images[0],
                    'images' => array_slice($images, 1),
                    'telephone' => $import->user->telephone ?? '',
                    'email' => $import->user->email,
                    'status' => 1,
                    'date_active' => now(),
                ])->forceFill(['import_images_hash' => $imagesHash])->save();

                $new++;
            } catch (\Throwable $e) {
                $error++;
                Log::error('Import batch error: ' . $e->getMessage(), [
                    'import_id' => $import->id,
                    'record' => $record['source_id'] ?? null,
                ]);
            }
        }

        return [$new, $update, $error];
    }

    /**
     * Завантажує фото на наш диск. Шлях містить ключ товару: раніше всі товари,
     * оброблені в ту саму секунду, писали у спільні imports/{user}/{time}/0.jpg
     * і затирали фото одне одного.
     */
    protected function downloadAndUploadImages(array $imageUrls, int $userId, string $importKey): array
    {
        $disk = Storage::cloud();
        $uploadedUrls = [];
        $dir = 'imports/' . $userId . '/' . substr(md5($importKey), 0, 12) . '-' . time();

        $ownBases = array_filter([
            rtrim($disk->url(''), '/'),
            ($b = config('filesystems.disks.s3.bucket')) ? "https://{$b}.s3" : null,
        ]);

        foreach (array_slice($imageUrls, 0, 10) as $index => $url) {
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            // Якщо URL вже з нашого сховища або S3 — не перезавантажуємо
            foreach ($ownBases as $base) {
                if (strpos($url, $base) === 0) {
                    $uploadedUrls[] = $url;
                    continue 2;
                }
            }

            try {
                $client = new Client(['timeout' => 30, 'verify' => false]);
                $response = $client->get($url, [
                    'headers' => ['User-Agent' => 'Mozilla/5.0 (compatible; bot)'],
                ]);

                if ($response->getStatusCode() !== 200) {
                    continue;
                }

                $body = $response->getBody()->getContents();
                if (empty($body)) {
                    continue;
                }

                $extension = $this->getImageExtension($url, $response->getHeaderLine('Content-Type'));
                $path = $dir . '/' . $index . '.' . $extension;

                $disk->put($path, $body, 'public');
                $uploadedUrls[] = $disk->url($path);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $uploadedUrls;
    }

    protected function getImageExtension(string $url, ?string $contentType = null): string
    {
        if ($contentType) {
            $mimeMap = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
                'image/bmp'  => 'bmp',
            ];
            foreach ($mimeMap as $mime => $ext) {
                if (strpos($contentType, $mime) !== false) {
                    return $ext;
                }
            }
        }

        $ext = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

        if (in_array($ext, $allowed)) {
            return $ext === 'jpeg' ? 'jpg' : $ext;
        }

        return 'jpg';
    }

    protected function deleteOldImages(Ad $product): void
    {
        $disk = Storage::cloud();

        if ($product->image) {
            $this->deleteImageFromStorage($disk, $product->image);
        }

        foreach ($product->images as $image) {
            if ($image) {
                $this->deleteImageFromStorage($disk, $image);
            }
        }
    }

    protected function deleteImageFromStorage($disk, string $url): void
    {
        $baseUrl = rtrim($disk->url(''), '/') . '/';
        if (strpos($url, $baseUrl) === 0) {
            $path = substr($url, strlen($baseUrl));
        } else {
            // Чуже посилання (не наш диск) не чіпаємо
            return;
        }

        try {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        } catch (\Throwable $e) {
            // не критично
        }
    }
}
