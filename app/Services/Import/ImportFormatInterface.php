<?php

namespace App\Services\Import;

interface ImportFormatInterface
{
    public function parse(string $filePath): array;

    public function getHeaders(): array;

    public function normalizeRecord(array $record): array;
}
