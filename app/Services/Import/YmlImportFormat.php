<?php

namespace App\Services\Import;

/**
 * YML (Yandex Market Language) — формат фідів Prom.ua, Rozetka, Hotline:
 * <yml_catalog><shop><offers><offer id=".." available="true">…</offer></offers></shop></yml_catalog>
 * Звідси беремо бренд (vendor), артикул (vendorCode) і штрихкод (barcode).
 */
class YmlImportFormat extends AbstractImportFormat
{
    public function parse(string $filePath): array
    {
        $xml = simplexml_load_file($filePath, 'SimpleXMLElement', LIBXML_NOCDATA);

        if (!$xml || !isset($xml->shop->offers->offer)) {
            return [];
        }

        $records = [];
        foreach ($xml->shop->offers->offer as $offer) {
            $pictures = [];
            foreach ($offer->picture as $picture) {
                $url = trim((string) $picture);
                if ($url !== '') {
                    $pictures[] = $url;
                }
            }

            $currency = strtoupper(trim((string) $offer->currencyId)) ?: 'UAH';
            if ($currency === 'RUR') {
                $currency = 'RUB';
            }
            $price = str_replace([' ', ','], ['', '.'], trim((string) $offer->price));

            $raw = [
                'id'                    => trim((string) $offer['id']),
                'title'                 => $this->firstNonEmpty($offer->name_ua, $offer->name, $offer->model),
                'description'           => $this->plainText($this->firstNonEmpty($offer->description_ua, $offer->description)),
                'link'                  => trim((string) $offer->url),
                'image_link'            => $pictures[0] ?? '',
                'additional_image_link' => implode(',', array_slice($pictures, 1)),
                'condition'             => 'new',
                'availability'          => $this->isAvailable($offer) ? 'in_stock' : 'out_of_stock',
                'price'                 => $price !== '' ? ((int) round((float) $price)) . $currency : '0',
                'brand'                 => trim((string) $offer->vendor),
                'mpn'                   => trim((string) $offer->vendorCode),
                'gtin'                  => trim((string) $offer->barcode),
            ];

            $records[] = $this->normalizeRecord($raw);
        }

        return $records;
    }

    public function getHeaders(): array
    {
        return ['id', 'name', 'description', 'url', 'picture', 'price', 'currencyId', 'available', 'vendor', 'vendorCode', 'barcode'];
    }

    protected function isAvailable(\SimpleXMLElement $offer): bool
    {
        $attr = strtolower(trim((string) $offer['available']));
        if ($attr === 'false' || $attr === '0') {
            return false;
        }
        if (isset($offer->stock_quantity) && trim((string) $offer->stock_quantity) !== '' && (int) $offer->stock_quantity <= 0) {
            return false;
        }
        return true;
    }

    protected function firstNonEmpty(...$values): string
    {
        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                return $value;
            }
        }
        return '';
    }

    /** HTML-опис з фіда → звичайний текст із переносами рядків */
    protected function plainText(string $html): string
    {
        $text = preg_replace('~<\s*(br|/p|/li|/div|/h\d)\b[^>]*>~i', "\n", $html);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("~[ \t]+~u", ' ', $text);
        return trim(preg_replace("~\n\s*\n+~u", "\n\n", $text));
    }
}
