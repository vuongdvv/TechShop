<?php
require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/includes/functions.php";
require_once dirname(__DIR__, 3) . "/back-end/admin/products/editproduct.php";

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa sản phẩm</title>
    <link rel="stylesheet" href="<?= FRONT_URL ?>/admin/asset/product.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/admin/asset/editproduct.css">
</head>

<body>
    <div class="admin-wrapper">
        <?php include_once dirname(__DIR__) . "/sidebar.php"; ?>
        <div class="admin-container">
            <div class="breadcrumbs">
                <a href="<?= FRONT_URL ?>/admin/products/product.php">Quản lý sản phẩm</a> >
                <span>Chỉnh sửa sản phẩm</span>
            </div>

            <div class="top-bar">
                <h1>Chỉnh sửa sản phẩm</h1>
                <div>
                    <a href="product.php" class="btn-cancel">Hủy</a>
                    <button form="product-edit-form" type="submit" class="btn-save">Cập nhật sản phẩm</button>
                </div>
            </div>
            <?php if (!empty($errors)): ?>
                <div style="color:red;">
                    <?php foreach ($errors as $error): ?>
                        <p><?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data"
                id="product-edit-form" class="grid-layout" autocomplete="off">

                <div class="left-column">
                    <div class="card">
                        <h3>1. Thông tin chung</h3>

                        <label for="product-name">Tên sản phẩm</label>
                        <input type="text" id="name" name="name" value="<?= $product['name'] ?? '' ?>">

                        <label for="product-slug">Slug sản phẩm</label>
                        <input type="text" id="slug" name="slug" value="<?= $product['slug'] ?? '' ?>">


                        <div class="row-2">
                            <div>
                                <label>Thương hiệu</label>
                                <select name="brand_id">
                                    <?php while ($brand = $brands->fetch_assoc()): ?>
                                        <option value="<?= $brand['id'] ?>"
                                            <?= ($brand['id'] == $product['brand_id']) ? 'selected' : '' ?>>
                                            <?= $brand['name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div>
                                <label>Danh mục</label>
                                <select name="category_id">
                                    <?php while ($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?= $cat['id'] ?>"
                                            <?= ($cat['id'] == $product['category_id']) ? 'selected' : '' ?>>
                                            <?= $cat['name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <h3>3. Thông số kỹ thuật</h3>
                        <div class="row-2">
                            <input type="text" name="cpu" placeholder="CPU" value="<?= $variant['cpu'] ?? '' ?>">
                            <input type="text" name="ram" placeholder="RAM" value="<?= $variant['ram'] ?? '' ?>">
                        </div>

                        <div class="row-2">
                            <input type="text" name="ssd" placeholder="SSD" value="<?= $variant['ssd'] ?? '' ?>">
                            <input type="text" name="gpu" placeholder="GPU" value="<?= $variant['gpu'] ?? '' ?>">
                        </div>

                        <div class="row-2">
                            <input type="text" name="screen" placeholder="Màn hình" value="<?= $variant['screen'] ?? '' ?>">
                            <input type="text" name="pin" placeholder="Pin" value="<?= $variant['pin'] ?? '' ?>">
                        </div>

                        <div class="row-2">
                            <input type="text" name="he_dieu_hanh" placeholder="Hệ điều hành" value="<?= $variant['he_dieu_hanh'] ?? '' ?>">
                            <input type="text" name="kich_thuoc" placeholder="Kích thước" value="<?= $variant['kich_thuoc'] ?? '' ?>">
                        </div>

                    </div>

                    <div class="card">
                        <h4>4. Mô tả sản phẩm</h4>
                        <textarea name="description" rows="6"><?= $product['description'] ?></textarea>
                    </div>

                </div>

                <div class="right-column">
                    <div class="card">
                        <h3>2. Giá & Kho hàng</h3>
                        <div class="row-2">
                            <label>Giá gốc (VNĐ)</label>
                            <input type="number" name="price" value="<?= $product['price'] ?>">

                            <label>Giá khuyến mãi (VNĐ)</label>
                            <input type="number" name="sale_price" value="<?= $product['sale_price'] ?>">

                            <label>Số lượng</label>
                            <input type="number" name="stock" value="<?= $product['stock'] ?>">
                        </div>

                        <div class="card">
                            <h3>5. Hình ảnh</h3>
                            <?php if ($image): ?>
                                <img src="<?= FRONT_URL ?>/assets/images/products/<?= $image['image_url'] ?>" width="150">
                            <?php else: ?>
                                <p>Chưa có ảnh</p>
                            <?php endif; ?>

                            <div class="upload-box" id="uploadBox">
                                <input type="file" name="image" id="imageInput" accept="image/*" hidden>
                                <div class="upload-content" id="uploadContent">
                                    <i class="fa fa-cloud-upload-alt upload-icon"></i>
                                    <p class="upload-text">Kéo thả ảnh vào đây</p>
                                    <span>Hỗ trợ JPG, PNG, WEBP (Max 5MB)</span>
                                </div>
                                <div style="position:relative; display:inline-block;">

                                    <img
                                        id="previewImage"
                                        style="width:150px; border:1px solid #ccc; padding:5px;
                                    <?= empty($image['image_url']) ? 'display:none;' : '' ?>">

                                    <span id="removeImage"
                                        class="removeImage"
                                        style="<?= empty($image['image_url']) ? 'display:none;' : '' ?>">
                                        &times;
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <h3>6. Đánh giá sản phẩm</h3>
                            <div class="rating-box">
                                <label>Đánh giá ★ (rating)</label>

                                <div class="rating-input">
                                    <input
                                        type="number"
                                        name="rating"
                                        min="0"
                                        max="5"
                                        step="0.1"
                                        value="<?= $product['rating'] ?? 0 ?>">

                                    <span class="star">★</span>
                                    <span id="ratingValue"><?= $product['rating'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="card status-card">
                            <h3>7. Trạng thái</h3>
                            <label class="status-option">
                                <input type="radio" name="status" value="1"
                                    <?= $product['status'] == 1 ? 'checked' : '' ?>>
                                <span>Công khai <small>(Hiển thị ngay)</small></span>
                            </label>

                            <label class="status-option">
                                <input type="radio" name="status" value="0"
                                    <?= $product['status'] == 0 ? 'checked' : '' ?>>
                                <span>Ẩn sản phẩm</span>
                            </label>
                        </div>
                    </div>
            </form>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div id="toast" class="toast-success">
            ✔ Cập nhật sản phẩm thành công
        </div>
    <?php endif; ?>

</body>

<script>
    const defaultImage = "<?= FRONT_URL ?>/assets/images/products/<?= $image['image_url'] ?? 'no-image.png' ?>";
</script>
<script src="<?= FRONT_URL ?>/js/admin_editproduct.js"></script>

</html>