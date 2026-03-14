<?php
session_start();

require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/includes/functions.php";
require_once dirname(__DIR__, 3) . "/back-end/admin/orders/order_detail.php";

?>

<!DOCTYPE html>
<html lang="vi">


<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?= $order['id'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/admin/asset/OrdersDetail.css">
</head>

<body>

    <div class="admin-wrapper">

        <!-- SIDEBAR -->
        <?php include dirname(__DIR__) . "/sidebar.php"; ?>

        <!-- CONTENT -->
        <div class="admin-container">

            <div class="breadcrumbs">
                <a href="<?= FRONT_URL ?>/admin/dashboard.php">Dashboard</a> >
                <a href="<?= FRONT_URL ?>/admin/orders/index.php">Đơn hàng</a> >
                <span>Chi tiết đơn hàng </span>



                <h2 class="page-title">Chi tiết đơn hàng #<?= $order['id'] ?></h2>

                <!-- THÔNG TIN KHÁCH -->
                <div class="box">
                    <div class="info-row"><strong>Khách hàng:</strong> <?= e($order['customer_name']) ?></div>
                    <div class="info-row"><strong>SĐT:</strong> <?= e($order['customer_phone']) ?></div>
                    <div class="info-row"><strong>Địa chỉ:</strong> <?= e($order['customer_address'] . ', ' . $order['city']) ?></div>
                    <div class="info-row"><strong>Ngày tạo:</strong> <?= $order['created_at'] ?></div>
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
                            'failed'    => ['status-red',   'Thanh toán thất bại'],
                        ];

                        [$class, $text] = $statusMap[$order['status']] ?? ['status-gray', 'Chờ xử lý'];
                        ?>

                        <span class="status-badge <?= $class ?>">
                            <?= $text ?>
                        </span>

                    </div>


                </div>

                <!-- SẢN PHẨM -->
                <div class="box">
                    <table>
                        <thead>
                            <tr>

                                <th class="product">Sản phẩm</th>
                                <th class="price">Giá</th>
                                <th class="quantity">Số lượng</th>
                                <th class="subtotal">Tạm tính</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>

                                <tr>
                                    <td class="product">
                                        <img src="<?= e($item['image_url'])
                                                        ? FRONT_URL . '/assets/images/products/' . $item['image_url']
                                                        : FRONT_URL . '/assets/images/products/no-image.png'; ?>">
                                        <?= e($item['name']) ?>
                                    </td>
                                    <td><?= formatPrice($item['price']) ?></td>
                                    <td class="quantity"><?= $item['quantity'] ?></td>
                                    <td><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="total">
                        Tổng tiền: <?= formatPrice($order['total_price']) ?>
                    </div>
                </div>

                <!-- CẬP NHẬT TRẠNG THÁI -->
                <div class="box">
                    <form action="<?= BASE_URL ?>/back-end/admin/orders/update_status.php" method="post">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                        <select name="status">
                            <?php
                            $current = $order['status'];
                            $nextStatuses = $allowedNextStatus[$current] ?? [];

                            $labels = [
                                'pending'   => 'Đơn mới tạo',
                                'paid'      => 'Đã thanh toán',
                                'shipped'   => 'Đang giao',
                                'completed' => 'Hoàn thành',
                                'failed'    => 'Thanh toán thất bại',
                                'cancel'  => 'Đã huỷ đơn hàng'
                            ];

                            foreach ($labels as $value => $label):
                                $disabled = '';

                                if ($value !== $current && !in_array($value, $allowedNextStatus[$current])) {
                                    $disabled = 'disabled';
                                }
                            ?>
                                <option value="<?= $value ?>"
                                    <?= $value == $current ? 'selected' : '' ?>
                                    <?= $disabled ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" class="btn btn-save">Cập nhật</button>
                    </form>


                </div>

            </div>

        </div>

</body>

</html>