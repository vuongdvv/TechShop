@extends('admin.layout')

@section('title', 'Quản lý Thương hiệu & Danh mục')

@push('styles')
<link rel="stylesheet" href="{{ $frontUrl }}/admin/asset/product.css">
<link rel="stylesheet" href="{{ $frontUrl }}/admin/asset/brand_category.css">
@endpush

@section('content')
<div id="toastContainer" class="toast-container"></div>

<div class="admin-wrapper">
    @include('admin.partials.sidebar')

    <div class="admin-container">
        <div class="admin-layout">
            <div class="page-header">
                <div>
                    <h1>Quản lý Thương hiệu & Danh mục</h1>
                    <p>Quản lý thương hiệu và danh mục sản phẩm</p>
                </div>
                <div class="header-actions">
                    <button class="btn-primary-add" onclick="openCategoryModal()">+ Thêm danh mục</button>
                    <button class="btn-primary-add" onclick="openBrandModal()">+ Thêm thương hiệu</button>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card"><i class="fa fa-box"></i>
                    <div>
                        <div>Tổng Thương hiệu</div>
                        <div class="stat-number">{{ $totalBrands }}</div>
                    </div>
                </div>
                <div class="stat-card"><i class="fa fa-folder"></i>
                    <div>
                        <div>Tổng Danh mục</div>
                        <div class="stat-number">{{ $totalCategories }}</div>
                    </div>
                </div>
            </div>

            <div class="table-wraper">
                <div class="left-table">
                    <h2>Danh sách Thương hiệu</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Logo</th>
                                <th>Thương hiệu</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($brands as $idx => $brand)
                            @php
                            $logoPath = !empty($brand->logo_image)
                            ? $frontUrl . '/assets/images/brands/' . $brand->logo_image
                            : $frontUrl . '/assets/images/brands/no-image.png';
                            @endphp
                            <tr data-id="brand-{{ $brand->id }}">
                                <td>{{ $idx + 1 }}</td>
                                <td><img src="{{ $logoPath }}" alt="{{ $brand->name }}" style="width:50px;"></td>
                                <td class="brand-name">{{ $brand->name }}</td>
                                <td>
                                    <button type="button" class="btn btn-edit" data-brand-id="{{ $brand->id }}" onclick="openEditBrandModal(this.dataset.brandId)">Sửa</button>
                                    <a href="{{ $frontUrl }}/admin/categories/delete_brand_category.php?id={{ $brand->id }}&type=brand" class="btn btn-delete" onclick="return confirm('Xóa thương hiệu này?')">Xóa</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="right-table">
                    <h2>Danh sách Danh mục</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Danh mục</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $idx => $category)
                            <tr data-id="category-{{ $category->id }}">
                                <td>{{ $idx + 1 }}</td>
                                <td class="category-name">{{ $category->name }}</td>
                                <td>
                                    <button type="button" class="btn btn-edit" data-category-id="{{ $category->id }}" onclick="openEditModal(this.dataset.categoryId)">Sửa</button>
                                    <a href="{{ $frontUrl }}/admin/categories/delete_brand_category.php?id={{ $category->id }}&type=category" class="btn btn-delete" onclick="return confirm('Xóa danh mục này?')">Xóa</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="brandModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeBrandModal()">&times;</span>
                    <h2>Thêm thương hiệu</h2>
                    <form id="addBrandForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="brand">
                        <div class="form-group"><label>Tên thương hiệu</label><input type="text" id="brand_name" name="name" required></div>
                        <div class="form-group"><label>Slug</label><input type="text" id="brand_slug" name="slug" readonly></div>
                        <div class="form-group"><label>Chọn logo</label><input type="file" name="logo" id="add_brand_logo" accept="image/*"></div>
                        <div class="form-group preview-group"><label>Preview</label>
                            <div class="preview-container"><img id="add_brand_preview" src="" style="display:none;"></div>
                        </div>
                        <button type="submit" class="btn-primary-save">Thêm</button>
                    </form>
                </div>
            </div>

            <div id="categoryModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeCategoryModal()">&times;</span>
                    <h2>Thêm danh mục</h2>
                    <form id="addCategoryForm">
                        @csrf
                        <input type="hidden" name="type" value="category">
                        <div class="form-group"><label>Tên danh mục</label><input type="text" id="category_name" name="category_name" autocomplete="off" required></div>
                        <div class="form-group"><label>Slug</label><input type="text" id="slug" name="slug" readonly></div>
                        <button type="submit" class="btn-primary-save">Thêm</button>
                    </form>
                </div>
            </div>

            <div id="editBrandModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeEditBrandModal()">&times;</span>
                    <h2>Sửa thương hiệu</h2>
                    <form id="editBrandForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="brand">
                        <input type="hidden" name="id" id="edit_brand_id">
                        <div class="form-group"><label>Tên thương hiệu</label><input type="text" name="name" id="edit_brand_name" required></div>
                        <div class="form-group"><label>Slug</label><input type="text" name="slug" id="edit_brand_slug" readonly></div>
                        <div class="form-group preview-group"><label>Logo hiện tại</label>
                            <div class="preview-container"><img id="edit_brand_preview" src=""></div>
                        </div>
                        <div class="form-group"><label>Chọn ảnh mới</label><input type="file" name="logo" id="edit_brand_logo" accept="image/*"></div>
                        <button type="submit" class="btn-primary-save">Cập nhật</button>
                    </form>
                </div>
            </div>

            <div id="editModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeEditModal()">&times;</span>
                    <h2>Sửa danh mục</h2>
                    <form id="editForm">
                        @csrf
                        <input type="hidden" name="type" value="category">
                        <input type="hidden" name="id" id="edit_id">
                        <label>Tên danh mục</label>
                        <input type="text" name="name" id="edit_name" required>
                        <label>Slug</label>
                        <input type="text" id="edit_slug" name="slug" readonly>
                        <button type="submit" class="btn-primary-save">Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const BASE_URL = '{{ $baseUrl }}';
    const FRONT_URL = '{{ $frontUrl }}';
</script>
<script src="{{ $frontUrl }}/js/brand_category.js"></script>
@endpush