<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BackOfficeController extends Controller
{
    protected function agentHasPermission($permission)
    {
        $user = auth()->user();
        // Agent ID=1 always allowed
        if ($user->id == 1) {
            return true;
        }
        // Otherwise check the specific permission flag
        return $user->$permission ?? false;
    }

    public function recentOrders(Request $request)
    {
        if (!$this->agentHasPermission('can_view_orders')) {
            return response()->json(['error' => 'Forbidden: insufficient permissions'], 403);
        }

        $orders = Order::with('user')
            ->where('created_at', '>=', now()->subDays(5))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $data = $orders->map(function ($order) {
            $resteAPayer = $order->status === 'pending' ? $order->total : 0;
            return [
                'id'             => $order->id,
                'customer_name'  => $order->user->name ?? 'N/A',
                'total'          => $order->total,
                'status'         => $order->status,
                'reste_a_payer'  => $resteAPayer,
            ];
        });

        return response()->json([
            'current_page' => $orders->currentPage(),
            'data'         => $data,
            'last_page'    => $orders->lastPage(),
            'per_page'     => $orders->perPage(),
            'total'        => $orders->total(),
        ]);
    }

    public function createProduct(Request $request)
    {
        if (!$this->agentHasPermission('can_create_product')) {
            return response()->json(['error' => 'Forbidden: insufficient permissions'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'stock'  => 'required|integer|min:0',
            'prices' => 'required|array',
            'prices.*' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $siteIds = array_keys($request->prices);
        $existingSites = Site::whereIn('id', $siteIds)->pluck('id')->toArray();
        $missing = array_diff($siteIds, $existingSites);
        if (!empty($missing)) {
            return response()->json(['error' => 'Invalid site IDs: ' . implode(', ', $missing)], 422);
        }

        DB::beginTransaction();
        try {
            $product = Product::create([
                'name'  => $request->name,
                'stock' => $request->stock,
            ]);

            foreach ($request->prices as $siteId => $price) {
                $product->sites()->attach($siteId, ['price' => $price]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product->load('sites'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create product: ' . $e->getMessage()], 500);
        }
    }
}