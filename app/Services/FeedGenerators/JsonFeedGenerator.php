<?php

namespace App\Services\FeedGenerators;

use Illuminate\Support\Collection;

class JsonFeedGenerator implements FeedGenerator
{
    public function generate(Collection $products): string
    {
        $data = $products->map(function ($product) {
            return [
                'id'       => $product->id,
                'name'     => $product->name,
                'in_stock' => $product->stock > 0,
            ];
        });

        return $data->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}