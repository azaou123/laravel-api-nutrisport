<?php

namespace App\Services\FeedGenerators;

use Illuminate\Support\Collection;

class XmlFeedGenerator implements FeedGenerator
{
    public function generate(Collection $products): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><products></products>');

        foreach ($products as $product) {
            $item = $xml->addChild('product');
            $item->addChild('id', (string) $product->id);
            $item->addChild('name', htmlspecialchars($product->name));
            $item->addChild('in_stock', $product->stock > 0 ? 'true' : 'false');
        }

        return $xml->asXML();
    }
}