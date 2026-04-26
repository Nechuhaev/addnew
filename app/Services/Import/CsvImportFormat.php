<?php

namespace App\Services\Import;

use League\Csv\Reader;

class CsvImportFormat implements ImportFormatInterface
{
    protected string $delimiter;

    public function __construct(string $delimiter = ',')
    {
        $this->delimiter = $delimiter;
    }

    public function parse(string $filePath): array
    {
        $reader = Reader::createFromPath($filePath, 'r');
        $reader->setDelimiter($this->delimiter);
        $reader->setHeaderOffset(0);

        $records = [];
        foreach ($reader->getRecords() as $rawRecord) {
            $record = array_map(function ($value) {
                $breaks = ["<br />", "<br>", "<br/>"];
                $replaced = str_ireplace($breaks, "\r\n", $value);
                return strip_tags($replaced);
            }, $rawRecord);

            $records[] = $this->normalizeRecord($record);
        }

        return $records;
    }

    public function getHeaders(): array
    {
        return [
            'id',
            'title',
            'description',
            'link',
            'image_link',
            'price',
            'availability',
            'condition',
            'brand',
            'mpn',
            'additional_image_link',
        ];
    }

    public function normalizeRecord(array $record): array
    {
        $brand = strtolower($record['brand'] ?? '');

        $condition = strtolower($record['condition'] ?? 'new');
        if (!in_array($condition, ['new', 'used', 'refurbished'])) {
            $condition = 'new';
        }

        $stock = strtolower($record['availability'] ?? 'in_stock');
        if (!in_array($stock, ['in_stock', 'out_of_stock'])) {
            $stock = 'in_stock';
        }

        $code = strtolower($record['ean'] ?? $record['mpn'] ?? '');

        $price = $record['price'] ?? 0;
        if (is_string($price) && strlen($price) > 3) {
            $price = (int) substr($price, 0, -3);
        } else {
            $price = (int) $price;
        }

        $images = [];
        if (!empty($record['image'])) {
            $images[] = trim($record['image']);
        }
        if (!empty($record['image_link'])) {
            $images[] = trim($record['image_link']);
        }
        if (!empty($record['additional_image_link'])) {
            $additionalImages = explode(',', $record['additional_image_link']);
            foreach ($additionalImages as $additionalImage) {
                $trimmed = trim($additionalImage);
                if ($trimmed) {
                    $images[] = $trimmed;
                }
            }
        }

        $currencyCode = 'UAH';
        if (is_string($record['price'] ?? '') && strlen($record['price']) > 3) {
            $currencyCode = strtoupper(substr($record['price'], -3, 3));
        }

        return [
            'source_id' => $record['id'] ?? null,
            'title' => $record['title'] ?? '',
            'description' => $record['description'] ?? '',
            'link' => $record['link'] ?? '',
            'images' => $images,
            'price' => $price,
            'currency_code' => $currencyCode,
            'brand' => $brand,
            'code' => $code,
            'stock' => $stock,
            'condition' => $condition,
        ];
    }
}
