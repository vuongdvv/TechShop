@php
$currentPath = request()->getPathInfo();
@endphp

<div class="sidebar">
    <h2>💻 Tech Admin</h2>

    <a href="{{ $frontUrl }}/admin/dashboard.php" class="{{ str_contains($currentPath, '/admin/dashboard.php') ? 'active' : '' }}">
        <i class="fa fa-chart-line"></i> Trang chủ
    </a>

    <a href="{{ $frontUrl }}/admin/orders/index.php" class="{{ str_contains($currentPath, '/admin/orders') ? 'active' : '' }}">
        <i class="fa fa-receipt"></i> Quản lý đơn hàng
    </a>

    <a href="{{ $frontUrl }}/admin/products/product.php" class="{{ str_contains($currentPath, '/admin/products') ? 'active' : '' }}">
        <i class="fa-solid fa-list"></i> Quản lý sản phẩm
    </a>

    <a href="{{ $frontUrl }}/admin/categories/brand_category.php" class="{{ str_contains($currentPath, '/admin/categories') ? 'active' : '' }}">
        <i class="fa fa-folder"></i> Quản lý thương hiệu và danh mục
    </a>

    <a href="{{ $frontUrl }}/admin/customers/customer.php" class="{{ str_contains($currentPath, '/admin/customers') ? 'active' : '' }}">
        <i class="fa fa-users"></i> Quản lý người dùng
    </a>

    <a href="{{ $frontUrl }}/admin/admin_auth/logout.php" class="logout">
        <i class="fa fa-sign-out-alt"></i> Đăng xuất
    </a>
</div>