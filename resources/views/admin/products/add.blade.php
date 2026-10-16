@extends('admin.layout')

@section('title', 'Thêm sản phẩm mới')

@push('styles')
<link rel="stylesheet" href="{{ $frontUrl }}/admin/asset/product.css">
<link rel="stylesheet" href="{{ $frontUrl }}/admin/asset/addproduct.css">
@endpush

@section('content')
<div class="admin-wrapper">
    @include('admin.partials.sidebar')
    <div class="admin-container">
        <div class="breadcrumbs">
            <a href="{{ $frontUrl }}/admin/products/product.php">Sản phẩm</a> >
            <span>Thêm sản phẩm mới</span>
        </div>

        @if(session('error'))
        <div class="toast-error">
            <p>{{ session('error') }}</p>
        </div>
        @endif

        <div class="top-bar">
            <h1>Thêm sản phẩm mới</h1>
            <div>
                <a href="{{ $frontUrl }}/admin/products/product.php" class="btn-cancel">Hủy</a>
                <button form="productForm" type="submit" class="btn-save">Lưu sản phẩm</button>
            </div>
        </div>

        <form method="POST" action="{{ $frontUrl }}/admin/products/addproduct.php" enctype="multipart/form-data" id="productForm" class="grid-layout" autocomplete="off">
            @csrf
            <div class="left-column">
                <div class="card">
                    <h3>1. Thông tin chung</h3>
                    <label>Tên sản phẩm</label>
                    <input type="text" name="name" value="{{ old('name') }}">

                    <label>Slug sản phẩm</label>
                    <input type="text" name="slug" value="{{ old('slug') }}">

                    <div class="row-2">
                        <div>
                            <label>Thương hiệu</label>
                            <select name="brand_id" required>
                                <option value="">Chọn thương hiệu</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Danh mục</label>
                            <select name="category_id" required>
                                <option value="">Chọn danh mục</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3>3. Thông số kỹ thuật</h3>
                    <div class="row-2"><input type="text" name="cpu" placeholder="CPU" value="{{ old('cpu') }}"><input type="text" name="ram" placeholder="RAM" value="{{ old('ram') }}"></div>
                    <div class="row-2"><input type="text" name="ssd" placeholder="SSD" value="{{ old('ssd') }}"><input type="text" name="gpu" placeholder="GPU" value="{{ old('gpu') }}"></div>
                    <div class="row-2"><input type="text" name="screen" placeholder="Màn hình" value="{{ old('screen') }}"><input type="text" name="pin" placeholder="Pin" value="{{ old('pin') }}"></div>
                    <div class="row-2"><input type="text" name="he_dieu_hanh" placeholder="Hệ điều hành" value="{{ old('he_dieu_hanh') }}"><input type="text" name="kich_thuoc" placeholder="Kích thước" value="{{ old('kich_thuoc') }}"></div>
                </div>

                <div class="card">
                    <h3>4. Mô tả sản phẩm</h3>
                    <textarea name="description" rows="6" placeholder="Nhập mô tả chi tiết sản phẩm...">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="right-column">
                <div class="card">
                    <h3>2. Giá & Kho hàng</h3>
                    <label>Giá gốc (VND)</label>
                    <input type="number" name="price" min="0" value="{{ old('price') }}">
                    <label>Giá khuyến mãi (VND)</label>
                    <input type="number" name="sale_price" min="0" value="{{ old('sale_price') }}">
                    <label>Số lượng</label>
                    <input type="number" name="stock" min="0" value="{{ old('stock') }}">
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
                            <img id="previewImage" src="{{ $frontUrl }}/assets/images/products/no-image.png" style="width:150px; border:1px solid #ccc; padding:5px; display:none;">
                            <span id="removeImage" class="removeImage" style="display:none;">&times;</span>
                        </div>
                    </div>
                </div>

                <div class="card status-card">
                    <h3>Trạng thái</h3>
                    <label class="status-option"><input type="radio" name="status" value="1" checked><span>Công khai <small>(Hiển thị ngay)</small></span></label>
                    <label class="status-option"><input type="radio" name="status" value="0"><span>Bản nháp</span></label>
                </div>
            </div>

            @if(request()->query('success'))
            <div id="toast" class="toast-success">✔ Lưu sản phẩm thành công</div>
            @endif
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const defaultImage = "{{ $frontUrl }}/assets/images/products/no-image.png";
</script>
<script src="{{ $frontUrl }}/js/admin_addproduct.js"></script>
@endpush