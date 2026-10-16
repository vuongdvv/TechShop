@extends('storefront.layout')

@section('title', 'Đặt hàng thành công - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/style.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/checkout.css">
@endpush

@section('content')
<main>
    <div class="container" style="padding: 40px 0;">
        <div class="cart-summary" style="max-width: 600px; margin: 0 auto; text-align: center;">
            <h2>Đặt hàng thành công</h2>
            <p>Đơn hàng #{{ $orderId }} đã được tạo.</p>
            <p>Cảm ơn bạn đã mua sắm tại TechShop.</p>
            <a class="btn btn-primary" href="{{ $frontUrl . '/orders/detail.php?id=' . $orderId }}">Xem chi tiết đơn hàng</a>
            <a class="btn" href="{{ $frontUrl }}/home.php" style="margin-left: 10px;">Về trang chủ</a>
        </div>
    </div>
</main>
@endsection