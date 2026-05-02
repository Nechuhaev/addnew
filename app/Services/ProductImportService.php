<?php

namespace App\Services;

use App\Ad;
use App\AdCurrency;
use App\Import;
use App\Jobs\ProcessImportBatch;
use App\User;
use Illuminate\Support\Facades\Storage;
use App\Services\Import\ImportFormatInterface;

class ProductImportService
{
    protected const BATCH_SIZE = 50;

    public function processFile(string $filePath, ImportFormatInterface $format, User $user): array
    {
        $records = $format->parse($filePath);

        $currencies = AdCurrency::all()->keyBy('code');

        $importKeys = [];
        foreach ($records as $record) {
            if (!empty($record['source_id'])) {
                $importKeys[] = 'exp_' . $record['source_id'];
            }
        }

        $existingKeys = [];
        if (!empty($importKeys)) {
            $existingKeys = Ad::where('user_id', $user->id)
                ->where('is_product', 1)
                ->whereIn('import_key', $importKeys)
                ->pluck('import_key')
                ->toArray();
        }

        $newCount = 0;
        $updateCount = 0;
        $previewProducts = [];
        $validRecords = [];

        foreach ($records as $record) {
            $currency = $currencies->get($record['currency_code']);
            if (!$currency) {
                continue;
            }

            $importKey = 'exp_' . $record['source_id'];
            $isExisting = in_array($importKey, $existingKeys);
            if ($isExisting) {
                $updateCount++;
                $status = 'update';
            } else {
                $newCount++;
                $status = 'new';
            }

            $validRecords[] = $record;

            $previewProducts[] = [
                'source_id' => $record['source_id'],
                'title' => $record['title'],
                'price' => $record['price'],
                'currency_code' => $record['currency_code'],
                'brand' => $record['brand'],
                'image_link' => $record['images'][0] ?? '',
                'status' => $status,
            ];
        }

        return [
            'total' => count($validRecords),
            'new_count' => $newCount,
            'update_count' => $updateCount,
            'preview' => $this->getRandomPreview($previewProducts, 20),
        ];
    }

    public function startImport(User $user, string $filePath, int $total, int $newCount, int $updateCount): Import
    {
        $import = Import::create([
            'user_id' => $user->id,
            'file_path' => $filePath,
            'status' => Import::STATUS_PENDING,
            'total' => $total,
            'new_count' => 0,
            'update_count' => 0,
            'error_count' => 0,
        ]);

        $offset = 0;
        while ($offset < $total) {
            ProcessImportBatch::dispatch($import->id, $offset, self::BATCH_SIZE);
            $offset += self::BATCH_SIZE;
        }

        $import->update(['status' => Import::STATUS_PROCESSING]);

        return $import;
    }

    public function getProgress(User $user): ?array
    {
        $import = Import::where('user_id', $user->id)
            ->whereIn('status', [Import::STATUS_PENDING, Import::STATUS_PROCESSING])
            ->latest()
            ->first();

        if (!$import) {
            return null;
        }

        return [
            'id' => $import->id,
            'status' => $import->status,
            'total' => $import->total,
            'processed' => $import->processed,
            'new_count' => $import->new_count,
            'update_count' => $import->update_count,
            'error_count' => $import->error_count,
            'progress' => $import->progress,
        ];
    }

    public function cancelImport(User $user): bool
    {
        $imports = Import::where('user_id', $user->id)
            ->whereIn('status', [Import::STATUS_PENDING, Import::STATUS_PROCESSING])
            ->get();

        if ($imports->isEmpty()) {
            return false;
        }

        foreach ($imports as $import) {
            $import->update(['status' => Import::STATUS_CANCELLED]);

            if (file_exists($import->file_path)) {
                @unlink($import->file_path);
            }
        }

        return true;
    }

    public function getHistory(User $user, int $limit = 15): array
    {
        return Import::where('user_id', $user->id)
            ->whereIn('status', [Import::STATUS_COMPLETED, Import::STATUS_CANCELLED, Import::STATUS_FAILED])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($import) {
                return [
                    'id' => $import->id,
                    'status' => $import->status,
                    'total' => $import->total,
                    'new_count' => $import->new_count,
                    'update_count' => $import->update_count,
                    'error_count' => $import->error_count,
                    'created_at' => $import->created_at->format('d.m.Y H:i'),
                ];
            })
            ->toArray();
    }

    protected function getRandomPreview(array $products, int $limit): array
    {
        if (count($products) <= $limit) {
            return $products;
        }

        $shuffled = $products;
        shuffle($shuffled);

        return array_slice($shuffled, 0, $limit);
    }
}
