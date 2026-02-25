<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Site;
use App\Services\CartService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationCustomer;
use App\Mail\OrderConfirmationAdmin;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function store(OrderRequest $request)
    {
        // Récupérer l'utilisateur authentifié (customer)
        $user = auth()->user();

        // Récupérer le site depuis le middleware SetSite
        $site = $request->get('site');
        if (!$site) {
            return response()->json(['error' => 'Site not specified'], 400);
        }

        // Récupérer le token du panier
        $token = $request->header('X-Cart-Token');
        if (!$token) {
            return response()->json(['error' => 'Cart token required'], 400);
        }

        // Récupérer le panier depuis le cache
        $cart = $this->cartService->getCart($token);
        if (!$cart || empty($cart['items'])) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        // Vérifier que le panier appartient bien au site courant (sécurité)
        if ($cart['site_id'] != $site->id) {
            return response()->json(['error' => 'Cart does not match current site'], 400);
        }

        // Validation des stocks avant de créer la commande
        foreach ($cart['items'] as $item) {
            $product = Product::find($item['product_id']);
            if (!$product || $product->stock < $item['quantity']) {
                return response()->json(['error' => "Insufficient stock for product: {$item['name']}"], 400);
            }
        }

        // Tout est bon, on crée la commande dans une transaction
        try {
            DB::beginTransaction();

            // Calculer le total
            $total = 0;
            foreach ($cart['items'] as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            // Créer la commande
            $order = Order::create([
                'user_id' => $user->id,
                'site_id' => $site->id,
                'total' => $total,
                'status' => 'pending', // en attente de paiement (virement)
                'shipping_full_name' => $request->shipping_full_name,
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_country' => $request->shipping_country,
                'payment_method' => 'bankwire',
            ]);

            // Créer les items de commande et décrémenter les stocks
            foreach ($cart['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                ]);

                // Décrémenter le stock
                $product = Product::find($item['product_id']);
                $product->decrement('stock', $item['quantity']);
            }

            // Vider le panier
            $this->cartService->clearCart($token);

            DB::commit();

            // Envoyer les emails (après commit pour éviter d'envoyer si erreur)
            $this->sendOrderEmails($order, $user, $cart);

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order->load('items.product'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create order: ' . $e->getMessage()], 500);
        }
    }

    protected function sendOrderEmails($order, $user, $cart)
    {
        // Email au client
        Mail::to($user->email)->send(new OrderConfirmationCustomer($order, $cart));

        // Email à l'administrateur (adresse définie dans .env)
        $adminEmail = env('MAIL_ADMIN', 'admin@nutrisport.com');
        Mail::to($adminEmail)->send(new OrderConfirmationAdmin($order, $cart));
    }

    public function history(){
        $user = JWTAuth::user();
        $orders = $user->orders()->orderBy('created_at', 'desc')->get()->map(function($order) {
            return [
                'id'      => $order->id,
                'total'   => $order->total,
                'status'  => $order->status,
                'items'   => $order->items, // JSON array of products
                'date'    => $order->created_at->toDateTimeString(),
            ];
        });

        return response()->json(['orders' => $orders]);
    }
}