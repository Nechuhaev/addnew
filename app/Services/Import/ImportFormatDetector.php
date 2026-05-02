<?php

namespace App\Services\Import;

class ImportFormatDetector
{
    public static function detect(string $filePath): ImportFormatInterface
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'xml') {
            return new XmlImportFormat();
        }

        // Визначення за вмістом файлу якщо розширення не xml
        $handle = fopen($filePath, 'r');
        $peek = $handle ? fread($handle, 200) : '';
        if ($handle) {
            fclose($handle);
        }

        $peek = ltrim($peek); // прибираємо BOM та пробіли
        if (strpos($peek, '<?xml') !== false || strpos($peek, '<rss') !== false || strpos($peek, '<feed') !== false) {
            return new XmlImportFormat();
        }

        return new CsvImportFormat();
    }
}
