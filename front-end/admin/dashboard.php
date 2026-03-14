<?php
session_start();

require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__) . "/includes/functions.php";
require_once dirname(__DIR__, 2) . "/back-end/admin/dashboard.php";

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>TechShop Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="asset/Dashboard.css">

</head>

<body>
    <main>
        <div class="admin-wrapper">

            <!-- SIDEBAR -->
            <div class="sidebar">
                <h2>💻 Tech Admin</h2>

                <a href="dashboard.php"
                    class="<?= (strpos($currentPage, 'dashboard.php') !== false && strpos($currentPage, 'orders') === false && strpos($currentPage, 'products') === false) ? 'active' : '' ?>">
                    <i class="fa fa-chart-line"></i> Trang chủ
                </a>

                <a href="orders/index.php"
                    class="<?= (strpos($currentPage, 'orders') !== false) ? 'active' : '' ?>">
                    <i class="fa fa-receipt"></i> Quản lý đơn hàng
                </a>

                <a href="products/product.php"
                    class="<?= (strpos($currentPage, 'products') !== false) ? 'active' : '' ?>">
                    <i class="fa-solid fa-list"></i> Quản lý sản phẩm
                </a>
                <a href="<?= FRONT_URL ?>/admin/categories/brand_category.php"
                    class="<?= (strpos($currentPath, '/admin/categories') !== false) ? 'active' : '' ?>">
                    <i class="fa fa-folder"></i> Quản lý thương hiệu
                </a>

                <a href="customers/customer.php"
                    class="<?= (strpos($currentPage, 'customers') !== false) ? 'active' : '' ?>">
                    <i class="fa fa-users"></i> Quản lý khách hàng
                </a>

                <a href="admin_auth/logout.php" style="color:#f87171;">
                    <i class="fa fa-sign-out-alt"></i> Đăng xuất
                </a>
            </div>

            <!-- MAIN CONTENT -->
            <div class="main">

                <div class="topbar">
                    <h1>Xin chào, <?= htmlspecialchars($_SESSION['admin_name']) ?></h1>

                </div>

                <div class="dashboard">

                    <div class="card" onclick="window.location.href='orders/index.php'">
                        <i class="fa fa-receipt"></i>
                        <h3>Tổng đơn hàng</h3>
                        <div class="value"><?= $totalOrders ?></div>
                    </div>

                    <div class="card">
                        <i class="fa fa-money-bill-wave"></i>
                        <h3>Doanh thu</h3>
                        <div class="value"><?= number_format($totalRevenue, 0, ',', '.') ?>₫</div>
                    </div>

                    <div class="card" onclick="window.location.href='products/product.php'">
                        <i class="fa fa-box"></i>
                        <h3>Sản phẩm</h3>
                        <div class="value"><?= $totalProducts ?></div>
                    </div>

                    <div class="card" onclick="window.location.href='customers/customer.php'">
                        <i class="fa fa-users"></i>
                        <h3>Khách hàng</h3>
                        <div class="value"><?= $totalUsers ?></div>
                    </div>

                </div>
                <div class="dashboard-extended">

                    <!-- Revenue Overview -->
                    <div class="card-box revenue-card">
                        <div class="card-header">
                            <div>
                                <h3>Tổng quan về doanh thu</h3>
                                <p>Doanh thu 7 ngày gần nhất</p>
                            </div>
                        </div>

                        <canvas id="revenueChart" height="100"></canvas>
                    </div>


                    <!-- Top Selling -->
                    <div class="card-box top-card">
                        <h3>Sản phẩm bán chạy nhất</h3>
                        <p class="sub">Laptop phổ biến nhất trong tuần</p>

                        <?php while ($row = $topProducts->fetch_assoc()): ?>
                            <div class="top-item">
                                <div class="product-info">

                                    <div class="product-image">
                                        <?php if (!empty($row['image_url'])): ?>
                                            <img src="<?= FRONT_URL ?>/assets/images/products/<?= htmlspecialchars($row['image_url']) ?>" alt="">
                                        <?php else: ?>
                                            <img src="<?= FRONT_URL ?>/assets/images/products/no-image.png" alt="">
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <strong><?= htmlspecialchars($row['name']) ?></strong>
                                        <div class="category">
                                            <?= htmlspecialchars($row['category_name'] ?? 'Chưa phân loại') ?>
                                        </div>
                                    </div>

                                </div>

                                <div class="sales">
                                    <?= $row['total_sold'] ?>
                                    <span>Bán ra</span>
                                </div>
                            </div>

                        <?php endwhile; ?>


                    </div>

                </div>


                <!-- Recent Orders -->
                <div class="card-box recent-card">

                    <div class="card-header">
                        <div>
                            <h3>Đơn đặt hàng gần đây</h3>
                            <p>Danh sách khách hàng mua hàng mới nhất</p>
                        </div>

                    </div>

                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>MÃ ĐƠN HÀNG</th>
                                <th>KHÁCH HÀNG</th>
                                <th>SẢN PHẨM</th>
                                <th>GIÁ</th>
                                <th>TRẠNG THÁI</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $statusLabels = [
                                'pending' => 'Chờ xử lý',
                                'paid' => 'Đã thanh toán',
                                'shipped' => 'Đang giao',
                                'completed' => 'Hoàn thành',
                                'cancel' => 'Đã hủy',
                                'failed' => 'Thanh toán thất bại'
                            ];
                            ?>
                            <?php while ($order = $recentOrders->fetch_assoc()): ?>
                                <tr>
                                    <td>#ORD-<?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['customer']) ?></td>
                                    <td><?= htmlspecialchars($order['product_name']) ?></td>
                                    <td><?= number_format($order['total_price'], 0, ',', '.') ?>₫</td>
                                    <td>
                                        <span class="status-badge <?= strtolower($order['display_status']) ?>">
                                            <?= htmlspecialchars($statusLabels[$order['display_status']] ?? 'Không xác định') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="orders/detail.php?id=<?= $order['id'] ?>" class="btn-view">
                                            Xem
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const revenueData = <?= json_encode($revenueByDay) ?>;

        const ctx = document.getElementById('revenueChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Doanh thu',
                    data: revenueData,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + '₫';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>