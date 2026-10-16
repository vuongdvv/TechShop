@extends('storefront.layout')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/style.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/cart.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/checkout.css">
@endpush

@section('content')
<main>
    <div class="container">
        <div class="breadcrumbs">
            <a href="{{ $frontUrl }}/home.php">Trang Chủ</a> >
            <a href="{{ $frontUrl }}/orders/index.php">Đơn hàng của tôi</a> >
            <span>Chi tiết đơn hàng</span>
        </div>
        <h2 class="page-title">🧾 Chi tiết đơn hàng #{{ $order->id }}</h2>

        @php
        $statusMap = [
        'pending' => ['status-gray', 'Chờ xử lý'],
        'paid' => ['status-green', 'Đã thanh toán'],
        'shipped' => ['status-orange', 'Đang giao'],
        'completed' => ['status-blue', 'Hoàn tất'],
        'cancel' => ['status-red', 'Đã huỷ đơn'],
        ];
        [$class, $text] = $statusMap[$order->status] ?? ['status-gray', 'Chờ xử lý'];
        @endphp

        <div class="order-info-box">
            <div class="info-row"><strong>Khách hàng:</strong> {{ e($order->customer_name) }}</div>
            <div class="info-row"><strong>SĐT:</strong> {{ e($order->customer_phone) }}</div>
            <div class="info-row"><strong>Địa chỉ:</strong> {{ e($order->customer_address . ', ' . ($order->city ?? '')) }}</div>
            <div class="info-row"><strong>Ngày đặt:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</div>
            <div class="info-row"><strong>Thanh toán:</strong> {{ strtoupper(e((string) ($order->payment_method ?? 'COD'))) }}</div>
            <div class="info-row"><strong>Trạng thái:</strong> <span class="status-badge {{ $class }}">{{ $text }}</span></div>
        </div>

        <table class="cart-table">
            <thead>
                <tr>
                    <td class="data-label">Sản phẩm</td>
                    <td data-label="Giá">Giá</td>
                    <td data-label="SL">SL</td>
                    <td data-label="Tổng tiền">Tổng tiền</td>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                @php
                $image = !empty($item->image_url)
                ? $frontUrl . '/assets/images/products/' . $item->image_url
                : $frontUrl . '/assets/images/products/no-image.png';
                $subTotal = ((float) $item->price) * ((int) $item->quantity);
                @endphp
                <tr>
                    <td class="cart-product">
                        <img src="{{ $image }}" alt="{{ e($item->name) }}">
                        <a href="{{ $frontUrl . '/product/detail.php?slug=' . $item->slug }}">{{ e($item->name) }}</a>
                    </td>
                    <td>{{ number_format((float) $item->price, 0, ',', '.') }} ₫</td>
                    <td>{{ (int) $item->quantity }}</td>
                    <td>{{ number_format($subTotal, 0, ',', '.') }} ₫</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="cart-summary">
            <h3>Tổng cộng: {{ number_format((float) $order->total_price, 0, ',', '.') }} ₫</h3>
        </div>
    </div>
</main>
@endsection