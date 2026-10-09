<?php

namespace App\Services\Import;

use App\Ad;
use App\Import;
use App\Mail\ShopAdminMessage;
use App\ShopFeed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Одне оновлення фіда магазину: завантажити → розпізнати формат → оновити/створити
 * товари (ImportRecordProcessor) → позначити зниклі з фіда «немає в наявності».
 * Кожен запуск — запис у imports (feed_id), видно в кабінеті й адмінці.
 */
class ShopFeedSync
{
    const BATCH = 50;

    /** Перевірка посилання без імпорту: [format, count, sample[]] */
    public static function check(string $url, int $userId): array
    {
        $path = FeedFetcher::download($url, 'feedcheck_' . $userId . '_' . time());
        try {
            $format = ImportFormatDetector::detect($path);
            $records = $format->parse($path);
        } catch (\Throwable $e) {
            @unlink($path);
            throw new \RuntimeException('Не вдалося прочитати фід: формат не розпізнано.');
        }
        @unlink($path);

        return [
            'format' => static::formatName($format),
            'count' => count($records),
            'sample' => array_map(function ($r) {
                return ['title' => $r['title'], 'price' => $r['price'], 'currency' => $r['currency_code']];
            }, array_slice($records, 0, 3)),
        ];
    }

    public static function formatName($format): string
    {
        if ($format instanceof YmlImportFormat) return 'YML';
        if ($format instanceof XmlImportFormat) return 'Google Merchant XML';
        return 'CSV';
    }

    public function run(ShopFeed $feed): Import
    {
        $feed->forceFill(['status' => 'running', 'started_at' => now(), 'last_run_at' => now()])->save();

        $import = Import::create([
            'user_id' => $feed->user_id,
            'feed_id' => $feed->id,
            'category_id' => $feed->category_id,
            'city_id' => $feed->city_id,
            'file_path' => $feed->url,
            'status' => Import::STATUS_PROCESSING,
        ]);

        $path = null;
        try {
            $path = FeedFetcher::download($feed->url, 'feed_' . $feed->id . '_' . time());
            $records = ImportFormatDetector::detect($path)->parse($path);
            @unlink($path);

            if (!$records) {
                // Порожній результат — швидше помилка фіда; не «ховаємо» всі товари
                throw new \RuntimeException('У фіді не знайдено жодного товару (формат не розпізнано або файл порожній).');
            }

            $import->update(['total' => count($records)]);
            $processor = new ImportRecordProcessor();
            $import->setRelation('user', $feed->user);

            foreach (array_chunk($records, self::BATCH) as $chunk) {
                [$new, $update, $error] = $processor->process($import, $chunk);
                $import->increment('processed', count($chunk));
                $import->increment('new_count', $new);
                $import->increment('update_count', $update);
                $import->increment('error_count', $error);
            }

            $missing = $this->markMissing($feed, $records);

            $import->update(['status' => Import::STATUS_COMPLETED, 'missing_count' => $missing]);
            $feed->forceFill(['status' => 'ok', 'last_error' => null, 'fail_count' => 0, 'last_success_at' => now()]);
        } catch (\Throwable $e) {
            if ($path) {
                @unlink($path);
            }
            $message = $e instanceof \RuntimeException ? $e->getMessage() : 'Внутрішня помилка імпорту.';
            if (!$e instanceof \RuntimeException) {
                Log::error('Feed sync failed', ['feed_id' => $feed->id, 'error' => $e->getMessage()]);
            }
            $import->update(['status' => Import::STATUS_FAILED, 'error_message' => $message]);
            $feed->forceFill(['status' => 'failed', 'last_error' => $message, 'fail_count' => $feed->fail_count + 1]);
            if ($feed->fail_count === ShopFeed::FAIL_NOTIFY_AFTER) {
                $this->notifyFailure($feed, $message);
            }
        }

        $feed->scheduleNext();
        $feed->save();

        return $import->fresh();
    }

    /** Товари магазину з імпорту, яких більше немає у фіді → «немає в наявності» */
    protected function markMissing(ShopFeed $feed, array $records): int
    {
        $keys = [];
        foreach ($records as $r) {
            if (!empty($r['source_id'])) {
                $keys['exp_' . $r['source_id']] = true;
            }
        }
        if (!$keys) {
            return 0;
        }

        $ids = [];
        Ad::where('user_id', $feed->user_id)
            ->where('is_product', 1)
            ->where('import_key', 'LIKE', 'exp\\_%')
            ->where(function ($q) {
                $q->whereNull('stock')->orWhere('stock', '!=', 'out_of_stock');
            })
            ->select(['id', 'import_key'])
            ->chunkById(1000, function ($ads) use (&$ids, $keys) {
                foreach ($ads as $ad) {
                    if (!isset($keys[$ad->import_key])) {
                        $ids[] = $ad->id;
                    }
                }
            });

        foreach (array_chunk($ids, 500) as $chunk) {
            DB::table('ads')->whereIn('id', $chunk)->update(['stock' => 'out_of_stock', 'updated_at' => now()]);
        }

        return count($ids);
    }

    protected function notifyFailure(ShopFeed $feed, string $message): void
    {
        $user = $feed->user;
        if (!$user || !$user->email) {
            return;
        }
        $body = '<p>Добрий день!</p>'
            . '<p>Ми ' . ShopFeed::FAIL_NOTIFY_AFTER . ' рази поспіль не змогли оновити товари вашого магазину з фіда на addnew.biz.</p>'
            . '<p><b>Посилання:</b> ' . e($feed->url) . '<br><b>Помилка:</b> ' . e($message) . '</p>'
            . '<p>Перевірте, будь ласка, що посилання відкривається, і за потреби змініть його в кабінеті: '
            . '<a href="' . e(route('profile.shop.import')) . '">' . e(route('profile.shop.import')) . '</a>.</p>'
            . '<p>Поки фід не оновлюється, ціни й наявність на addnew.biz можуть бути застарілими.</p>';
        try {
            Mail::to($user->email)->send(new ShopAdminMessage('Не вдається оновити товари з фіда — addnew.biz', $body));
        } catch (\Throwable $e) {
            Log::warning('Feed failure email not sent', ['feed_id' => $feed->id, 'error' => $e->getMessage()]);
        }
    }
}
