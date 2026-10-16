<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Kết quả thanh toán VNPay</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9fafb;
        }

        .box {
            max-width: 520px;
            margin: 80px auto;
            background: #fff;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
            text-align: center;
        }

        .success {
            color: #16a34a;
        }

        .error {
            color: #dc2626;
        }

        .info {
            margin-top: 16px;
            text-align: left;
            font-size: 15px;
        }

        .info p {
            margin: 6px 0;
        }

        .btn {
            display: inline-block;
            margin-top: 24px;
            padding: 10px 20px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>

<body>
    <div class="box">
        @if($isSuccess)
        <h2 class="success">Thanh toán thành công</h2>
        <p>Cảm ơn bạn đã thanh toán qua VNPay</p>
        @else
        <h2 class="error">Thanh toán thất bại</h2>
        <p>Giao dịch không thành công hoặc đã bị hủy</p>
        @endif

        <div class="info">
            <p><strong>Mã đơn hàng:</strong> #{{ $orderId }}</p>
            @if($isSuccess)
            <p><strong>Số tiền:</strong> {{ number_format($amount, 0, ',', '.') }} VND</p>
            @endif
            @if(count($products) > 0)
            <p><strong>Sản phẩm:</strong></p>
            <ul style="text-align:left;padding-left:18px;">
                @foreach($products as $p)
                <li>{{ $p->name }} (x{{ $p->quantity }})</li>
                @endforeach
            </ul>
            @endif
        </div>

        @if($isSuccess)
        <a href="{{ $frontUrl }}/orders/index.php" class="btn">Xem đơn hàng</a>
        @else
        <a href="{{ $frontUrl }}/home.php" class="btn">Về trang chủ</a>
        @endif
    </div>
</body>

</html>