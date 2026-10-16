<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'buy_now' => ['nullable', 'boolean'],
        ]);

        $userId = (int) $request->attributes->get('auth_user_id');
        $productId = (int) $validated['product_id'];
        $quantityToAdd = (int) ($validated['quantity'] ?? 1);

        $product = DB::table('products')
            ->select(['id', 'price', 'sale_price'])
            ->where('id', $productId)
            ->where('status', 1)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'San pham khong ton tai',
            ], 404);
        }

        $price = !empty($product->sale_price) ? (float) $product->sale_price : (float) $product->price;

        $cart = DB::table('carts')->where('user_id', $userId)->first();

        if (!$cart) {
            $cartId = DB::table('carts')->insertGetId([
                'user_id' => $userId,
                'created_at' => now(),
            ]);
        } else {
            $cartId = (int) $cart->id;
        }

        $cartItem = DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $newQty = (int) $cartItem->quantity + $quantityToAdd;
            DB::table('cart_items')->where('id', $cartItem->id)->update([
                'quantity' => $newQty,
            ]);
            $cartItemId = (int) $cartItem->id;
            $quantity = $newQty;
        } else {
            $cartItemId = DB::table('cart_items')->insertGetId([
                'cart_id' => $cartId,
                'product_id' => $productId,
                'quantity' => $quantityToAdd,
                'price' => $price,
            ]);
            $quantity = $quantityToAdd;
        }

        $cartCount = (int) DB::table('cart_items as ci')
            ->join('carts as c', 'c.id', '=', 'ci.cart_id')
            ->where('c.user_id', $userId)
            ->sum('ci.quantity');

        return response()->json([
            'success' => true,
            'message' => 'Da them san pham vao gio hang',
            'data' => [
                'cart_item_id' => $cartItemId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'cart_count' => $cartCount,
            ],
        ]);
    }

    public function update(Request $request, int $cartItemId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $userId = (int) $request->attributes->get('auth_user_id');
        $quantity = (int) $validated['quantity'];

        $cartItem = DB::table('cart_items as ci')
            ->join('carts as c', 'ci.cart_id', '=', 'c.id')
            ->select(['ci.id'])
            ->where('ci.id', $cartItemId)
            ->where('c.user_id', $userId)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay san pham trong gio hang',
            ], 404);
        }

        DB::table('cart_items')->where('id', $cartItemId)->update([
            'quantity' => $quantity,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cap nhat so luong thanh cong',
            'data' => [
                'cart_item_id' => $cartItemId,
                'quantity' => $quantity,
            ],
        ]);
    }

    public function destroy(Request $request, int $cartItemId): JsonResponse
    {
        $userId = (int) $request->attributes->get('auth_user_id');

        $deleted = DB::table('cart_items as ci')
            ->join('carts as c', 'ci.cart_id', '=', 'c.id')
            ->where('ci.id', $cartItemId)
            ->where('c.user_id', $userId)
            ->delete();

        if ($deleted === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay san pham trong gio hang',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Da xoa san pham khoi gio hang',
        ]);
    }
}
