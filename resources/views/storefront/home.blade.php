@extends('storefront.layout')

@section('title', 'TechStore - Laptop chính hãng')

@push('styles')
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/home.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/style.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/productlist.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endpush

@section('content')
<main>
    <div class="home-banner">
        <div class="banner-content"></div>
    </div>

    <div class="brands">
        <h2>Thương hiệu</h2>
        <div class="brand-list">
            <a href="{{ $frontUrl }}/home.php" class="brand-item {{ $brandFilter === '' ? 'active' : '' }}">Tất cả</a>
            @foreach($brands as $brand)
            @php
            $logoPath = !empty($brand->logo_image)
            ? $frontUrl . '/assets/images/brands/' . $brand->logo_image
            : $frontUrl . '/assets/images/brands/no-image.png';
            @endphp
            <a href="{{ $frontUrl . '/home.php?brand=' . $brand->slug }}" class="brand-item {{ $brandFilter === $brand->slug ? 'active' : '' }}">
                <img src="{{ $logoPath }}" alt="{{ e($brand->name) }}">
            </a>
            @endforeach
        </div>
    </div>

    <div class="featured-products">
        <div class="section-header">
            <h2>Sản phẩm nổi bật</h2>
            <a href="{{ $frontUrl }}/product/list.php">Xem tất cả</a>
        </div>
        <div class="product-grid">
            @foreach($products as $product)
            @php
            $imagePath = !empty($product->image_url)
            ? $frontUrl . '/assets/images/products/' . $product->image_url
            : $frontUrl . '/assets/images/products/no-image.png';
            @endphp
            <div class="product-card">
                <div class="product-label">
                    <div class="product-rating">
                        <span class="star">★</span>
                        <span class="rating-number">{{ number_format((float) $product->rating, 1) }}</span>
                    </div>
                    <a href="{{ $frontUrl . '/product/detail.php?slug=' . $product->slug }}" class="product-thumb">
                        <img src="{{ $imagePath }}" alt="{{ e($product->name) }}">
                    </a>
                </div>
                <div class="product-info">
                    <div class="product-brand">{{ strtoupper(e((string) $product->brand_name)) }}</div>
                    <h3 class="product-name">{{ e($product->name) }}</h3>
                    <div class="product-tags">
                        @if(!empty($product->cpu))<span>{{ e($product->cpu) }}</span>@endif
                        @if(!empty($product->ram))<span>{{ e($product->ram) }}</span>@endif
                        @if(!empty($product->ssd))<span>{{ e($product->ssd) }}</span>@endif
                    </div>
                    <div class="product-price-box">
                        <div>
                            @if(!empty($product->sale_price) && $product->sale_price < $product->price)
                                @php $discountPercent = round((1 - ($product->sale_price / $product->price)) * 100); @endphp
                                <div class="old-price-row">
                                    <span class="old-price">{{ number_format((float) $product->price, 0, ',', '.') }} ₫</span>
                                    <span class="discount-badge">-{{ $discountPercent }}%</span>
                                </div>
                                <div class="new-price">{{ number_format((float) $product->sale_price, 0, ',', '.') }} ₫</div>
                                @else
                                <div class="new-price">{{ number_format((float) $product->price, 0, ',', '.') }} ₫</div>
                                @endif
                        </div>
                    </div>
                    <div class="product-actions">
                        <a href="#" data-product-id="{{ $product->id }}" class="cart-icon js-add-to-cart"><i class="fa fa-cart-plus"></i></a>
                        <a href="{{ $frontUrl . '/cart/add.php?id=' . $product->id }}" class="btn btn-primary">Mua ngay</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="promotion">
        <div class="promo-box">
            <div class="promo-content">
                <h2>Mùa Tự Trường<br>Giảm giá tới 22%</h2>
                <p>Ưu đãi laptop cho học sinh – sinh viên trong thời gian có hạn.</p>
                <a href="{{ $frontUrl }}/product/list.php" class="btn-outline-white">Xem khuyến mãi</a>
            </div>
            <div class="promo-image">
                <img src="{{ $frontUrl }}/assets/images/products/banner-promo.jpg" alt="Banner khuyến mãi">
            </div>
        </div>
    </div>

    <section class="services">
        <div class="service-item">
            <i class="fa fa-shield"></i>
            <h4>Bảo hành chính hãng</h4>
            <p>100% laptop chính hãng</p>
        </div>
        <div class="service-item">
            <i class="fa fa-truck"></i>
            <h4>Miễn phí vận chuyển</h4>
            <p>Toàn quốc – Nhanh chóng</p>
        </div>
        <div class="service-item">
            <i class="fa fa-headset"></i>
            <h4>Hỗ trợ 24/7</h4>
            <p>Đội ngũ kỹ thuật chuyên nghiệp</p>
        </div>
    </section>
</main>
@endsection