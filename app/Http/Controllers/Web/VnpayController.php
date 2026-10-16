<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VnpayController extends Controller
{
    /**
     * Build and return the HMAC-SHA512 secure hash for VNPay parameters.
     */
    private function buildSecureHash(array $inputData, string $hashSecret): string
    {
        ksort($inputData);
        $hashData = '';
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i === 1) {
                $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashData .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
        }
        return hash_hmac('sha512', $hashData, $hashSecret);
    }

    /**
     * Verify the VNPay secure hash from incoming GET params.
     */
    private function verifyHash(array $params, string $hashSecret): bool
    {
        $secureHash = $params['vnp_SecureHash'] ?? '';
        $inputData = [];
        foreach ($params as $key => $value) {
            if (str_starts_with($key, 'vnp_') && $key !== 'vnp_SecureHash' && $key !== 'vnp_SecureHashType') {
                $inputData[$key] = $value;
            }
        }
        return hash_equals($this->buildSecureHash($inputData, $hashSecret), $secureHash);
    }

    /**
     * Create VNPay payment URL and redirect the user.
     */
    public function create(Request $request): RedirectResponse
    {
        $orderId = (int) $request->query('order_id', 0);
        if ($orderId <= 0) {
            abort(400, 'Missing order_id');
        }

        $order = DB::table('orders')->where('id', $orderId)->where('status', 'pending')->first();
        if (!$order) {
            abort(404, 'Order not found or not pending');
        }

        $tmnCode    = (string) config('vnpay.tmn_code');
        $hashSecret = (string) config('vnpay.hash_secret');
        $vnpUrl     = (string) config('vnpay.url');
        $returnUrl  = (string) config('vnpay.return_url');

        $tz     = new \DateTimeZone('Asia/Ho_Chi_Minh');
        $now    = new \DateTime('now', $tz);
        $expire = new \DateTime('+15 minutes', $tz);

        $txnRef   = $orderId . '_' . $now->format('U');
        $amount   = (int) $order->total_price * 100;

        $inputData = [
            'vnp_Version'    => '2.1.0',
            'vnp_TmnCode'    => $tmnCode,
            'vnp_Amount'     => $amount,
            'vnp_Command'    => 'pay',
            'vnp_CreateDate' => $now->format('YmdHis'),
            'vnp_CurrCode'   => 'VND',
            'vnp_IpAddr'     => $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('REMOTE_ADDR') ?? '127.0.0.1',
            'vnp_Locale'     => 'vn',
            'vnp_OrderInfo'  => 'Thanh toan don hang ' . $orderId,
            'vnp_OrderType'  => 'billpayment',
            'vnp_ReturnUrl'  => $returnUrl,
            'vnp_TxnRef'     => $txnRef,
            'vnp_ExpireDate' => $expire->format('YmdHis'),
            'vnp_BankCode'   => 'NCB',
        ];

        ksort($inputData);
        $query    = http_build_query($inputData);
        $hashData = '';
        $i        = 0;
        foreach ($inputData as $key => $value) {
            if ($i === 1) {
                $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashData .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
        }
        $secureHash = hash_hmac('sha512', $hashData, $hashSecret);
        $redirectUrl = $vnpUrl . '?' . $query . '&vnp_SecureHash=' . $secureHash;

        return redirect()->away($redirectUrl);
    }

    /**
     * Handle VNPay return after payment (user-facing redirect).
     */
    public function return(Request $request): View|RedirectResponse
    {
        $params     = $request->query();
        $hashSecret = (string) config('vnpay.hash_secret');

        if (!$this->verifyHash($params, $hashSecret)) {
            abort(400, 'Invalid signature');
        }

        $txnRef       = (string) ($params['vnp_TxnRef'] ?? '');
        $orderId      = (int) explode('_', $txnRef)[0];
        $responseCode = (string) ($params['vnp_ResponseCode'] ?? '');
        $txnStatus    = (string) ($params['vnp_TransactionStatus'] ?? '');
        $amount       = isset($params['vnp_Amount']) ? (int) $params['vnp_Amount'] / 100 : 0;
        $transactionNo = (string) ($params['vnp_TransactionNo'] ?? '');

        $isSuccess = ($responseCode === '00' && $txnStatus === '00');

        if ($orderId > 0) {
            DB::beginTransaction();
            try {
                $order = DB::table('orders')->where('id', $orderId)->lockForUpdate()->first();

                if ($order && $order->status === 'pending') {
                    if ($amount > 0 && (int) $order->total_price !== $amount) {
                        throw new \RuntimeException('Invalid amount');
                    }

                    if ($isSuccess) {
                        $newOrderStatus   = 'paid';
                        $newPaymentStatus = 'success';

                        // Deduct stock
                        $items = DB::table('order_items')->where('order_id', $orderId)->get();
                        foreach ($items as $item) {
                            $affected = DB::table('products')
                                ->where('id', $item->product_id)
                                ->where('stock', '>=', $item->quantity)
                                ->decrement('stock', $item->quantity);
                            if ($affected === 0) {
                                throw new \RuntimeException('Product out of stock: ' . $item->product_id);
                            }
                        }
                    } elseif ($responseCode === '24') {
                        $newOrderStatus   = 'cancel';
                        $newPaymentStatus = 'cancel';
                    } else {
                        $newOrderStatus   = 'cancel';
                        $newPaymentStatus = 'cancel';
                    }

                    DB::table('orders')->where('id', $orderId)->update([
                        'status'             => $newOrderStatus,
                        'vnp_transaction_no' => $transactionNo,
                    ]);

                    DB::table('payments')->where('order_id', $orderId)->update([
                        'status'           => $newPaymentStatus,
                        'transaction_code' => $transactionNo,
                        'response_payload' => json_encode($params),
                        'updated_at'       => now(),
                    ]);
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $isSuccess = false;
            }
        }

        // Fetch ordered products for receipt display
        $products = [];
        if ($orderId > 0) {
            $products = DB::table('order_items as oi')
                ->join('products as p', 'p.id', '=', 'oi.product_id')
                ->where('oi.order_id', $orderId)
                ->select('p.name', 'oi.quantity')
                ->get()
                ->toArray();
        }

        return view('storefront.payment.vnpay_return', [
            'isSuccess'   => $isSuccess,
            'orderId'     => $orderId,
            'amount'      => $amount,
            'products'    => $products,
            'frontUrl'    => rtrim($request->getSchemeAndHttpHost() . $request->getBasePath(), '/'),
        ]);
    }

    /**
     * Handle VNPay IPN (server-to-server callback).
     */
    public function ipn(Request $request): JsonResponse
    {
        $params     = $request->query();
        $hashSecret = (string) config('vnpay.hash_secret');

        if (!$this->verifyHash($params, $hashSecret)) {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }

        $txnRef        = (string) ($params['vnp_TxnRef'] ?? '');
        $orderId       = (int) explode('_', $txnRef)[0];
        $vnpAmount     = isset($params['vnp_Amount']) ? (int) $params['vnp_Amount'] / 100 : 0;
        $responseCode  = (string) ($params['vnp_ResponseCode'] ?? '');
        $txnStatus     = (string) ($params['vnp_TransactionStatus'] ?? '');
        $transactionNo = (string) ($params['vnp_TransactionNo'] ?? '');

        if (!$orderId) {
            return response()->json(['RspCode' => '01', 'Message' => 'Invalid TxnRef']);
        }

        DB::beginTransaction();
        try {
            $order = DB::table('orders')->where('id', $orderId)->lockForUpdate()->first();

            if (!$order) {
                DB::rollBack();
                return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
            }

            if ((int) $order->total_price !== $vnpAmount) {
                DB::rollBack();
                return response()->json(['RspCode' => '04', 'Message' => 'Invalid amount']);
            }

            if ($order->status !== 'pending') {
                DB::commit();
                return response()->json(['RspCode' => '02', 'Message' => 'Order already confirmed']);
            }

            if ($responseCode === '00' && $txnStatus === '00') {
                $newStatus    = 'paid';
                $paymentStatus = 'success';

                $items = DB::table('order_items')->where('order_id', $orderId)->get();
                foreach ($items as $item) {
                    $affected = DB::table('products')
                        ->where('id', $item->product_id)
                        ->where('stock', '>=', $item->quantity)
                        ->decrement('stock', $item->quantity);
                    if ($affected === 0) {
                        throw new \RuntimeException('Product out of stock: ' . $item->product_id);
                    }
                }
            } elseif ($responseCode === '24') {
                $newStatus    = 'cancel';
                $paymentStatus = 'cancel';
            } else {
                $newStatus    = 'cancel';
                $paymentStatus = 'cancel';
            }

            DB::table('orders')->where('id', $orderId)->update([
                'status'             => $newStatus,
                'vnp_transaction_no' => $transactionNo,
            ]);

            DB::table('payments')->where('order_id', $orderId)->update([
                'status'           => $paymentStatus,
                'transaction_code' => $transactionNo,
                'response_payload' => json_encode($params),
                'updated_at'       => now(),
            ]);

            DB::commit();
            return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['RspCode' => '99', 'Message' => 'Unknown error']);
        }
    }
}
