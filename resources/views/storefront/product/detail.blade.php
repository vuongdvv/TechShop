@extends('storefront.layout')

@section('title', e($product->name) . ' - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/style.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/productdetail.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/productlist.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/navbar.css">
@endpush

@section('content')
<main>
    <div class="container product-detail">
        <div class="breadcrumb">
            <a href="{{ $frontUrl }}/home.php">Trang chủ</a> /
            <a href="{{ $frontUrl }}/product/list.php">Sản phẩm</a> /
            <a href="{{ $frontUrl . '/product/list.php?category=' . $product->category_slug }}">{{ e($product->category_name) }}</a> /
            <span>{{ e($product->name) }}</span>
        </div>

        <div class="product-detail-grid">
            <div class="product-gallery">
                @php
                $mainImage = $images->first()->image_url ?? null;
                @endphp
                <img class="main-image" src="{{ $mainImage ? $frontUrl . '/assets/images/products/' . $mainImage : $frontUrl . '/assets/images/products/no-image.png' }}">
            </div>

            <div class="product-summary">
                <h1>{{ e($product->name) }}</h1>
                <div class="meta">
                    <span>Thương hiệu: <b>{{ e($product->brand_name) }}</b></span>
                    <span>Danh mục: {{ e($product->category_name) }}</span>
                </div>
                <div class="stock-info">
                    @if($product->stock > 0)
                    <span class="in-stock">Còn hàng ({{ (int) $product->stock }})</span>
                    @else
                    <span class="out-of-stock">Hết hàng</span>
                    @endif
                </div>

                <div class="price-box">
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

                @if($variants->isNotEmpty())
                <div class="variants">
                    <h4>Cấu hình:</h4>
                    @foreach($variants as $v)
                    <div class="variant"><span>{{ e($v->cpu) }} / {{ e($v->ram) }} / {{ e($v->ssd) }}</span></div>
                    @endforeach
                </div>
                @endif

                <div class="product-actions-detail">
                    <a href="{{ $frontUrl . '/cart/add.php?id=' . $product->id }}" class="btn-buy"><i class="fa fa-cart-plus"></i> Thêm vào giỏ hàng</a>
                    <a href="{{ $frontUrl . '/cart/add.php?id=' . $product->id }}" class="btn btn-primary">Mua ngay</a>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection