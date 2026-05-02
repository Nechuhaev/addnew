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

        // Визначення за вмістом файлу
        $handle = fopen($filePath, 'r');
        $peek = $handle ? fread($handle, 500) : '';
        if ($handle) {
            fclose($handle);
        }

        $peek = ltrim($peek);

        if (strpos($peek, '<?xml') !== false || strpos($peek, '<rss') !== false || strpos($peek, '<feed') !== false) {
            return new XmlImportFormat();
        }

        return new CsvImportFormat();
    }
}
