<?php

namespace App\Services\Caller;

use App\Models\Caller\DoNotCall;
use App\Models\Caller\Lead;
use App\User;
use League\Csv\Reader;

/**
 * Імпорт лідів із CSV (колонки: phone, store_name, store_site, contact_name, category).
 * Пропускає: невалідні номери, «не дзвонити», дублікати і магазини, що вже зареєстровані на сайті.
 */
class LeadImporter
{
    /** @var array домени вже зареєстрованих магазинів */
    private $shopDomains;

    public function importFile(string $path, string $source = 'csv'): array
    {
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);
        // Підтримуємо і кому, і крапку з комою (Excel в українській локалі зберігає через «;»)
        $first = (string) fgets(fopen($path, 'r'));
        if (substr_count($first, ';') > substr_count($first, ',')) {
            $csv->setDelimiter(';');
        }
        $rows = [];
        foreach ($csv->getRecords() as $r) {
            $rows[] = $r;
        }
        return $this->importRows($rows, $source);
    }

    /** @return array статистика: inserted, exists, dnc, registered, invalid, errors[] */
    public function importRows(array $rows, string $source = 'csv'): array
    {
        $stats = ['inserted' => 0, 'exists' => 0, 'dnc' => 0, 'registered' => 0, 'invalid' => 0, 'errors' => []];
        foreach ($rows as $i => $r) {
            $r = $this->normalizeKeys($r);
            $phone = Util::normalizeUaPhone((string) ($r['phone'] ?? ''));
            $site = trim((string) ($r['store_site'] ?? ''));
            $name = trim((string) ($r['store_name'] ?? '')) ?: Util::domain($site);
            $line = $i + 2;

            if (!$phone) {
                $stats['invalid']++;
                $stats['errors'][] = "Рядок {$line}: невалідний номер «" . ($r['phone'] ?? '') . "»";
                continue;
            }
            if ($name === '') {
                $stats['invalid']++;
                $stats['errors'][] = "Рядок {$line}: немає назви магазину й сайту";
                continue;
            }
            if (DoNotCall::has($phone)) {
                $stats['dnc']++;
                continue;
            }
            if ($site !== '' && $this->isRegisteredShop($site)) {
                $stats['registered']++;
                continue;
            }
            $lead = Lead::firstOrCreate(['phone' => $phone], [
                'store_name' => mb_substr($name, 0, 250),
                'store_site' => mb_substr($site, 0, 250),
                'contact_name' => mb_substr(trim((string) ($r['contact_name'] ?? '')), 0, 250),
                'category' => mb_substr(trim((string) ($r['category'] ?? '')), 0, 250),
                'source' => $source,
            ]);
            $lead->wasRecentlyCreated ? $stats['inserted']++ : $stats['exists']++;
        }
        return $stats;
    }

    /** Приймаємо і англійські, і українські назви колонок. */
    private function normalizeKeys(array $r): array
    {
        $aliases = [
            'phone' => ['phone', 'телефон', 'номер', 'tel'],
            'store_name' => ['store_name', 'назва', 'магазин', 'name', 'store'],
            'store_site' => ['store_site', 'сайт', 'site', 'url', 'website'],
            'contact_name' => ['contact_name', 'контакт', "ім'я", 'імя', 'contact'],
            'category' => ['category', 'категорія'],
        ];
        $out = [];
        foreach ($r as $k => $v) {
            $key = mb_strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $k)));
            foreach ($aliases as $target => $names) {
                if (in_array($key, $names, true)) {
                    $out[$target] = $v;
                }
            }
        }
        return $out;
    }

    public function isRegisteredShop(string $site): bool
    {
        if ($this->shopDomains === null) {
            $this->shopDomains = [];
            foreach (User::whereNotNull('site_url')->where('site_url', '!=', '')->pluck('site_url') as $url) {
                $d = Util::domain($url);
                if ($d !== '') {
                    $this->shopDomains[$d] = true;
                }
            }
        }
        return isset($this->shopDomains[Util::domain($site)]);
    }
}
