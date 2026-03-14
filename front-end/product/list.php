<?php
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__) . "/includes/functions.php";
require_once dirname(__DIR__) . "/includes/header.php";
require_once dirname(__DIR__, 2) . "/back-end/product/product-list.php";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sản phẩm - <?= SITE_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/productlist.css">

</head>

<body>


    <main>
        <div class="container">

            <div class="brands">
                <h2>Thương hiệu</h2>
                <div class="brand-list">
                    <a href="<?= $allBrandHref ?>" class="brand-item <?= ($brandFilter == 0) ? 'active' : '' ?>">
                        Tất cả
                    </a>
                    <?php foreach ($brands as $brand): ?>
                        <?php
                        $logoPath = !empty($brand['logo_image'])
                            ? FRONT_URL . '/assets/images/brands/' . $brand['logo_image']
                            : FRONT_URL . '/assets/images/brands/no-image.png';
                        $brandQuery = $_GET;
                        $brandQuery['brand'] = $brand['slug'];
                        unset($brandQuery['page']);
                        $brandHref = '?' . http_build_query($brandQuery);
                        ?>
                        <a href="<?= $brandHref ?>"
                            class="brand-item <?= (($brandSlug == $brand['slug'])) ? 'active' : '' ?>">
                            <img src="<?= $logoPath ?>"
                                alt="<?= e($brand['name']) ?>">
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ===== CATEGORY ===== -->

            <div class="categories">
                <h2>Danh mục</h2>
                <div class="category-list">
                    <a href="<?= $allCatHref ?>" class="category-item <?= empty($categorySlug) ? 'active' : '' ?>">
                        Tất cả
                    </a>
                    <?php foreach ($categories as $category): ?>
                        <?php
                        if (!empty($brandSlug)) {
                            $url = "?brand=" . $brandSlug . "&category=" . $category['slug'];
                        } else {
                            $url = "?category=" . $category['slug'];
                        }
                        ?>
                        <a href="<?= $url ?>"
                            class="category-item <?= ($categorySlug === $category['slug']) ? 'active' : '' ?>">
                            <span><?= e($category['name']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <h2 class="page-title">
                Danh sách sản phẩm
            </h2>

            <div class="product-grid">
                <?php if (empty($products)): ?>
                    <p>Không có sản phẩm nào.</p>
                <?php else: ?>
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
                                        class="cart-icon"> <i class="fa fa-cart-plus"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/back-end/cart/add.php?id=<?= $product['id'] ?>&buy_now=1"
                                        class="btn btn-primary"> Mua ngay
                                    </a>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
            <?php if ($totalProducts > count($products)): ?>
                <div class="load-more-wrap">
                    <button id="loadMoreBtn"
                        data-page="1"
                        data-total="<?= $totalProducts ?>"
                        data-loaded="<?= count($products) ?>"
                        data-keyword="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>"
                        data-brand="<?= htmlspecialchars($_GET['brand'] ?? '') ?>"
                        data-ram="<?= htmlspecialchars($_GET['ram'] ?? '') ?>"
                        data-ssd="<?= htmlspecialchars($_GET['ssd'] ?? '') ?>"
                        data-category="<?= htmlspecialchars($_GET['category'] ?? '') ?>"
                        class="load-more-btn">
                        Xem thêm sản phẩm <i class="fa fa-chevron-down"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php include dirname(__DIR__) . "/includes/footer.php"; ?>

    <script src="<?= FRONT_URL ?>/js/listproduct.js"></script>


</body>

</html>