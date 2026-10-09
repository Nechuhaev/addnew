<?php

namespace App\Http\Controllers\Front\User\Shop;

use App\Http\Controllers\Controller;
use App\ShopFeed;
use App\Services\Import\FeedFetcher;
use App\Services\Import\ShopFeedSync;
use Illuminate\Http\Request;

/**
 * Кабінет магазину: налаштування автооновлення фіда за URL.
 */
class ShopFeedController extends Controller
{
    protected function t(string $uk, string $ru): string
    {
        return app()->getLocale() === 'ru' ? $ru : $uk;
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'url' => 'required|url|max:1000',
            'frequency_hours' => 'required|in:' . implode(',', ShopFeed::FREQUENCIES),
            'category_id' => 'required|integer|exists:ad_categories,id',
            'city_id' => 'required|integer|exists:ad_cities,id',
        ], [
            'url.required' => $this->t('Вкажіть посилання на фід.', 'Укажите ссылку на фид.'),
            'url.url' => $this->t('Посилання має починатися з http:// або https://', 'Ссылка должна начинаться с http:// или https://'),
            'category_id.required' => $this->t('Оберіть категорію для нових товарів.', 'Выберите категорию для новых товаров.'),
            'city_id.required' => $this->t('Оберіть місто для нових товарів.', 'Выберите город для новых товаров.'),
        ]);

        try {
            FeedFetcher::assertPublicUrl($data['url']);
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['url' => $e->getMessage()]);
        }

        $feed = ShopFeed::firstOrNew(['user_id' => auth()->id()]);
        $urlChanged = !$feed->exists || $feed->url !== $data['url'];
        $feed->fill($data);
        $feed->enabled = $request->boolean('enabled', true);
        if ($urlChanged) {
            $feed->fail_count = 0;
            $feed->last_error = null;
            $feed->next_run_at = now(); // нове посилання — оновити найближчим часом
        } elseif (!$feed->next_run_at) {
            $feed->next_run_at = now();
        }
        $feed->save();

        return back()->with('feed_success', $urlChanged
            ? $this->t('Фід збережено. Перше оновлення почнеться протягом 5 хвилин.', 'Фид сохранён. Первое обновление начнётся в течение 5 минут.')
            : $this->t('Налаштування фіда збережено.', 'Настройки фида сохранены.'));
    }

    /** AJAX: перевірити посилання (завантажити й розпізнати, без імпорту) */
    public function check(Request $request)
    {
        $request->validate(['url' => 'required|url|max:1000']);
        try {
            $result = ShopFeedSync::check($request->input('url'), auth()->id());
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
        if ($result['count'] === 0) {
            return response()->json(['success' => false, 'message' => $this->t(
                'Файл завантажено, але товарів у ньому не знайдено. Підтримуються YML, Google Merchant XML і CSV.',
                'Файл загружен, но товаров в нём не найдено. Поддерживаются YML, Google Merchant XML и CSV.'
            )], 422);
        }
        return response()->json(['success' => true] + $result);
    }

    public function run()
    {
        $feed = ShopFeed::where('user_id', auth()->id())->firstOrFail();
        if ($feed->isRunning()) {
            return back()->with('feed_success', $this->t('Фід уже оновлюється.', 'Фид уже обновляется.'));
        }
        $feed->forceFill(['next_run_at' => now(), 'enabled' => true])->save();

        return back()->with('feed_success', $this->t('Оновлення почнеться протягом 5 хвилин.', 'Обновление начнётся в течение 5 минут.'));
    }

    public function destroy()
    {
        ShopFeed::where('user_id', auth()->id())->delete();

        return back()->with('feed_success', $this->t('Автооновлення вимкнено, фід видалено. Товари лишились на сайті.', 'Автообновление выключено, фид удалён. Товары остались на сайте.'));
    }
}
