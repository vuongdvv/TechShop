<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function process(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart_item_ids' => ['required', 'array', 'min:1'],
            'cart_item_ids.*' => ['integer', 'min:1'],
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', 'in:cod,vnpay'],
        ]);

        $userId = (int) $request->attributes->get('auth_user_id');
        $cartItemIds = array_values(array_unique(array_map('intval', $validated['cart_item_ids'])));

        $cart = DB::table('carts')->select(['id'])->where('user_id', $userId)->first();
        if (!$cart) {
            return response()->json(['success' => false, 'message' => 'Khong tim thay gio hang'], 404);
        }

        DB::beginTransaction();

        try {
            $items = DB::table('cart_items as ci')
                ->join('products as p', 'p.id', '=', 'ci.product_id')
                ->select(['ci.id', 'ci.product_id', 'ci.quantity', 'ci.price', 'p.name as product_name', 'p.stock'])
                ->whereIn('ci.id', $cartItemIds)
                ->where('ci.cart_id', (int) $cart->id)
                ->get();

            if ($items->isEmpty()) {
                throw new \RuntimeException('Khong co san pham hop le');
            }

            foreach ($items as $item) {
                if ((int) $item->stock <= 0) {
                    throw new \RuntimeException('San pham da het hang');
                }
                if ((int) $item->stock < (int) $item->quantity) {
                    throw new \RuntimeException('So luong san pham khong du');
                }
            }

            $totalPrice = 0.0;
            foreach ($items as $item) {
                $totalPrice += ((float) $item->price) * ((int) $item->quantity);
            }

            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $userId,
                'customer_name' => trim($validated['customer_name']),
                'customer_phone' => trim($validated['phone']),
                'customer_address' => trim($validated['address']),
                'total_price' => $totalPrice,
                'status' => 'pending',
                'created_at' => now(),
            ]);

            DB::table('payments')->insert([
                'order_id' => $orderId,
                'method' => $validated['payment_method'],
                'amount' => $totalPrice,
                'status' => 'pending',
                'created_at' => now(),
            ]);

            foreach ($items as $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => (int) $item->product_id,
                    'product_name' => (string) $item->product_name,
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->price,
                ]);
            }

            if ($validated['payment_method'] === 'cod') {
                foreach ($items as $item) {
                    $affected = DB::table('products')
                        ->where('id', (int) $item->product_id)
                        ->where('stock', '>=', (int) $item->quantity)
                        ->decrement('stock', (int) $item->quantity);

                    if ($affected === 0) {
                        throw new \RuntimeException('San pham khong du hang khi tru kho');
                    }
                }
            }

            DB::table('cart_items')->whereIn('id', $cartItemIds)->delete();

            DB::commit();

            $nextUrl = $validated['payment_method'] === 'vnpay'
                ? '/payment/vnpay/create?order_id=' . $orderId
                : '/TechShop/cart/success.php?order_id=' . $orderId;

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $orderId,
                    'next_url' => $nextUrl,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
