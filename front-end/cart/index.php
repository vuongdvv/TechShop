<?php
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__) . "/includes/functions.php";
require_once dirname(__DIR__) . "/includes/header.php";
require_once dirname(__DIR__, 2) . "/back-end/cart/cart-index.php";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng - <?= SITE_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/cart.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/checkout.css">
</head>

<body>
    <main>
        <div class="container">
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="toast-error">
                    <?= $_SESSION['error'] ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <h2 class="page-title">Giỏ hàng của bạn</h2>
            <?php if (empty($cartItems)): ?>
                <div class="empty-cart">
                    <div class="empty-cart-content">
                        <img src="<?= FRONT_URL ?>/assets/images/products/empty-cart.png"
                            alt="Giỏ hàng trống">
                        <h3>Giỏ hàng trống</h3>
                        <p>Không có sản phẩm nào trong giỏ hàng</p>
                        <a href="<?= FRONT_URL ?>/product/list.php"
                            class="btn-continue-shopping">
                            ← Tiếp tục mua sắm
                        </a>
                    </div>
                <?php else: ?>
                    <form action="<?= FRONT_URL ?>/cart/checkout.php"
                        method="post"
                        id="checkoutForm">
                        <div class="cart-page">
                            <!-- ================= LEFT: CART LIST ================= -->
                            <div class="cart-card">

                                <table class="cart-table">
                                    <thead>
                                        <tr>
                                            <td></td>
                                            <td data-label="Sản phẩm">Sản Phẩm</td>
                                            <td data-label="Giá">Giá</td>
                                            <td data-label="Số lượng">Số lượng</td>
                                            <td data-label="Tổng cộng">Tổng cộng</td>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cartItems as $item): ?>
                                            <?php
                                            $imagePath = !empty($item['image_url'])
                                                ? FRONT_URL . "/assets/images/products/" . $item['image_url']
                                                : FRONT_URL . "/assets/images/products/no-image.png";
                                            ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox"
                                                        name="selected_items[]"
                                                        value="<?= $item['cart_item_id'] ?>"
                                                        class="item-checkbox"
                                                        data-price="<?= $item['price'] * $item['quantity'] ?>">
                                                </td>
                                                <td>
                                                    <div class="cart-product">
                                                        <img src="<?= $imagePath ?>" alt="<?= e($item['name']) ?>">
                                                        <div>
                                                            <a href="<?= FRONT_URL ?>/product/detail.php?slug=<?= e($item['slug']) ?>">
                                                                <?= e($item['name']) ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="cart-price">
                                                    <?= formatPrice($item['price']) ?>
                                                </td>
                                                <td>
                                                    <div class="qty-box">
                                                        <button type="button" onclick="decreaseQty(this, <?= $item['cart_item_id'] ?>, <?= $item['price'] ?>)">−</button>
                                                        <input type="number" name="quantity" min="1"
                                                            value="<?= $item['quantity'] ?>"
                                                            data-cart-item-id="<?= $item['cart_item_id'] ?>"
                                                            data-price="<?= $item['price'] ?>"
                                                            oninput="handleManualInput(this)">
                                                        <button type="button" onclick="increaseQty(this, <?= $item['cart_item_id'] ?>, <?= $item['price'] ?>)">+</button>
                                                    </div>
                                                </td>
                                                <td class="cart-price">
                                                    <?= formatPrice($item['price'] * $item['quantity']) ?>
                                                </td>
                                                <td>
                                                    <a href="<?= BASE_URL ?>/back-end/cart/remove.php?id=<?= $item['cart_item_id'] ?>"
                                                        class="remove-btn">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <a href="<?= FRONT_URL ?>/product/list.php" class="cart-continue-shopping">
                                    ← Tiếp tục mua sắm
                                </a>
                            </div>
                            <!-- ================= RIGHT: SUMMARY ================= -->
                            <div class="cart-card cart-summary">
                                <h3>Tóm tắt đơn hàng</h3>
                                <div class="summary-row">
                                    <span>Tạm tính</span>
                                    <span id="subtotal">0 đ</span>
                                </div>
                                <div class="summary-row total">
                                    <span>Tổng cộng</span>
                                    <span id="total">0 đ</span>
                                </div>
                                <button type="submit" class="btn-checkout">
                                    🔒 Tiến hành thanh toán
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const FRONT_URL = '<?= FRONT_URL ?>';
    </script>
    <script src="<?= FRONT_URL ?>/js/cart.js"></script>
</body>

</html>