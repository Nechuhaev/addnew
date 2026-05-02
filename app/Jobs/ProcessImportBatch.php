<?php

namespace App\Jobs;

use App\Ad;
use App\AdCurrency;
use App\Import;
use App\Services\Import\ImportFormatDetector;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
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

        $format = ImportFormatDetector::detect($import->file_path);
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

                if (empty($images)) {
                    $errorCount++;
                    continue;
                }

                $mainImage = $images[0];
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
                Log::error('Import batch error: ' . $e->getMessage(), [
                    'record' => $record['source_id'] ?? null,
                    'trace' => $e->getTraceAsString(),
                ]);
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
        $disk = Storage::cloud();
        $uploadedUrls = [];
        $dir = 'imports/' . $userId . '/' . time();

        $ownBases = array_filter([
            rtrim($disk->url(''), '/'),
            ($b = config('filesystems.disks.s3.bucket')) ? "https://{$b}.s3" : null,
        ]);

        foreach ($imageUrls as $index => $url) {
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

                $contentType = $response->getHeaderLine('Content-Type');
                $extension = $this->getImageExtension($url, $contentType);
                $filename = $index . '.' . $extension;
                $path = $dir . '/' . $filename;

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

        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
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
            $path = ltrim(parse_url($url, PHP_URL_PATH), '/');
        }

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}
