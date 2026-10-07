<?php

namespace App\Console\Commands;

use App\Services\SiteContactEmailFinder;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckShopEmails extends Command
{
    /**
     * php artisan shops:check-email
     *
     * Для кожного магазину із заповненим site_url спершу перевіряє, чи
     * сайт ВЗАГАЛІ відповідає (окремим швидким запитом) — і тільки якщо
     * так, шукає на ньому реальний контактний email (mailto: посилання
     * в першу чергу, як найнадійніше джерело). Якщо знайдений email
     * відрізняється від того, що вказаний у нас — автоматично оновлює
     * поле email магазину й позначає це в лозі (звідти список магазинів
     * покаже попереджувальну іконку). Якщо збігається — просто фіксує
     * "OK", і список підсвітить email зеленим.
     *
     * Домен, що НЕ відповідає взагалі (DNS-помилка, timeout, відмова
     * з'єднання), логується окремим статусом domain_unreachable —
     * раніше це виглядало так само, як "сайт живий, email не знайдено",
     * і їх неможливо було розрізнити в логах.
     */
    protected $signature = 'shops:check-email {--limit=} {--user=}';

    protected $description = 'Звіряє email магазину з тим, що реально вказано на сайті магазину';

    /** @var SiteContactEmailFinder */
    protected $finder;

    public function __construct()
    {
        parent::__construct();
        $this->finder = new SiteContactEmailFinder();
    }

    public function handle()
    {
        $limit = (int) ($this->option('limit') ?: env('SHOP_EMAIL_CHECK_PER_RUN', 20));
        $userId = $this->option('user');

        $query = User::whereHas('ads', function ($q) {
                $q->where('is_product', 1);
            })
            ->whereNotNull('site_url')
            ->where('site_url', '!=', '');

        if ($userId) {
            $query->where('id', $userId);
        } else {
            $query->orderByRaw('(SELECT MAX(checked_at) FROM shop_email_checks WHERE shop_email_checks.user_id = users.id) IS NOT NULL')
                ->orderByRaw('(SELECT MAX(checked_at) FROM shop_email_checks WHERE shop_email_checks.user_id = users.id) ASC')
                ->limit($limit);
        }

        $shops = $query->get();

        if ($shops->isEmpty()) {
            $this->info('Немає магазинів із заповненим site_url для перевірки.');
            return 0;
        }

        $this->info('Перевіряю email для ' . $shops->count() . ' магазинів(ну)');

        foreach ($shops as $i => $shop) {
            $this->info('[' . ($i + 1) . '/' . $shops->count() . "] {$shop->username} ({$shop->site_url})");

            try {
                $this->checkOne($shop);
            } catch (\Throwable $e) {
                $this->error('Помилка: ' . $e->getMessage());
            }

            usleep(300000);
        }

        return 0;
    }

    protected function checkOne(User $shop): void
    {
        $result = $this->finder->find($shop->site_url);

        if ($result['status'] === 'unreachable') {
            $this->warn('Домен недоступний — сайт не відповідає (DNS-помилка, timeout або відмова з\'єднання)');
            $this->logCheck($shop->id, $shop->email, null, false, 'domain_unreachable');
            return;
        }

        $foundEmail = $result['email'];
        if ($foundEmail && $result['page'] !== '/') {
            $this->info("  (знайдено на {$result['page']})");
        }

        if (!$foundEmail) {
            $this->warn('Сайт живий, але email не знайдено ані на головній, ані на сторінках контактів');
            $this->logCheck($shop->id, $shop->email, null, false, 'not_found');
            return;
        }

        $matches = strtolower(trim($foundEmail)) === strtolower(trim($shop->email));

        if ($matches) {
            $this->info('Email збігається: OK');
            $this->logCheck($shop->id, $shop->email, $foundEmail, true, 'ok');
            return;
        }

        // Захист від дублікатів: якщо знайдений email уже належить
        // ІНШОМУ магазину — це майже напевно спільний технічний email
        // платформи (як s@prom.ua), а не персональний контакт продавця.
        // НЕ зберігаємо, тільки логуємо для видимості.
        $ownedByOther = User::where('email', $foundEmail)
            ->where('id', '!=', $shop->id)
            ->exists();

        if ($ownedByOther) {
            $this->warn("Знайдений email '{$foundEmail}' уже належить іншому магазину — ігнорую (схоже на спільний технічний email платформи)");
            $this->logCheck($shop->id, $shop->email, $foundEmail, false, 'duplicate_ignored');
            return;
        }

        $oldEmail = $shop->email;
        $shop->email = $foundEmail;
        $shop->save();

        $this->warn("Email оновлено: {$oldEmail} -> {$foundEmail}");
        $this->logCheck($shop->id, $oldEmail, $foundEmail, false, 'updated');
    }

    protected function logCheck(int $userId, ?string $oldEmail, ?string $foundEmail, bool $matched, string $status): void
    {
        DB::table('shop_email_checks')->insert([
            'user_id' => $userId,
            'old_email' => $oldEmail,
            'found_email' => $foundEmail,
            'matched' => $matched,
            'status' => $status,
            'checked_at' => now(),
        ]);
    }
}
