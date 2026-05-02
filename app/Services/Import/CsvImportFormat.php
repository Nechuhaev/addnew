<?php

namespace App\Services\Import;

use League\Csv\Reader;

class CsvImportFormat extends AbstractImportFormat
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
                $breaks = ['<br />', '<br>', '<br/>'];
                return strip_tags(str_ireplace($breaks, "\r\n", $value));
            }, $rawRecord);

            $records[] = $this->normalizeRecord($record);
        }

        return $records;
    }

    public function getHeaders(): array
    {
        return ['id', 'title', 'description', 'link', 'image_link', 'price', 'availability', 'condition', 'brand', 'mpn', 'additional_image_link'];
    }
}
