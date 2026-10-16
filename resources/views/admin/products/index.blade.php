@extends('admin.layout')

@section('title', 'Quản lý sản phẩm')

@push('styles')
<link rel="stylesheet" href="{{ $frontUrl }}/admin/asset/product.css">
@endpush

@section('content')
<div class="admin-wrapper">
    @include('admin.partials.sidebar')
    <div class="admin-container">
        <div class="admin-layout">
            <div class="page-header">
                <div>
                    <h1>Quản lý sản phẩm</h1>
                    <p>Quản lý kho hàng và danh mục sản phẩm</p>
                </div>
                <div class="header-actions">
                    <a href="{{ $frontUrl }}/admin/products/addproduct.php" class="btn-primary">+ Thêm sản phẩm mới</a>
                </div>
            </div>

            <div class="stats-grid">
                <a href="{{ $frontUrl }}/admin/products/product.php" class="stat-card-link {{ (!request()->has('low_stock') && !request()->has('out_stock')) ? 'active-card' : '' }}">
                    <div class="stat-card">
                        <div class="stat-icon blue"><i class="fa fa-box"></i></div>
                        <div>
                            <div class="stat-title">Tổng sản phẩm</div>
                            <div class="stat-number">{{ $totalProduct }}</div>
                        </div>
                    </div>
                </a>
                <a href="{{ $frontUrl }}/admin/products/product.php?low_stock=1" class="stat-card-link {{ request()->has('low_stock') ? 'active-card' : '' }}">
                    <div class="stat-card">
                        <div class="stat-icon orange"><i class="fa fa-exclamation"></i></div>
                        <div>
                            <div class="stat-title">Sắp hết hàng</div>
                            <div class="stat-number">{{ $lowStock }}</div>
                        </div>
                    </div>
                </a>
                <a href="{{ $frontUrl }}/admin/products/product.php?out_stock=1" class="stat-card-link {{ request()->has('out_stock') ? 'active-card' : '' }}">
                    <div class="stat-card">
                        <div class="stat-icon red"><i class="fa fa-exclamation"></i></div>
                        <div>
                            <div class="stat-title">Hết hàng</div>
                            <div class="stat-number">{{ $outOfStock }}</div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="table-wrapper">
                <form method="GET" class="table-filter">
                    <input type="text" name="keyword" placeholder="Tìm kiếm laptop..." autocomplete="off" value="{{ $keyword }}">
                    <button type="submit">Tìm kiếm</button>
                </form>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá bán</th>
                            <th>Giá gốc</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $row)
                        @php
                        $image = !empty($row->image_url)
                        ? $frontUrl . '/assets/images/products/' . $row->image_url
                        : $frontUrl . '/assets/images/products/no-image.png';
                        if ((int)$row->stock === 0) {
                        $status = '<span class="badge badge-red">Hết hàng</span>';
                        } elseif ((int)$row->stock <= 5) {
                            $status='<span class="badge badge-orange">Sắp hết (' . $row->stock . ')</span>';
                            } else {
                            $status = '<span class="badge badge-green">Còn hàng (' . $row->stock . ')</span>';
                            }
                            @endphp
                            <tr>
                                <td><img src="{{ $image }}" class="product-img"></td>
                                <td>
                                    <div class="product-name">{{ $row->name }}<small>{{ $row->brand_name }}</small></div>
                                </td>
                                <td><span class="category-tag">{{ $row->category_name }}</span></td>
                                <td class="price">{{ number_format($row->sale_price ?: $row->price) }}đ</td>
                                <td>{{ $row->sale_price ? number_format($row->price) . 'đ' : '' }}</td>
                                <td>{!! $status !!}</td>
                                <td class="actions">
                                    <a href="{{ $frontUrl }}/admin/products/editproduct.php?id={{ $row->id }}" class="btn-edit">Sửa</a>
                                    <button type="button" class="btn-delete" data-product-id="{{ $row->id }}" onclick="openDeleteModal(this.dataset.productId)">Xóa</button>
                                </td>
                            </tr>
                            @endforeach
                    </tbody>
                </table>

                <div class="pagination-wrapper">
                    <div class="pagination-info">Hiển thị {{ $start }}–{{ $end }} trên {{ $totalRows }} sản phẩm</div>
                    <div class="pagination">
                        @php
                        $query = request()->query();
                        @endphp
                        @if($page > 1)
                        @php $prev = $query; $prev['page'] = $page - 1; @endphp
                        <a href="{{ $frontUrl . '/admin/products/product.php?' . http_build_query($prev) }}">&laquo;</a>
                        @endif
                        @for($i = 1; $i <= $totalPages; $i++)
                            @php $qp=$query; $qp['page']=$i; @endphp
                            <a href="{{ $frontUrl . '/admin/products/product.php?' . http_build_query($qp) }}" class="{{ $i === $page ? 'active' : '' }}">{{ $i }}</a>
                            @endfor
                            @if($page < $totalPages)
                                @php $next=$query; $next['page']=$page + 1; @endphp
                                <a href="{{ $frontUrl . '/admin/products/product.php?' . http_build_query($next) }}">&raquo;</a>
                                @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3>Xác nhận xóa</h3>
        <p>Bạn có chắc muốn xóa sản phẩm này không?</p>
        <form method="POST" action="{{ $frontUrl }}/admin/products/deleteproduct.php">
            @csrf
            <input type="hidden" name="id" id="deleteId">
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Hủy</button>
                <button type="submit" class="btn-confirm">Xóa</button>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
<div id="toast" class="toast toast-success"><i class="fa fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div id="toast" class="toast toast-error"><i class="fa fa-times-circle"></i> {{ session('error') }}</div>
@endif
@endsection

@push('scripts')
<script>
    function openDeleteModal(id) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    const toast = document.getElementById('toast');
    if (toast) {
        setTimeout(() => toast.remove(), 3000);
    }
</script>
@endpush