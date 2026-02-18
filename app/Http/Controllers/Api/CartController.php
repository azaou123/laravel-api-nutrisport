<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * View current cart.
     */
    public function index(Request $request)
    {
        $token = $request->header('X-Cart-Token');
        if (!$token) {
            return response()->json(['cart' => null, 'message' => 'No cart token provided'], 200);
        }

        $cart = $this->cartService->getCart($token);
        if (!$cart) {
            return response()->json(['cart' => null, 'message' => 'Cart not found'], 200);
        }

        return response()->json($cart);
    }

    /**
     * Add item to cart.
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $site = $request->get('site'); // from SetSite middleware
        if (!$site) {
            return response()->json(['error' => 'Site not specified'], 400);
        }

        $token = $request->header('X-Cart-Token');
        if (!$token) {
            // Generate new token for new cart
            $token = $this->cartService->generateToken();
        }

        try {
            $cart = $this->cartService->addItem($token, $request->product_id, $request->quantity, $site);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json($cart)
            ->header('X-Cart-Token', $token);
    }

    /**
     * Remove item from cart (or reduce quantity).
     */
    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $token = $request->header('X-Cart-Token');
        if (!$token) {
            return response()->json(['error' => 'Cart token required'], 400);
        }

        try {
            $cart = $this->cartService->removeItem($token, $request->product_id, $request->quantity);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }

        return response()->json($cart);
    }

    /**
     * Clear cart.
     */
    public function clear(Request $request)
    {
        $token = $request->header('X-Cart-Token');
        if (!$token) {
            return response()->json(['error' => 'Cart token required'], 400);
        }

        $this->cartService->clearCart($token);
        return response()->json(['message' => 'Cart cleared']);
    }
}