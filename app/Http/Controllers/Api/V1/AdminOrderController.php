<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    public function updateStatus(Request $request, int $orderId): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $newStatus = trim($validated['status']);

        $order = DB::table('orders as o')
            ->leftJoin('payments as p', 'p.order_id', '=', 'o.id')
            ->select(['o.status', 'p.method as payment_method'])
            ->where('o.id', $orderId)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Don hang khong ton tai'], 404);
        }

        $currentStatus = (string) $order->status;
        $paymentMethod = (string) ($order->payment_method ?? 'cod');

        $allowed = $paymentMethod === 'cod'
            ? [
                'pending' => ['shipped', 'cancel'],
                'shipped' => ['completed'],
                'completed' => [],
                'cancel' => [],
            ]
            : [
                'pending' => ['paid', 'cancel'],
                'paid' => ['shipped'],
                'shipped' => ['completed'],
                'completed' => [],
                'cancel' => [],
            ];

        if (!isset($allowed[$currentStatus])) {
            return response()->json(['success' => false, 'message' => 'Trang thai hien tai khong hop le'], 422);
        }

        if ($newStatus !== $currentStatus && !in_array($newStatus, $allowed[$currentStatus], true)) {
            return response()->json(['success' => false, 'message' => 'Khong duoc phep chuyen trang thai nay'], 422);
        }

        DB::table('orders')
            ->where('id', $orderId)
            ->update(['status' => $newStatus]);

        return response()->json(['success' => true]);
    }
}
