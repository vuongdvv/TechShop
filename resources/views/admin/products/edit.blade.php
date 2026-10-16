@extends('admin.layout')

@section('title', 'Chỉnh sửa sản phẩm')

@push('styles')
<link rel="stylesheet" href="{{ $frontUrl }}/admin/asset/product.css">
<link rel="stylesheet" href="{{ $frontUrl }}/admin/asset/editproduct.css">
@endpush

@section('content')
<div class="admin-wrapper">
    @include('admin.partials.sidebar')
    <div class="admin-container">
        <div class="breadcrumbs">
            <a href="{{ $frontUrl }}/admin/products/product.php">Quản lý sản phẩm</a> >
            <span>Chỉnh sửa sản phẩm</span>
        </div>

        <div class="top-bar">
            <h1>Chỉnh sửa sản phẩm</h1>
            <div>
                <a href="{{ $frontUrl }}/admin/products/product.php" class="btn-cancel">Hủy</a>
                <button form="product-edit-form" type="submit" class="btn-save">Cập nhật sản phẩm</button>
            </div>
        </div>

        @if(session('error'))
        <div class="toast-error">
            <p>{{ session('error') }}</p>
        </div>
        @endif

        <form method="POST" action="{{ $frontUrl }}/admin/products/editproduct.php?id={{ $product->id }}" enctype="multipart/form-data" id="product-edit-form" class="grid-layout" autocomplete="off">
            @csrf
            <div class="left-column">
                <div class="card">
                    <h3>1. Thông tin chung</h3>
                    <label>Tên sản phẩm</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}">

                    <label>Slug sản phẩm</label>
                    <input type="text" name="slug" value="{{ old('slug', $product->slug) }}">

                    <div class="row-2">
                        <div>
                            <label>Thương hiệu</label>
                            <select name="brand_id">
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ (int)old('brand_id', $product->brand_id) === (int)$brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Danh mục</label>
                            <select name="category_id">
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (int)old('category_id', $product->category_id) === (int)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3>3. Thông số kỹ thuật</h3>
                    <div class="row-2"><input type="text" name="cpu" placeholder="CPU" value="{{ old('cpu', $variant->cpu ?? '') }}"><input type="text" name="ram" placeholder="RAM" value="{{ old('ram', $variant->ram ?? '') }}"></div>
                    <div class="row-2"><input type="text" name="ssd" placeholder="SSD" value="{{ old('ssd', $variant->ssd ?? '') }}"><input type="text" name="gpu" placeholder="GPU" value="{{ old('gpu', $variant->gpu ?? '') }}"></div>
                    <div class="row-2"><input type="text" name="screen" placeholder="Màn hình" value="{{ old('screen', $variant->screen ?? '') }}"><input type="text" name="pin" placeholder="Pin" value="{{ old('pin', $variant->pin ?? '') }}"></div>
                    <div class="row-2"><input type="text" name="he_dieu_hanh" placeholder="Hệ điều hành" value="{{ old('he_dieu_hanh', $variant->he_dieu_hanh ?? '') }}"><input type="text" name="kich_thuoc" placeholder="Kích thước" value="{{ old('kich_thuoc', $variant->kich_thuoc ?? '') }}"></div>
                </div>

                <div class="card">
                    <h4>4. Mô tả sản phẩm</h4>
                    <textarea name="description" rows="6">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>

            <div class="right-column">
                <div class="card">
                    <h3>2. Giá & Kho hàng</h3>
                    <div class="row-2">
                        <label>Giá gốc (VNĐ)</label>
                        <input type="number" name="price" value="{{ old('price', $product->price) }}">
                        <label>Giá khuyến mãi (VNĐ)</label>
                        <input type="number" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}">
                        <label>Số lượng</label>
                        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}">
                    </div>

                    <div class="card">
                        <h3>5. Hình ảnh</h3>
                        <div class="upload-box" id="uploadBox">
                            <input type="file" name="image" id="imageInput" accept="image/*" hidden>
                            <div class="upload-content" id="uploadContent">
                                <i class="fa fa-cloud-upload-alt upload-icon"></i>
                                <p class="upload-text">Kéo thả ảnh vào đây</p>
                                <span>Hỗ trợ JPG, PNG, WEBP (Max 5MB)</span>
                            </div>
                            <div style="position:relative; display:inline-block;">
                                @if(empty($image?->image_url))
                                <img id="previewImage" style="width:150px; border:1px solid #ccc; padding:5px; display:none;">
                                <span id="removeImage" class="removeImage" style="display:none;">&times;</span>
                                @else
                                <img id="previewImage" style="width:150px; border:1px solid #ccc; padding:5px;">
                                <span id="removeImage" class="removeImage" style="display:block;">&times;</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card status-card">
                        <h3>7. Trạng thái</h3>
                        <label class="status-option"><input type="radio" name="status" value="1" {{ (int)old('status', $product->status) === 1 ? 'checked' : '' }}><span>Công khai <small>(Hiển thị ngay)</small></span></label>
                        <label class="status-option"><input type="radio" name="status" value="0" {{ (int)old('status', $product->status) === 0 ? 'checked' : '' }}><span>Ẩn sản phẩm</span></label>
                    </div>
                </div>
            </div>
        </form>

        @if(request()->query('success'))
        <div id="toast" class="toast-success">✔ Cập nhật sản phẩm thành công</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    const defaultImage = "{{ $frontUrl }}/assets/images/products/{{ $image->image_url ?? 'no-image.png' }}";
    document.getElementById('previewImage').src = defaultImage;
</script>
<script src="{{ $frontUrl }}/js/admin_editproduct.js"></script>
@endpush