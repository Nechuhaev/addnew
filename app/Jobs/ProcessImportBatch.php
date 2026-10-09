<?php

namespace App\Jobs;

use App\Import;
use App\Services\Import\ImportFormatDetector;
use App\Services\Import\ImportRecordProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

        $batchRecords = array_slice($allRecords, $this->offset, $this->limit);

        [$newCount, $updateCount, $errorCount] = (new ImportRecordProcessor())->process($import, $batchRecords);

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
}
