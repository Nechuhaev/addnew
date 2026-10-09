<?php

namespace App\Services\Import;

abstract class AbstractImportFormat implements ImportFormatInterface
{
    public function normalizeRecord(array $record): array
    {
        $condition = strtolower($record['condition'] ?? 'new');
        if (!in_array($condition, ['new', 'used', 'refurbished'])) {
            $condition = 'new';
        }

        $stock = strtolower($record['availability'] ?? 'in_stock');
        if (!in_array($stock, ['in_stock', 'out_of_stock'])) {
            $stock = 'in_stock';
        }

        $priceRaw = trim($record['price'] ?? '0');
        $price = 0;
        $currencyCode = 'UAH';

        if (strlen($priceRaw) > 3) {
            $currencyCode = strtoupper(trim(substr($priceRaw, -3)));
            $price = (int) substr($priceRaw, 0, -3);
        } else {
            $price = (int) $priceRaw;
        }

        $images = [];
        foreach (['image', 'image_link'] as $field) {
            if (!empty($record[$field])) {
                $images[] = trim($record[$field]);
            }
        }
        if (!empty($record['additional_image_link'])) {
            foreach (explode(',', $record['additional_image_link']) as $img) {
                $trimmed = trim($img);
                if ($trimmed) {
                    $images[] = $trimmed;
                }
            }
        }

        return [
            'source_id'     => $record['id'] ?? null,
            'title'         => $record['title'] ?? '',
            'description'   => $record['description'] ?? '',
            'link'          => $record['link'] ?? '',
            'images'        => $images,
            'price'         => $price,
            'currency_code' => $currencyCode,
            'brand'         => strtolower($record['brand'] ?? ''),
            'code'          => strtolower($record['ean'] ?? $record['mpn'] ?? ''),
            'gtin'          => self::normalizeGtin($record['gtin'] ?? $record['ean'] ?? ''),
            'stock'         => $stock,
            'condition'     => $condition,
        ];
    }

    /**
     * GTIN/EAN — лише цифри, 8–14 знаків (EAN-8, UPC-12, EAN-13, GTIN-14).
     * Ведучі нулі до 13 знаків прибираємо, щоб UPC-12 і EAN-13 «0…» збігались.
     */
    public static function normalizeGtin($value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        $len = strlen($digits);
        if ($len < 8 || $len > 14 || trim($digits, '0') === '') {
            return null;
        }
        if ($len > 13 && $digits[0] === '0') {
            $digits = substr($digits, 1);
        }
        if (strlen($digits) === 13 && $digits[0] === '0') {
            $digits = substr($digits, 1);
        }
        return $digits;
    }
}
