<?php

namespace App\Services\Import;

class XmlImportFormat extends AbstractImportFormat
{
    private const G_NS = 'http://base.google.com/ns/1.0';

    public function parse(string $filePath): array
    {
        $xml = simplexml_load_file($filePath, 'SimpleXMLElement', LIBXML_NOCDATA);

        if (!$xml || !isset($xml->channel)) {
            return [];
        }

        $records = [];
        foreach ($xml->channel->item as $item) {
            $g = $item->children(self::G_NS);

            $raw = [
                'id'                     => trim((string) $g->id),
                'title'                  => trim((string) $g->title),
                'description'            => trim((string) $g->description),
                'link'                   => trim((string) $g->link),
                'image_link'             => trim((string) $g->image_link),
                'additional_image_link'  => trim((string) $g->additional_image_link),
                'condition'              => trim((string) $g->condition),
                'availability'           => trim((string) $g->availability),
                'price'                  => trim((string) $g->price),
                'brand'                  => trim((string) $g->brand),
                'mpn'                    => trim((string) $g->mpn),
            ];

            $records[] = $this->normalizeRecord($raw);
        }

        return $records;
    }

    public function getHeaders(): array
    {
        return ['id', 'title', 'description', 'link', 'image_link', 'condition', 'availability', 'price', 'brand', 'mpn'];
    }
}
