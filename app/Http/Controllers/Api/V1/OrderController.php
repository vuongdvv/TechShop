<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function cancel(Request $request, int $orderId): JsonResponse
    {
        $userId = (int) $request->attributes->get('auth_user_id');

        if ($orderId <= 0) {
            return response()->json(['success' => false, 'message' => 'Don hang khong hop le'], 422);
        }

        $order = DB::table('orders')
            ->select(['id', 'status'])
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Khong tim thay don hang'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Khong the huy don nay'], 422);
        }

        DB::beginTransaction();

        try {
            $items = DB::table('order_items')
                ->select(['product_id', 'quantity'])
                ->where('order_id', $orderId)
                ->get();

            foreach ($items as $item) {
                DB::table('products')
                    ->where('id', $item->product_id)
                    ->increment('stock', (int) $item->quantity);
            }

            DB::table('orders')
                ->where('id', $orderId)
                ->update(['status' => 'cancel']);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Loi khi huy don'], 500);
        }
    }
}
