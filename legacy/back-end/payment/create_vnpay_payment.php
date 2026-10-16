<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

require_once dirname(__DIR__, 2) . "/config/database.php";
$vnpay = require dirname(__DIR__, 2) . "/config/vnpay.php";


if (!isset($_GET['order_id'])) {
    die("Missing order_id");
}

$orderId = (int)$_GET['order_id'];

$stmt = $conn->prepare("
    SELECT total_price 
    FROM orders 
    WHERE id = ? AND status = 'pending'
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found or not pending");
}

$realAmount = (int)$order['total_price'];
$payAmount = $realAmount;

$vnp_TxnRef = $orderId . '_' . time();
$vnp_OrderInfo = 'Thanh toan don hang test';
$vnp_OrderType = 'billpayment';
$vnp_Amount = $payAmount * 100;
$vnp_Locale = 'vn';
$vnp_BankCode = 'NCB';
$vnp_IpAddr = '127.0.0.1';
$vnp_TmnCode = $vnpay['vnp_TmnCode'];
$vnp_ReturnUrl = $vnpay['vnp_ReturnUrl'];
$vnp_Url = $vnpay['vnp_Url'];
$vnp_HashSecret = $vnpay['vnp_HashSecret'];


$vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes'));

$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => $vnp_OrderType,
    "vnp_ReturnUrl" => $vnp_ReturnUrl,
    "vnp_TxnRef" => $vnp_TxnRef,
    "vnp_ExpireDate" => $vnp_ExpireDate

);

if (isset($vnp_BankCode) && $vnp_BankCode != "") {
    $inputData['vnp_BankCode'] = $vnp_BankCode;
}

ksort($inputData);
$query = "";
$i = 0;
$hashdata = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$vnp_Url = $vnp_Url . "?" . $query;
if (isset($vnp_HashSecret)) {
    $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
}
header('Location: ' . $vnp_Url);
exit;
