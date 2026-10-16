<?php
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__) . "/includes/functions.php";
require_once dirname(__DIR__) . "/includes/header.php";
require_once dirname(__DIR__, 2) . "/back-end/product/product-detail.php";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?= e($product['name']) ?> - <?= SITE_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/productdetail.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/productlist.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/navbar.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/reviews.css">
</head>

<body>
    <main>
        <div class="container product-detail">
            <div class="breadcrumb">
                <a href="<?= FRONT_URL ?>/home.php">Trang chủ</a> /
                <a href="<?= FRONT_URL ?>/product/list.php">Sản phẩm</a> /
                <a href="<?= FRONT_URL ?>/product/list.php?category=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a> /
                <span><?= e($product['name']) ?></span>
            </div>
            <div class="product-detail-grid">
                <div class="product-gallery">
                    <?php
                    $mainImage = $images[0]['image_url'] ?? null;
                    ?>
                    <img class="main-image"
                        src="<?= $mainImage ? FRONT_URL . '/assets/images/products/' . $mainImage : FRONT_URL . '/assets/images/products/no-image.png' ?>">
                </div>

                <div class="product-summary">
                    <h1><?= e($product['name']) ?></h1>
                    <div class="meta"> <span>Thương hiệu: <b><?= e($product['brand_name']) ?></b></span>
                        <span>Danh mục: <?= e($product['category_name']) ?></span>
                    </div>
                    <div class="stock-info">
                        <?php if ($product['stock'] > 0): ?>
                            <span class="in-stock">
                                Còn hàng (<?= (int)$product['stock'] ?>)
                            </span>
                        <?php else: ?>
                            <span class="out-of-stock">
                                Hết hàng
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="price-box">
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
                    <?php if ($variants): ?> <div class="variants">
                            <h4>Cấu hình:</h4> <?php foreach ($variants as $v): ?>
                                <div class="variant">
                                    <span><?= e($v['cpu']) ?> / <?= e($v['ram']) ?> / <?= e($v['ssd']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div> <?php endif; ?>
                    <div class="product-actions-detail">
                        <a href="<?= BASE_URL ?>/back-end/cart/add.php?id=<?= $product['id'] ?>&redirect=detail&slug=<?= $product['slug'] ?>"
                            class="btn-buy">
                            <i class="fa fa-cart-plus"></i> Thêm vào giỏ hàng
                        </a>
                        <a href="<?= BASE_URL ?>/back-end/cart/add.php?id=<?= $product['id'] ?>&buy_now=1"
                            class="btn btn-primary">
                            Mua ngay
                        </a>
                    </div>
                </div>
            </div>
            <div class="product-extra">
                <div class="extra-grid">
                    <div class="spec-box">
                        <h3>Cấu hình chi tiết</h3>
                        <table class="spec-table">
                            <?php if (!empty($variants)): ?>
                                <?php
                                $v = $variants[0];
                                ?>
                                <tr>
                                    <td>CPU</td>
                                    <td><?= e($v['cpu']) ?></td>
                                </tr>
                                <tr>
                                    <td>RAM</td>
                                    <td><?= e($v['ram']) ?></td>
                                </tr>
                                <tr>
                                    <td>Ổ cứng</td>
                                    <td><?= e($v['ssd']) ?></td>
                                </tr>
                                <tr>
                                    <td>Card đồ họa</td>
                                    <td><?= e($v['gpu']) ?></td>
                                </tr>
                                <tr>
                                    <td>Màn hình</td>
                                    <td><?= e($v['screen']) ?></td>
                                </tr>
                                <tr>
                                    <td>Pin</td>
                                    <td><?= e($v['pin']) ?></td>
                                </tr>
                                <tr>
                                    <td>Hệ điều hành</td>
                                    <td><?= e($v['he_dieu_hanh']) ?></td>
                                </tr>
                                <tr>
                                    <td>Kích thước</td>
                                    <td><?= e($v['kich_thuoc']) ?></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2">Đang cập nhật cấu hình</td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <div class="desc-box">
                        <h3>Mô tả ngắn</h3>

                        <p class="short-desc">
                            <?= nl2br(e(mb_strimwidth($product['description'], 0, 300, '...'))) ?>
                        </p>
                        <div class="promo-box">
                            <div class="promo-title">
                                <i class="fa fa-gift"></i> Ưu đãi đặc biệt
                            </div>
                            <ul>
                                <li>Tặng balo laptop cao cấp</li>
                                <li>Tặng chuột không dây chính hãng</li>
                                <li>Giảm giá khi nâng cấp RAM / SSD</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="reviews-section" id="reviews-section">
                <h3 class="reviews-title"></i> Đánh giá sản phẩm</h3>
                <div class="reviews-overview">
                    <div class="reviews-avg">
                        <div class="avg-score"><?= number_format($reviewStats['avg_rating'], 1) ?></div>
                        <div class="avg-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= floor($reviewStats['avg_rating'])): ?>
                                    <i class="fa fa-star"></i>
                                <?php elseif ($i - 0.5 <= $reviewStats['avg_rating']): ?>
                                    <i class="fa fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="avg-count"><?= (int)$reviewStats['total_reviews'] ?> đánh giá</div>
                    </div>
                    <div class="reviews-bars">
                        <?php for ($star = 5; $star >= 1; $star--): ?>
                            <?php
                            $count = $ratingDistribution[$star];
                            $percent = $reviewStats['total_reviews'] > 0 ? ($count / $reviewStats['total_reviews']) * 100 : 0;
                            ?>
                            <div class="bar-row">
                                <span class="bar-label"><?= $star ?> <i class="fa fa-star"></i></span>
                                <div class="bar-track">
                                    <div class="bar-fill" style="width: <?= $percent ?>%"></div>
                                </div>
                                <span class="bar-count"><?= $count ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <?php if (isLoggedIn()): ?>
                    <?php if (!$userHasReviewed): ?>
                        <div class="review-form-wrap" id="review-form-wrap">
                            <h4>Viết đánh giá của bạn</h4>
                            <form id="review-form">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <div class="star-rating-input">
                                    <span>Đánh giá: </span>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="far fa-star star-input" data-value="<?= $i ?>"></i>
                                    <?php endfor; ?>
                                    <input type="hidden" name="rating" id="rating-value" value="0">
                                </div>
                                <textarea name="content" id="review-content" rows="4"
                                    placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này..."></textarea>
                                <button type="submit" class="btn-submit-review">
                                    <i class="fa fa-paper-plane"></i> Gửi đánh giá
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="review-notice">
                            <i class="fa fa-check-circle"></i> Bạn đã đánh giá sản phẩm này.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="review-login-notice">
                        <a href="<?= FRONT_URL ?>/auth/login.php">Đăng nhập</a> để viết đánh giá sản phẩm.
                    </div>
                <?php endif; ?>

                <div class="reviews-list" id="reviews-list">
                    <?php if (empty($reviews)): ?>
                        <div class="no-reviews" id="no-reviews">
                            <i class="far fa-comment-dots"></i>
                            <p>Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-item" data-review-id="<?= $review['id'] ?>">
                                <div class="review-header">
                                    <div class="review-user">
                                        <div class="user-avatar"><?= mb_strtoupper(mb_substr($review['user_name'], 0, 1)) ?></div>
                                        <div>
                                            <strong class="user-name"><?= e($review['user_name']) ?></strong>
                                            <div class="review-stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fa<?= $i <= $review['rating'] ? 's' : 'r' ?> fa-star"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="review-date"><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></span>
                                </div>
                                <div class="review-content"><?= nl2br(e($review['content'])) ?></div>

                                <div class="comments-section">
                                    <button type="button" class="btn-toggle-comments" data-review-id="<?= $review['id'] ?>">
                                        <i class="far fa-comment"></i>
                                        <span class="comment-count">
                                            <?php $cnt = isset($comments[$review['id']]) ? count($comments[$review['id']]) : 0; ?>
                                            <?= $cnt > 0 ? $cnt . ' bình luận' : 'Bình luận' ?>
                                        </span>
                                    </button>
                                    <div class="comments-wrap" id="comments-<?= $review['id'] ?>" style="display:none;">
                                        <div class="comments-list">
                                            <?php if (isset($comments[$review['id']])): ?>
                                                <?php foreach ($comments[$review['id']] as $cmt): ?>
                                                    <div class="comment-item <?= $cmt['parent_id'] ? 'comment-reply' : '' ?>" data-comment-id="<?= $cmt['id'] ?>">
                                                        <div class="comment-header">
                                                            <div class="comment-user">
                                                                <div class="user-avatar small"><?= mb_strtoupper(mb_substr($cmt['user_name'], 0, 1)) ?></div>
                                                                <strong><?= e($cmt['user_name']) ?></strong>
                                                            </div>
                                                            <span class="comment-date"><?= date('d/m/Y H:i', strtotime($cmt['created_at'])) ?></span>
                                                        </div>
                                                        <div class="comment-content"><?= nl2br(e($cmt['content'])) ?></div>
                                                        <?php if (isLoggedIn()): ?>
                                                            <button class="btn-reply" data-review-id="<?= $review['id'] ?>" data-parent-id="<?= $cmt['id'] ?>" data-user-name="<?= e($cmt['user_name']) ?>">
                                                                <i class="fa fa-reply"></i> Trả lời
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (isLoggedIn()): ?>
                                            <form method="post" class="comment-form" data-review-id="<?= $review['id'] ?>">
                                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                <input type="hidden" name="parent_id" value="0" class="comment-parent-id">
                                                <div class="comment-input-wrap">
                                                    <input type="text" name="content" placeholder="Viết bình luận..." required class="comment-input">
                                                    <button type="submit" class="btn-send-comment"><i class="fa fa-paper-plane"></i></button>
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if ($reviewStats['total_reviews'] > 5): ?>
                    <div class="load-more-reviews" id="load-more-reviews">
                        <button class="btn-load-more" data-page="2" data-product-id="<?= $product['id'] ?>">
                            Xem thêm đánh giá <i class="fa fa-chevron-down"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include dirname(__DIR__) . "/includes/footer.php"; ?>
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?= FRONT_URL ?>/js/reviews.js?v=<?= filemtime(dirname(__DIR__) . '/js/reviews.js') ?>"></script>
</body>

</html>