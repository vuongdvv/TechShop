@extends('storefront.layout')

@section('title', 'Giỏ hàng - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/style.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/cart.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/checkout.css">
@endpush

@section('content')
<main>
    <div class="container">
        <h2 class="page-title">Giỏ hàng của bạn</h2>

        @if($cartItems->isEmpty())
        <div class="empty-cart">
            <div class="empty-cart-content">
                <img src="{{ $frontUrl }}/assets/images/products/empty-cart.png" alt="Giỏ hàng trống">
                <h3>Giỏ hàng trống</h3>
                <p>Không có sản phẩm nào trong giỏ hàng</p>
                <a href="{{ $frontUrl }}/product/list.php" class="btn-continue-shopping">← Tiếp tục mua sắm</a>
            </div>
        </div>
        @else
        <form action="{{ $frontUrl }}/cart/checkout.php" method="post" id="checkoutForm">
            @csrf
            <div class="cart-page">
                <div class="cart-card">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <td></td>
                                <td data-label="Sản phẩm">Sản phẩm</td>
                                <td data-label="Giá">Giá</td>
                                <td data-label="Số lượng">Số lượng</td>
                                <td data-label="Tổng cộng">Tổng cộng</td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cartItems as $item)
                            @php
                            $imagePath = !empty($item->image_url)
                            ? $frontUrl . '/assets/images/products/' . $item->image_url
                            : $frontUrl . '/assets/images/products/no-image.png';
                            $lineTotal = ((float) $item->price) * ((int) $item->quantity);
                            @endphp
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_items[]" value="{{ $item->cart_item_id }}" class="item-checkbox" data-price="{{ $lineTotal }}">
                                </td>
                                <td>
                                    <div class="cart-product">
                                        <img src="{{ $imagePath }}" alt="{{ e($item->name) }}">
                                        <div>
                                            <a href="{{ $frontUrl . '/product/detail.php?slug=' . $item->slug }}">{{ e($item->name) }}</a>
                                        </div>
                                    </div>
                                </td>
                                <td class="cart-price">{{ number_format((float) $item->price, 0, ',', '.') }} ₫</td>
                                <td>
                                    <div class="qty-box">
                                        <button type="button" class="js-decrease-qty" data-cart-item-id="{{ $item->cart_item_id }}" data-price="{{ (int) $item->price }}">−</button>
                                        <input type="number" name="quantity" min="1" value="{{ $item->quantity }}" data-cart-item-id="{{ $item->cart_item_id }}" data-price="{{ (int) $item->price }}">
                                        <button type="button" class="js-increase-qty" data-cart-item-id="{{ $item->cart_item_id }}" data-price="{{ (int) $item->price }}">+</button>
                                    </div>
                                </td>
                                <td class="cart-price-total">{{ number_format($lineTotal, 0, ',', '.') }} ₫</td>
                                <td>
                                    <a href="{{ $frontUrl . '/cart/remove.php?id=' . $item->cart_item_id }}" class="remove-btn"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <a href="{{ $frontUrl }}/product/list.php" class="cart-continue-shopping">← Tiếp tục mua sắm</a>
                </div>

                <div class="cart-card cart-summary">
                    <h3>Tóm tắt đơn hàng</h3>
                    <div class="summary-row">
                        <span>Tạm tính</span>
                        <span id="subtotal">0 đ</span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng cộng</span>
                        <span id="total">0 đ</span>
                    </div>
                    <button type="submit" class="btn-checkout">Tiến hành thanh toán</button>
                </div>
            </div>
        </form>
        @endif
    </div>
</main>
@endsection

@push('scripts')
<script src="{{ $frontUrl }}/js/cart.js"></script>
@endpush