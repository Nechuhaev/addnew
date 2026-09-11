<?php

namespace App\Observers;

use App\AdTag;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class AdTagObserver
{
    /**
     * Одразу після створення нового тега (наприклад, коли відвідувач
     * подає оголошення з новою міткою) — синхронно генеруємо унікальний
     * SEO-текст, щоб не чекати щоденного пакетного запуску. Затримка
     * ~2-5 сек на запит до LLM для користувача, що створив тег —
     * прийнятна (підтверджено власником сайту).
     *
     * Якщо генерація впаде (немає кредитів, усі провайдери недоступні
     * тощо) — тег просто лишається seo_optimized=false і його підхопить
     * наступний щоденний пакетний прогін tags:seo-optimize.
     */
    public function created(AdTag $tag)
    {
        try {
            Artisan::call('tags:seo-optimize', ['--only' => $tag->id]);
        } catch (\Throwable $e) {
            Log::warning("AdTagObserver: не вдалося згенерувати SEO для нового тега ID={$tag->id}: " . $e->getMessage());
        }
    }
}
