<?php

namespace App\Http\Controllers\Front\User\Shop;

use App\Http\Controllers\Controller;
use App\Services\ProductImportService;
use App\Services\Import\ImportFormatDetector;
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
        $user = auth()->user();
        $progress = $this->importService->getProgress($user);
        $history = $this->importService->getHistory($user);

        // Категорія/місто за замовчуванням: з налаштувань фіда або з останнього імпорту
        $feed = \App\ShopFeed::where('user_id', $user->id)->first();
        $lastImport = \App\Import::where('user_id', $user->id)->whereNotNull('category_id')->latest()->first();

        return view('front.user.profile.shop.import', [
            'progress' => $progress,
            'history' => $history,
            'feed' => $feed,
            'feedRuns' => $feed ? \App\Import::where('feed_id', $feed->id)->latest()->limit(10)->get() : collect(),
            'defaultCategory' => optional($feed)->category_id ?? optional($lastImport)->category_id,
            'defaultCity' => optional($feed)->city_id ?? optional($lastImport)->city_id,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xml,yml|max:25600',
        ]);

        $user = auth()->user();

        try {
            $file = $request->file('file');
            $uniqueName = 'import_' . $user->id . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('imports', $uniqueName, 'local');
            $fullPath = storage_path('app/' . $path);

            $format = ImportFormatDetector::detect($fullPath);
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
            // Для нових товарів обов'язкові (без них товар не створиться)
            'category_id' => 'required_unless:new_count,0|nullable|integer|exists:ad_categories,id',
            'city_id' => 'required_unless:new_count,0|nullable|integer|exists:ad_cities,id',
        ], [
            'category_id.required_unless' => app()->getLocale() === 'ru' ? 'Выберите категорию для новых товаров.' : 'Оберіть категорію для нових товарів.',
            'city_id.required_unless' => app()->getLocale() === 'ru' ? 'Выберите город для новых товаров.' : 'Оберіть місто для нових товарів.',
        ]);

        $user = auth()->user();

        // Шлях приходить із браузера: дозволяємо лише власний файл у storage/app/imports
        $real = realpath((string) $request->file_path);
        $dir = realpath(storage_path('app/imports'));
        if (!$real || !$dir || strpos($real, $dir . DIRECTORY_SEPARATOR) !== 0
            || strpos(basename($real), 'import_' . $user->id . '_') !== 0) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ru' ? 'Файл импорта не найден. Загрузите его ещё раз.' : 'Файл імпорту не знайдено. Завантажте його ще раз.',
            ], 422);
        }

        try {
            $import = $this->importService->startImport(
                $user,
                $real,
                $request->total,
                $request->new_count,
                $request->update_count,
                $request->category_id ? (int) $request->category_id : null,
                $request->city_id ? (int) $request->city_id : null
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
