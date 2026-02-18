<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $site = $request->get('site');
        if (!$site) {
            return response()->json(['error' => 'Site not specified'], 400);
        }

        $products = Product::with(['sites' => function ($query) use ($site) {
            $query->where('site_id', $site->id);
        }])->get();

        // Transform to include price and stock
        $result = $products->map(function ($product) use ($site) {
            $sitePivot = $product->sites->first();
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $sitePivot ? $sitePivot->pivot->price : null,
                'in_stock' => $product->stock > 0,
            ];
        });

        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $site = $request->attributes->get('site');

        if (!$site) {
            return response()->json(['error' => 'Site not specified'], 400);
        }

        $product = Product::with(['sites' => function ($query) use ($site) {
            $query->where('product_site.site_id', $site->id);
        }])->find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $sitePivot = $product->sites->first();

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $sitePivot ? $sitePivot->pivot->price : null,
            'stock' => $product->stock,
            'in_stock' => $product->stock > 0,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
