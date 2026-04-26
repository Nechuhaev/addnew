<?php

namespace App\Http\Controllers\Front\User\Shop;

use App\Http\Controllers\Controller;
use App\Services\ProductExportService;
use Illuminate\Support\Facades\Log;

class AllActionsController extends Controller
{
    public function index()
    {
        return view('front.user.profile.shop.import-export');
    }

    public function import()
    {
        return view('front.user.profile.shop.import');
    }

    public function export(ProductExportService $exportService)
    {
        $user = auth()->user();

        return view('front.user.profile.shop.export', [
            'fields'          => $exportService->getFields(),
            'fileUrl'         => $exportService->getFileUrl($user),
            'lastGeneratedAt' => $exportService->getLastGeneratedAt($user),
            'hasChanges'      => $exportService->hasChangedSinceExport($user),
        ]);
    }

    public function generateExport(ProductExportService $exportService)
    {
        $user = auth()->user();

        try {
            $exportService->generate($user);
            return redirect()->route('profile.shop.export')
                ->with('success', 'Файл успішно згенеровано.');
        } catch (\Throwable $e) {
            Log::error('Product export failed: ' . $e->getMessage());
            return redirect()->route('profile.shop.export')
                ->with('error', 'Помилка під час генерації файлу. Спробуйте ще раз.');
        }
    }
}
