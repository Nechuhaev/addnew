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
    protected $signature = 'shops:check-email {--limit=} {--user=} {--all : усі магазини з site_url, без ліміту} {--dry-run : лише звіт, email у базі не змінюється} {--csv= : шлях до CSV-звіту (за замовчуванням storage/app/shop-email-check-ДАТА.csv при --all)}';

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
        } elseif ($this->option('all')) {
            $query->orderBy('id');
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

        $this->dryRun = (bool) $this->option('dry-run');
        $csvPath = $this->option('csv') ?: ($this->option('all') ? storage_path('app/shop-email-check-' . date('Y-m-d-His') . '.csv') : null);
        $csv = null;
        if ($csvPath) {
            $csv = fopen($csvPath, 'w');
            fwrite($csv, "\xEF\xBB\xBF"); // BOM — щоб Excel відкрив кирилицю
            fputcsv($csv, ['ID', 'Магазин', 'Сайт', 'Email у нас', 'Статус', 'Email на сайті', 'Усі email на сайті', 'Сторінка', 'Адмінка'], ';');
        }

        $this->info('Перевіряю email для ' . $shops->count() . ' магазинів' . ($this->dryRun ? ' (лише звіт, без змін)' : ''));

        $totals = [];
        foreach ($shops as $i => $shop) {
            $this->info('[' . ($i + 1) . '/' . $shops->count() . "] {$shop->username} ({$shop->site_url})");

            try {
                $row = $this->checkOne($shop);
            } catch (\Throwable $e) {
                $this->error('Помилка: ' . $e->getMessage());
                $row = ['status' => 'error', 'found' => null, 'emails' => [], 'page' => null];
            }
            $totals[$row['status']] = ($totals[$row['status']] ?? 0) + 1;

            if ($csv) {
                fputcsv($csv, [
                    $shop->id, $shop->username, $shop->site_url, $row['old'] ?? $shop->email, self::STATUS_LABELS[$row['status']] ?? $row['status'],
                    $row['found'], implode(', ', $row['emails']), $row['page'], url('/admin/shops/' . $shop->id),
                ], ';');
            }

            usleep(300000);
        }

        if ($csv) {
            fclose($csv);
        }

        $this->info('');
        $this->info('=== Підсумок ===');
        foreach ($totals as $status => $n) {
            $this->line('  ' . (self::STATUS_LABELS[$status] ?? $status) . ": {$n}");
        }
        if ($csvPath) {
            $this->info("Звіт: {$csvPath}");
        }

        return 0;
    }

    const STATUS_LABELS = [
        'ok' => 'Збігається',
        'updated' => 'Оновлено на email із сайту',
        'mismatch' => 'НЕ збігається (без змін)',
        'not_found' => 'Email на сайті не знайдено',
        'duplicate_ignored' => 'Email із сайту належить іншому магазину',
        'domain_unreachable' => 'Сайт недоступний',
        'error' => 'Помилка перевірки',
    ];

    /** @var bool */
    protected $dryRun = false;

    protected function checkOne(User $shop): array
    {
        $result = $this->finder->find($shop->site_url, $shop->email);
        $out = ['old' => $shop->email, 'found' => $result['email'], 'emails' => $result['emails'] ?? [], 'page' => $result['page']];

        if ($result['status'] === 'unreachable') {
            $this->warn('Домен недоступний — сайт не відповідає (DNS-помилка, timeout або відмова з\'єднання)');
            $this->logCheck($shop->id, $shop->email, null, false, 'domain_unreachable');
            return $out + ['status' => 'domain_unreachable'];
        }

        $foundEmail = $result['email'];
        if ($foundEmail && $result['page'] !== '/') {
            $this->info("  (знайдено на {$result['page']})");
        }

        if (!$foundEmail) {
            $this->warn('Сайт живий, але email не знайдено ані на головній, ані на сторінках контактів');
            $this->logCheck($shop->id, $shop->email, null, false, 'not_found');
            return $out + ['status' => 'not_found'];
        }

        // Збіг — якщо наш email є серед УСІХ адрес на сайті (футер, контакти, «Про нас»),
        // а не лише дорівнює першій знайденій
        $ours = strtolower(trim((string) $shop->email));
        $matches = $ours !== '' && in_array($ours, array_map('strtolower', $out['emails'] ?: [$foundEmail]), true);

        if ($matches) {
            $this->info('Email збігається: OK');
            $this->logCheck($shop->id, $shop->email, $ours, true, 'ok');
            return array_merge($out, ['found' => $ours, 'status' => 'ok']);
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
            return $out + ['status' => 'duplicate_ignored'];
        }

        if ($this->dryRun) {
            $this->warn("Email НЕ збігається: у нас {$shop->email}, на сайті {$foundEmail} (без змін)");
            $this->logCheck($shop->id, $shop->email, $foundEmail, false, 'mismatch');
            return $out + ['status' => 'mismatch'];
        }

        $oldEmail = $shop->email;
        $shop->email = $foundEmail;
        $shop->save();

        $this->warn("Email оновлено: {$oldEmail} -> {$foundEmail}");
        $this->logCheck($shop->id, $oldEmail, $foundEmail, false, 'updated');

        return $out + ['status' => 'updated'];
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
