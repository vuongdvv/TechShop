<?php
session_start();

require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__) . "/includes/functions.php";
require_once dirname(__DIR__) . "/includes/header.php";
require_once dirname(__DIR__, 2) . "/back-end/orders/detail.php";

?>



<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?= $order['id'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/cart.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/checkout.css">
</head>

<body>

    <!-- <?php include dirname(__DIR__) . "/includes/header.php"; ?> -->
    <main>
        <div class="container">
            <div class="breadcrumbs">
                <a href="<?= FRONT_URL ?>/home.php">Trang Chủ</a> >
                <a href="<?= FRONT_URL ?>/orders/index.php">Đơn hàng của tôi</a> >
                <span>Chi tiết đơn hàng </span>
            </div>
            <h2 class="page-title">🧾 Chi tiết đơn hàng #<?= $order['id'] ?></h2>

            <!-- THÔNG TIN ĐƠN -->
            <div class="order-info-box">
                <div class="info-row"><strong>Khách hàng:</strong> <?= e($order['customer_name']) ?></div>
                <div class="info-row"><strong>SĐT:</strong> <?= e($order['customer_phone']) ?></div>
                <div class="info-row"><strong>Địa chỉ:</strong> <?= e($order['customer_address'] . ', ' . $order['city']) ?></div>
                <div class="info-row"><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>

                <div class="info-row">
                    <strong>Thanh toán:</strong><?= strtoupper(e($order['payment_method'])) ?>
                </div>
                <div class="info-row">
                    <strong>Trạng thái:</strong>
                    <?php
                    $statusMap = [
                        'pending'   => ['status-gray',  'Chờ xử lý'],
                        'paid'      => ['status-green', 'Đã thanh toán'],
                        'shipped'   => ['status-orange', 'Đang giao'],
                        'completed' => ['status-blue',  'Hoàn tất'],
                        'cancel'    => ['status-red',   'Đã huỷ đơn'],
                        // 'failed'    => ['status-red',   'Thanh toán thất bại'],
                    ];

                    [$class, $text] = $statusMap[$order['status']] ?? ['status-gray', 'Chờ xử lý'];
                    ?>

                    <span class="status-badge <?= $class ?>">
                        <?= $text ?>
                    </span>

                </div>
            </div>

            <!-- DANH SÁCH SẢN PHẨM -->
            <table class="cart-table">
                <thead>
                    <tr>

                        <td class="data-label">Sản phẩm</td>
                        <td data-label="Giá">Giá</td>
                        <td data-label="SL">SL</td>
                        <td data-label="Tổng tiền">Tổng tiền</td>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $image = !empty($item['image_url'])
                            ? FRONT_URL . "/assets/images/products/" . $item['image_url']
                            : FRONT_URL . "/assets/images/products/no-image.png";

                        $subTotal = $item['price'] * $item['quantity'];
                        ?>
                        <tr>
                            <td class="cart-product">
                                <img src="<?= $image ?>" alt="<?= e($item['name']) ?>">
                                <a href="<?= FRONT_URL ?>/product/detail.php?slug=<?= e($item['slug']) ?>">
                                    <?= e($item['name']) ?>
                                </a>
                            </td>

                            <td><?= formatPrice($item['price']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= formatPrice($subTotal) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- TỔNG TIỀN -->
            <div class="cart-summary">
                <h3>Tổng cộng: <?= formatPrice($order['total_price']) ?></h3>
            </div>

        </div>
    </main>


</body>

</html>