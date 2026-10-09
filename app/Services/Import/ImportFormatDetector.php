<?php

namespace App\Services\Import;

class ImportFormatDetector
{
    public static function detect(string $filePath): ImportFormatInterface
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Визначення за вмістом файлу (YML і Google Merchant RSS — обидва XML)
        $handle = fopen($filePath, 'r');
        $peek = $handle ? fread($handle, 2000) : '';
        if ($handle) {
            fclose($handle);
        }

        $peek = ltrim($peek);

        if (stripos($peek, '<yml_catalog') !== false || stripos($peek, 'DOCTYPE yml_catalog') !== false) {
            return new YmlImportFormat();
        }

        if (in_array($ext, ['xml', 'yml'], true)
            || strpos($peek, '<?xml') !== false || strpos($peek, '<rss') !== false || strpos($peek, '<feed') !== false) {
            return new XmlImportFormat();
        }

        return new CsvImportFormat();
    }
}
