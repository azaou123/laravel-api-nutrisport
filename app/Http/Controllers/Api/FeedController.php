<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\FeedGenerators\JsonFeedGenerator;
use App\Services\FeedGenerators\XmlFeedGenerator;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    /**
     * Map format to generator class.
     */
    protected array $generators = [
        'json' => JsonFeedGenerator::class,
        'xml'  => XmlFeedGenerator::class,
    ];

    /**
     * Generate and return the catalogue feed in the requested format.
     */
    public function __invoke(Request $request, string $format)
    {
        if (!array_key_exists($format, $this->generators)) {
            abort(404, 'Format not supported.');
        }

        // Fetch only the needed columns
        $products = Product::all(['id', 'name', 'stock']);

        $generator = app($this->generators[$format]);
        $content = $generator->generate($products);

        $contentType = $format === 'json' ? 'application/json' : 'application/xml';

        return response($content, 200)
            ->header('Content-Type', $contentType)
            ->header('X-Content-Type-Options', 'nosniff');
    }
}