<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function status(int $orderId): JsonResponse
    {
        if ($orderId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Order khong hop le',
            ], 422);
        }

        $result = DB::table('payments as p')
            ->join('orders as o', 'o.id', '=', 'p.order_id')
            ->select(['p.status', 'o.status as order_status'])
            ->where('p.order_id', $orderId)
            ->where('p.method', 'vnpay')
            ->first();

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay payment',
            ], 404);
        }

        if ($result->status === 'success' && $result->order_status === 'paid') {
            return response()->json([
                'success' => true,
                'status' => 'success',
                'redirect' => '/TechShop/cart/success.php?order_id=' . $orderId,
            ]);
        }

        if ($result->status === 'cancel') {
            return response()->json([
                'success' => true,
                'status' => 'cancel',
                'message' => 'Thanh toan that bai',
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => 'pending',
        ]);
    }
}
