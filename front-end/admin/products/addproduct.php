<?php
session_start();
require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/includes/functions.php";
require_once dirname(__DIR__, 3) . "/back-end/admin/products/addproduct.php";

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="<?= FRONT_URL ?>/admin/asset/product.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/admin/asset/addproduct.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="admin-wrapper">

        <!-- ================= SIDEBAR ================= -->
        <?php include dirname(__DIR__) . "/sidebar.php"; ?>
        <div class="admin-container">

            <div class="breadcrumbs">
                <a href="<?= FRONT_URL ?>/admin/products/product.php">Sản phẩm</a> >
                <span>Thêm sản phẩm mới</span>
            </div>
            <div class="top-bar">
                <h1>Thêm sản phẩm mới</h1>
                <div>
                    <a href="product.php" class="btn-cancel">Hủy</a>
                    <button form="productForm" type="submit" class="btn-save">Lưu sản phẩm</button>
                </div>
            </div>

            <form method="POST" action="" enctype="multipart/form-data"
                id="productForm" class="grid-layout" autocomplete="off">

                <!-- LEFT COLUMN -->
                <div class="left-column">

                    <!-- 1. Thông tin chung -->
                    <div class="card">
                        <h3>1. Thông tin chung</h3>

                        <label>Tên sản phẩm </label>
                        <input type="text" name="name" required>

                        <label>Slug sản phẩm</label>
                        <input type="text" name="slug">

                        <div class="row-2">
                            <div>
                                <label>Thương hiệu</label>
                                <select name="brand_id" required>
                                    <option value="">Chọn thương hiệu</option>
                                    <?php while ($brand = $brandResult->fetch_assoc()): ?>
                                        <option value="<?= $brand['id'] ?>">
                                            <?= $brand['name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div>
                                <label>Danh mục</label>
                                <select name="category_id" required>
                                    <option value="">Chọn danh mục</option>
                                    <?php while ($cat = $categoryResult->fetch_assoc()): ?>
                                        <option value="<?= $cat['id'] ?>">
                                            <?= $cat['name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>


                    </div>

                    <!-- 3. Thông số kỹ thuật -->
                    <div class="card">
                        <h3>3. Thông số kỹ thuật</h3>

                        <div class="row-2">
                            <input type="text" name="cpu" placeholder="CPU">
                            <input type="text" name="ram" placeholder="RAM">
                        </div>

                        <div class="row-2">
                            <input type="text" name="ssd" placeholder="SSD">
                            <input type="text" name="gpu" placeholder="GPU">
                        </div>

                        <div class="row-2">
                            <input type="text" name="screen" placeholder="Màn hình">
                            <input type="text" name="pin" placeholder="Pin">
                        </div>

                        <div class="row-2">
                            <input type="text" name="he_dieu_hanh" placeholder="Hệ điều hành">
                            <input type="text" name="kich_thuoc" placeholder="Kích thước">
                        </div>
                    </div>

                    <!-- 4. Mô tả -->
                    <div class="card">
                        <h3>4. Mô tả sản phẩm</h3>
                        <textarea name="description" rows="6"
                            placeholder="Nhập mô tả chi tiết sản phẩm..."></textarea>
                    </div>

                </div>


                <!-- RIGHT COLUMN -->
                <div class="right-column">

                    <!-- 2. Giá & Kho -->
                    <div class="card">
                        <h3>2. Giá & Kho hàng</h3>

                        <label>Giá gốc (VND)</label>
                        <input type="number" name="price" min="0">

                        <label>Giá khuyến mãi (VND)</label>
                        <input type="number" name="sale_price" min="0">

                        <label>Số lượng</label>
                        <input type="number" name="stock" min="0" value="">

                    </div>


                    <div class="card">
                        <h3>5. Hình ảnh</h3>

                        <div class="upload-box" id="uploadBox">
                            <input type="file" name="image" id="imageInput" accept="image/*" hidden>

                            <div class="upload-content" id="uploadContent">
                                <i class="fa fa-cloud-upload-alt upload-icon"></i>
                                <p class="upload-text">Kéo thả ảnh vào đây</p>
                                <span>Hỗ trợ JPG, PNG, WEBP (Max 5MB)</span>
                            </div>
                            <div style="position:relative; display:inline-block;">
                                <img id="previewImage" src="<?= FRONT_URL ?>/assets/images/products/<?= $image['image_url'] ?? 'no-image.png' ?>"
                                    style="width:150px; border:1px solid #ccc; padding:5px;">
                                <span id="removeImage" class="removeImage"> &times; </span>
                            </div>


                        </div>
                    </div>
                    <div class="card">
                        <h3>6. Đánh giá sản phẩm</h3>
                        <div class="rating-box">
                            <label>Đánh giá ★ (rating)</label>
                            <div class="rating-input">
                                <input type="number" name="rating" min="0" max="5" step="0.1" placeholder="VD: 4.5">

                            </div>
                        </div>
                    </div>
                    <!-- Trạng thái -->
                    <div class="card status-card">
                        <h3>Trạng thái</h3>

                        <label class="status-option">
                            <input type="radio" name="status" value="1" checked>
                            <span>Công khai <small>(Hiển thị ngay)</small></span>
                        </label>

                        <label class="status-option">
                            <input type="radio" name="status" value="0">
                            <span>Bản nháp</span>
                        </label>
                    </div>


                </div>
                <?php if (isset($_GET['success'])): ?> <div id="toast" class="toast-success">
                        ✔ Lưu sản phẩm thành công </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <script>
        const defaultImage = "<?= FRONT_URL ?>/assets/images/products/<?= $image['image_url'] ?? 'no-image.png' ?>";
    </script>
    <script src="<?= FRONT_URL ?>/js/admin_addproduct.js"></script>
</body>



</html>