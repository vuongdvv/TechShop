<?php
require_once dirname(__DIR__) . "/config/config.php";
require_once dirname(__DIR__) . "/config/database.php";
require_once dirname(__DIR__) . "/back-end/home.php";
require_once __DIR__ . "/includes/functions.php";
require_once __DIR__ . "/includes/header.php";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>TechStore - Laptop chính hãng</title>
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/productlist.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <main>
        <!-- ===== BANNER ===== -->
        <div class="home-banner">
            <div class="banner-content">

            </div>
        </div>

        <!-- ===== BRAND LOGO ===== -->
        <div class="brands">
            <h2>Thương hiệu</h2>
            <div class="brand-list">
                <a href="?" class="brand-item <?= ($brandFilter === '') ? 'active' : '' ?>">
                    Tất cả
                </a>
                <?php foreach ($brands as $brand): ?>
                    <?php
                    $logoPath = !empty($brand['logo_image'])
                        ? FRONT_URL . '/assets/images/brands/' . $brand['logo_image']
                        : FRONT_URL . '/assets/images/brands/no-image.png';
                    ?>
                    <a href="?brand=<?= $brand['slug'] ?>"
                        class="brand-item <?= ($brandFilter === $brand['slug']) ? 'active' : '' ?>">
                        <img src="<?= $logoPath ?>"
                            alt="<?= e($brand['name']) ?>">
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ===== CATEGORY ===== -->
        <!-- <div class="categories">
            <h2>Danh mục</h2>
            <div class="category-list">
                <?php foreach ($categories as $category): ?>
                    <?php
                    if ($brandFilter !== '') {
                        $url = "?brand=" . $brandFilter . "&category=" . $category['slug'];
                    } else {
                        $url = "?category=" . $category['slug'];
                    }
                    ?>
                    <a href="<?= $url ?>"
                        class="category-item <?= ($categoryFilter === $category['slug']) ? 'active' : '' ?>">
                        <span><?= e($category['name']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div> -->


        <div class="featured-products">
            <div class="section-header">
                <h2>Sản phẩm nổi bật</h2>
                <a href="product/list.php">Xem tất cả</a>
            </div>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <?php
                    $imagePath = !empty($product['image_url'])
                        ? FRONT_URL . '/assets/images/products/' . $product['image_url']
                        : FRONT_URL . '/assets/images/products/no-image.png';
                    ?>
                    <div class="product-card">

                        <div class=" product-label">
                            <div class="product-rating">
                                <span class="star">★</span>
                                <span class="rating-number"><?= number_format($product['rating'], 1) ?></span>
                            </div>
                            <a href="<?= FRONT_URL ?>/product/detail.php?slug=<?= e($product['slug']) ?>" class="product-thumb">
                                <img src="<?= $imagePath ?>" alt="<?= e($product['name']) ?>">
                            </a>
                        </div>
                        <div class="product-info">
                            <div class="product-brand">
                                <?= strtoupper(e($product['brand_name'])) ?>
                            </div>
                            <h3 class="product-name">
                                <?= e($product['name']) ?>
                            </h3>
                            <div class="product-tags">
                                <?php if (!empty($product['cpu'])): ?>
                                    <span><?= e($product['cpu']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($product['ram'])): ?>
                                    <span><?= e($product['ram']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($product['ssd'])): ?>
                                    <span><?= e($product['ssd']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-price-box">
                                <div>
                                    <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                        <?php
                                        $discountPercent = round(
                                            (1 - ($product['sale_price'] / $product['price'])) * 100
                                        );
                                        ?>
                                        <div class="old-price-row">
                                            <span class="old-price"><?= formatPrice($product['price']) ?></span>
                                            <span class="discount-badge">-<?= $discountPercent ?>%</span>
                                        </div>
                                        <div class="new-price">
                                            <?= formatPrice($product['sale_price']) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="new-price">
                                            <?= formatPrice($product['price']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="product-actions">
                                <a href="<?= BASE_URL ?>/back-end/cart/add.php?id=<?= $product['id'] ?>&redirect=list"
                                    class="cart-icon">
                                    <i class="fa fa-cart-plus"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/back-end/cart/add.php?id=<?= $product['id'] ?>&buy_now=1"
                                    class="btn btn-primary">
                                    Mua ngay
                                </a>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="promotion">
            <div class="promo-box">
                <div class="promo-content">
                    <h2>Mùa Tự Trường<br>Giảm giá tới 22%</h2>
                    <p>Ưu đãi laptop cho học sinh – sinh viên trong thời gian có hạn.</p>
                    <a href="product/list.php" class="btn-outline-white">Xem khuyến mãi</a>
                </div>
                <div class="promo-image">
                    <img src="<?= FRONT_URL ?>/assets/images/products/banner-promo.jpg" alt="">
                </div>
            </div>
        </div>
        <section class="services">
            <div class="service-item">
                <i class="fa fa-shield"></i>
                <h4>Bảo hành chính hãng</h4>
                <p>100% laptop chính hãng</p>
            </div>
            <div class="service-item">
                <i class="fa fa-truck"></i>
                <h4>Miễn phí vận chuyển</h4>
                <p>Toàn quốc – Nhanh chóng</p>
            </div>
            <div class="service-item">
                <i class="fa fa-headset"></i>
                <h4>Hỗ trợ 24/7</h4>
                <p>Đội ngũ kỹ thuật chuyên nghiệp</p>
            </div>
        </section>
    </main>
    <?php include 'includes/footer.php'; ?>

</body>

</html>