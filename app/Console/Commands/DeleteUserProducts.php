<?php

namespace App\Console\Commands;

use App\Ad;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteUserProducts extends Command
{
    protected $signature = 'products:delete {user_id : ID пользователя} {--force : Без подтверждения}';
    protected $description = 'Удаляет все товары указанного пользователя';

    public function handle()
    {
        $userId = $this->argument('user_id');

        $products = Ad::where('user_id', $userId)
            ->where('is_product', 1)
            ->get();

        if ($products->isEmpty()) {
            $this->info("У пользователя {$userId} нет товаров.");
            return 0;
        }

        $this->info("Найдено товаров: {$products->count()}");

        if (!$this->option('force')) {
            if (!$this->confirm("Вы уверены, что хотите удалить все {$products->count()} товаров пользователя {$userId}?")) {
                $this->info('Отменено.');
                return 0;
            }
        }

        $deletedImages = 0;
        $disk = Storage::disk('s3');

        $this->withProgressBar($products, function ($product) use ($disk, &$deletedImages) {
            if ($product->image) {
                $this->deleteImage($disk, $product->image);
                $deletedImages++;
            }

            foreach ($product->images as $image) {
                if ($image) {
                    $this->deleteImage($disk, $image);
                    $deletedImages++;
                }
            }

            $product->delete();
        });

        $this->newLine();
        $this->info("Удалено товаров: {$products->count()}");
        $this->info("Удалено изображений: {$deletedImages}");

        return 0;
    }

    protected function deleteImage($disk, string $url): void
    {
        $path = ltrim(parse_url($url, PHP_URL_PATH), '/');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}
