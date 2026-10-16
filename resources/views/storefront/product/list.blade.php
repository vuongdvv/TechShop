@extends('storefront.layout')

@section('title', 'Sản phẩm - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/style.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/productlist.css">
@endpush

@section('content')
<main>
    <div class="container">
        <div class="brands">
            <h2>Thương hiệu</h2>
            <div class="brand-list">
                <a href="{{ $frontUrl }}/product/list.php" class="brand-item {{ $brandSlug === '' ? 'active' : '' }}">Tất cả</a>
                @foreach($brands as $brand)
                @php
                $logoPath = !empty($brand->logo_image)
                ? $frontUrl . '/assets/images/brands/' . $brand->logo_image
                : $frontUrl . '/assets/images/brands/no-image.png';
                $query = request()->query();
                $query['brand'] = $brand->slug;
                unset($query['page']);
                @endphp
                <a href="{{ $frontUrl . '/product/list.php?' . http_build_query($query) }}" class="brand-item {{ $brandSlug === $brand->slug ? 'active' : '' }}">
                    <img src="{{ $logoPath }}" alt="{{ e($brand->name) }}">
                </a>
                @endforeach
            </div>
        </div>

        <div class="categories">
            <h2>Danh mục</h2>
            <div class="category-list">
                <a href="{{ $frontUrl }}/product/list.php" class="category-item {{ $categorySlug === '' ? 'active' : '' }}">Tất cả</a>
                @foreach($categories as $category)
                @php
                $query = request()->query();
                $query['category'] = $category->slug;
                unset($query['page']);
                @endphp
                <a href="{{ $frontUrl . '/product/list.php?' . http_build_query($query) }}" class="category-item {{ $categorySlug === $category->slug ? 'active' : '' }}">
                    <span>{{ e($category->name) }}</span>
                </a>
                @endforeach
            </div>
        </div>

        <h2 class="page-title">Danh sách sản phẩm</h2>
        <div class="product-grid">
            @if($products->isEmpty())
            <p>Không có sản phẩm nào.</p>
            @else
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
            @endif
        </div>
    </div>
</main>
@endsection