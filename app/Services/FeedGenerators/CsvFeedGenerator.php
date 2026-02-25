<?php
namespace App\Services\FeedGenerators;

use Illuminate\Support\Collection;

class CsvFeedGenerator implements FeedGenerator
{
    public function generate(Collection $products): string
    {
        // Open memory "file" to write CSV
        $handle = fopen('php://memory', 'r+');

        // Header row
        fputcsv($handle, ['id', 'name', 'in_stock']);

        // Data rows
        foreach ($products as $product) {
            fputcsv($handle, [
                $product->id,
                $product->name,
                $product->stock > 0 ? 'true' : 'false'
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
