<?php

namespace App\Jobs;

use App\Ad;
use App\AdCurrency;
use App\Import;
use App\Services\Import\CsvImportFormat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessImportBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 1;

    protected $importId;
    protected $offset;
    protected $limit;

    public function __construct(int $importId, int $offset, int $limit)
    {
        $this->importId = $importId;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function handle()
    {
        $import = Import::find($this->importId);

        if (!$import || $import->status === Import::STATUS_CANCELLED) {
            return;
        }

        if (!file_exists($import->file_path)) {
            $import->update([
                'status' => Import::STATUS_FAILED,
                'error_message' => 'Файл импорта не найден.',
            ]);
            return;
        }

        $format = new CsvImportFormat();
        $allRecords = $format->parse($import->file_path);

        $currencies = AdCurrency::all()->keyBy('code');

        $batchRecords = array_slice($allRecords, $this->offset, $this->limit);

        $importKeys = [];
        foreach ($batchRecords as $record) {
            if (!empty($record['source_id'])) {
                $importKeys[] = 'exp_' . $record['source_id'];
            }
        }

        $existingProducts = [];
        if (!empty($importKeys)) {
            $existingProducts = Ad::where('user_id', $import->user_id)
                ->where('is_product', 1)
                ->whereIn('import_key', $importKeys)
                ->get()
                ->keyBy('import_key');
        }

        $newCount = 0;
        $updateCount = 0;
        $errorCount = 0;

        foreach ($batchRecords as $record) {
            if ($import->fresh()->status === Import::STATUS_CANCELLED) {
                break;
            }

            try {
                $currency = $currencies->get($record['currency_code']);
                if (!$currency) {
                    $errorCount++;
                    continue;
                }

                $images = $this->downloadAndUploadImages($record['images'], $import->user_id);
                $mainImage = $images[0] ?? null;
                $additionalImages = array_slice($images, 1);

                $importKey = 'exp_' . $record['source_id'];
                $existingProduct = $existingProducts->get($importKey);

                if ($existingProduct) {
                    $this->deleteOldImages($existingProduct);

                    $existingProduct->name = $record['title'];
                    $existingProduct->content = $record['description'];
                    $existingProduct->price = $record['price'];
                    $existingProduct->currency_id = $currency->id;
                    $existingProduct->brand = $record['brand'];
                    $existingProduct->code = $record['code'];
                    $existingProduct->stock = $record['stock'];
                    $existingProduct->condition = $record['condition'];
                    $existingProduct->url = $record['link'];
                    $existingProduct->image = $mainImage;
                    $existingProduct->images = $additionalImages;
                    $existingProduct->save();

                    $updateCount++;
                } else {
                    Ad::create([
                        'user_id' => $import->user_id,
                        'source_id' => $record['source_id'],
                        'import_key' => $importKey,
                        'name' => $record['title'],
                        'content' => $record['description'],
                        'price' => $record['price'],
                        'currency_id' => $currency->id,
                        'brand' => $record['brand'],
                        'code' => $record['code'],
                        'stock' => $record['stock'],
                        'condition' => $record['condition'],
                        'url' => $record['link'],
                        'is_product' => 1,
                        'image' => $mainImage,
                        'images' => $additionalImages,
                        'telephone' => $import->user->telephone ?? '',
                        'email' => $import->user->email,
                        'status' => 1,
                        'date_active' => now(),
                    ]);

                    $newCount++;
                }
            } catch (\Throwable $e) {
                $errorCount++;
            }
        }

        $import->increment('processed', count($batchRecords));
        $import->increment('new_count', $newCount);
        $import->increment('update_count', $updateCount);
        $import->increment('error_count', $errorCount);

        $this->checkCompletion($import, $allRecords);
    }

    protected function checkCompletion(Import $import, array $allRecords)
    {
        $import = $import->fresh();

        if ($import->status === Import::STATUS_CANCELLED) {
            return;
        }

        if ($import->processed >= count($allRecords)) {
            $import->update(['status' => Import::STATUS_COMPLETED]);
            @unlink($import->file_path);
        }
    }

    protected function downloadAndUploadImages(array $imageUrls, int $userId): array
    {
        $disk = Storage::disk('s3');
        $uploadedUrls = [];
        $dir = 'imports/' . $userId . '/' . time();

        foreach ($imageUrls as $index => $url) {
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            try {
                $content = @file_get_contents($url);
                if ($content === false) {
                    continue;
                }

                $extension = $this->getImageExtension($url);
                $filename = $index . '.' . $extension;
                $path = $dir . '/' . $filename;

                $disk->put($path, $content, 'public');
                $uploadedUrls[] = $disk->url($path);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $uploadedUrls;
    }

    protected function getImageExtension(string $url): string
    {
        $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $ext = strtolower($ext);

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        if (in_array($ext, $allowed)) {
            return $ext === 'jpeg' ? 'jpg' : $ext;
        }

        return 'jpg';
    }

    protected function deleteOldImages(Ad $product): void
    {
        $disk = Storage::disk('s3');

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
        $path = ltrim(parse_url($url, PHP_URL_PATH), '/');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}
