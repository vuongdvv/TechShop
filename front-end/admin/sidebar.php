        <div class="sidebar">
            <h2>💻 Tech Admin</h2>

            <a href="<?= FRONT_URL ?>/admin/dashboard.php"
                class="<?= ($currentPath === '/admin/dashboard.php') ? 'active' : '' ?>">
                <i class="fa fa-chart-line"></i> Trang chủ
            </a>

            <a href="<?= FRONT_URL ?>/admin/orders/index.php"
                class="<?= (strpos($currentPath, '/admin/orders') !== false) ? 'active' : '' ?>">
                <i class="fa fa-receipt"></i> Quản lý đơn hàng
            </a>

            <a href="<?= FRONT_URL ?>/admin/products/product.php"
                class="<?= (strpos($currentPath, '/admin/products') !== false) ? 'active' : '' ?>">
                <i class="fa-solid fa-list"></i> Quản lý sản phẩm
            </a>

            <a href="<?= FRONT_URL ?>/admin/categories/brand_category.php"
                class="<?= (strpos($currentPath, '/admin/categories') !== false) ? 'active' : '' ?>">
                <i class="fa fa-folder"></i> Quản lý thương hiệu
            </a>

            <a href="<?= FRONT_URL ?>/admin/customers/customer.php"
                class="<?= (strpos($currentPath, '/admin/customers') !== false) ? 'active' : '' ?>">
                <i class="fa fa-users"></i> Quản lý khách hàng
            </a>

            <a href="<?= FRONT_URL ?>/admin/admin_auth/logout.php" class="logout">
                <i class="fa fa-sign-out-alt"></i> Đăng xuất
            </a>
        </div>