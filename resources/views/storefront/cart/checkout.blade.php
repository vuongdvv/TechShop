@extends('storefront.layout')

@section('title', 'Thanh toán - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/style.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/cart.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/checkout.css">
@endpush

@section('content')
<main>
    <div class="container">
        @if(session('error'))
        <div class="toast-error">{{ session('error') }}</div>
        @endif

        <h2 class="page-title">💳 Thanh toán</h2>
        <form action="{{ $frontUrl }}/cart/process_checkout.php" method="post" id="checkoutForm">
            @csrf
            <div class="checkout-wrapper">
                <div class="checkout-left">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <td data-label="Sản phẩm">Sản phẩm</td>
                                <td data-label="Số lượng">Số lượng</td>
                                <td data-label="Giá">Giá</td>
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
                                    <a href="{{ $frontUrl . '/product/detail.php?slug=' . urlencode($item->slug) }}" class="cart-product-link">
                                        <img src="{{ $image }}" alt="{{ e($item->name) }}">
                                        {{ e($item->name) }}
                                    </a>
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($subTotal, 0, ',', '.') }} ₫</td>
                            </tr>
                            <input type="hidden" name="cart_item_ids[]" value="{{ $item->cart_item_id }}">
                            @endforeach
                        </tbody>
                    </table>
                    <a href="{{ $frontUrl }}/cart/index.php" class="cart-continue-shopping">← Trở lại giỏ hàng</a>
                </div>

                <div class="checkout-right">
                    <h3>Thông tin người nhận</h3>
                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name', $userAddress->full_name ?? '') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" value="{{ old('phone', $userAddress->phone ?? '') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <textarea name="address" rows="3" required>{{ old('address', $userAddress->address_detail ?? '') }}</textarea>
                    </div>

                    <h3>Phương thức thanh toán</h3>
                    <div class="payment-method">
                        <label><input type="radio" name="payment_method" value="cod" checked> Thanh toán khi nhận hàng (COD)</label>
                    </div>
                    <div class="payment-method">
                        <label><input type="radio" name="payment_method" value="vnpay"> Thanh toán bằng VNPAY</label>
                    </div>

                    <div class="cart-summary">
                        <h3>Tổng thanh toán: <span id="totalPrice">{{ number_format($totalPrice, 0, ',', '.') }} ₫</span></h3>
                        <button type="submit" class="btn-checkout" id="checkoutBtn">Xác nhận thanh toán</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>
@endsection