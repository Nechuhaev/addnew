<?php

namespace App\Http\Controllers\Front\User\Shop;

use App\Http\Controllers\Controller;
use App\Services\ProductImportService;
use App\Services\Import\CsvImportFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductImportController extends Controller
{
    protected $importService;

    public function __construct(ProductImportService $importService)
    {
        $this->importService = $importService;
    }

    public function index()
    {
        $progress = $this->importService->getProgress(auth()->user());

        return view('front.user.profile.shop.import', [
            'progress' => $progress,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:25600',
        ]);

        $user = auth()->user();

        try {
            $file = $request->file('file');
            $uniqueName = 'import_' . $user->id . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('imports', $uniqueName, 'local');
            $fullPath = storage_path('app/' . $path);

            $format = new CsvImportFormat();
            $stats = $this->importService->processFile($fullPath, $format, $user);

            return response()->json([
                'success' => true,
                'data' => $stats,
                'file_path' => $fullPath,
            ]);
        } catch (\Throwable $e) {
            Log::error('Product import upload failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Помилка під час обробки файлу. Переконайтеся, що файл має правильний формат.',
            ], 422);
        }
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
            'total' => 'required|integer',
            'new_count' => 'required|integer',
            'update_count' => 'required|integer',
        ]);

        $user = auth()->user();

        try {
            $import = $this->importService->startImport(
                $user,
                $request->file_path,
                $request->total,
                $request->new_count,
                $request->update_count
            );

            return response()->json([
                'success' => true,
                'import_id' => $import->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Product import start failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Помилка під час запуску імпорту.',
            ], 422);
        }
    }

    public function progress()
    {
        $user = auth()->user();
        $progress = $this->importService->getProgress($user);

        if (!$progress) {
            return response()->json([
                'success' => false,
                'message' => 'Активний імпорт не знайдено.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $progress,
        ]);
    }

    public function cancel()
    {
        $user = auth()->user();

        try {
            $cancelled = $this->importService->cancelImport($user);

            if ($cancelled) {
                return response()->json(['success' => true]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Активний імпорт не знайдено.',
            ], 404);
        } catch (\Throwable $e) {
            Log::error('Product import cancel failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Помилка під час скасування імпорту.',
            ], 422);
        }
    }
}
