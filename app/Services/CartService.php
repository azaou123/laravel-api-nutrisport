<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Site;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CartService
{
    protected $cachePrefix = 'cart:';
    protected $ttl = 259200; // 3 days in seconds

    /**
     * Get cart data from cache by token.
     */
    public function getCart(string $token): ?array
    {
        return Cache::get($this->cachePrefix . $token);
    }

    /**
     * Save cart data to cache.
     */
    public function saveCart(string $token, array $cart): void
    {
        Cache::put($this->cachePrefix . $token, $cart, $this->ttl);
    }

    /**
     * Generate a new cart token.
     */
    public function generateToken(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Add item to cart.
     */
    public function addItem(string $token, int $productId, int $quantity, Site $site): array
    {
        $cart = $this->getCart($token) ?? $this->createEmptyCart($token, $site->id);

        // Check if product exists and get price for this site
        $product = Product::with(['sites' => function ($query) use ($site) {
            $query->where('site_id', $site->id);
        }])->find($productId);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        $sitePivot = $product->sites->first();
        if (!$sitePivot) {
            throw new \Exception('Product not available for this site');
        }

        $price = $sitePivot->pivot->price;
        $name = $product->name;

        // Check if product already in cart
        $found = false;
        foreach ($cart['items'] as &$item) {
            if ($item['product_id'] == $productId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $cart['items'][] = [
                'product_id' => $productId,
                'name' => $name,
                'quantity' => $quantity,
                'price' => $price,
            ];
        }

        $cart['updated_at'] = now()->toDateTimeString();
        $this->saveCart($token, $cart);

        return $cart;
    }

    /**
     * Remove item (or reduce quantity) from cart.
     */
    public function removeItem(string $token, int $productId, ?int $quantity = null): array
    {
        $cart = $this->getCart($token);
        if (!$cart) {
            throw new \Exception('Cart not found');
        }

        $cart['items'] = array_values(array_filter($cart['items'], function ($item) use ($productId, $quantity) {
            if ($item['product_id'] != $productId) {
                return true;
            }
            if ($quantity === null || $item['quantity'] <= $quantity) {
                return false; // remove entirely
            }
            // reduce quantity
            $item['quantity'] -= $quantity;
            return true;
        }));

        $cart['updated_at'] = now()->toDateTimeString();
        $this->saveCart($token, $cart);

        return $cart;
    }

    /**
     * Clear cart.
     */
    public function clearCart(string $token): void
    {
        Cache::forget($this->cachePrefix . $token);
    }

    /**
     * Create empty cart structure.
     */
    protected function createEmptyCart(string $token, int $siteId): array
    {
        return [
            'token' => $token,
            'site_id' => $siteId,
            'user_id' => null, // could be set later if user logs in
            'items' => [],
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ];
    }
}