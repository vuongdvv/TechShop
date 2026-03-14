<?php
session_start();
require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/includes/functions.php";
require_once dirname(__DIR__, 3) . "/back-end/admin/customer/customer.php";

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý khách hàng</title>
    <link rel="stylesheet" href="<?= FRONT_URL ?>/admin/asset/customer.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/admin/asset/product.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">



</head>

<body>
    <div class="admin-wraper">
        <!-- SIDEBAR -->
        <?php include dirname(__DIR__) . "/sidebar.php"; ?>

        <!-- MAIN -->
        <div class="admin-container">

            <div class="page-header">
                <div>
                    <h2>Quản lý khách hàng</h2>
                    <p>Quản lý và theo dõi thông tin khách hàng</p>
                </div>

            </div>

            <!-- CARDS -->
            <div class="stats-grid">
                <div class="card">
                    <p>Tổng khách hàng</p>
                    <h3><?= $totalCustomers ?></h3>

                </div>

                <div class="card">
                    <p>Khách mới tháng này</p>
                    <h3><?= $newCustomers ?></h3>

                </div>

                <div class="card">
                    <p>Người dùng hoạt động</p>
                    <h3><?= $activeUsers ?></h3>

                </div>
            </div>

            <!-- TABLE -->
            <div class="table-wrapper">

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Khách hàng</th>
                            <th>Email</th>
                            <th>SĐT</th>
                            <th>Đơn hàng</th>
                            <th>Tổng chi tiêu</th>
                            <th>Trạng thái</th>
                            <th>Vai trò</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = $customers->fetch_assoc()): ?>
                            <tr data-user-id="<?= $row['id'] ?>">
                                <td><?= $row['full_name'] ?></td>
                                <td><?= $row['email'] ?></td>
                                <td><?= $row['phone'] ?></td>
                                <td><?= $row['total_orders'] ?></td>
                                <td><?= number_format($row['total_spent'], 0, ',', '.') ?> đ</td>
                                <td>
                                    <?php if ($row['status'] == '1'): ?>
                                        <span class="status active-status">Hoạt động</span>
                                    <?php else: ?>
                                        <span class="status inactive-status">Ngừng</span>
                                    <?php endif; ?>
                                </td>
                                <td class="user-role"><?= $row['role'] ?></td>

                                <td>
                                    <button class="btn btn-edit" onclick="openEditUserModal(<?= $row['id'] ?>)">
                                        Sửa
                                    </button>

                                    <a href="?delete=<?= $row['id'] ?>"
                                        onclick="return confirm('Bạn có chắc muốn xóa khách hàng này?')"
                                        class="btn-delete">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>

                </table>
            </div>
            <?php if (isset($_GET['success'])): ?> <div id="toast" class="toast-success">
                    ✔ Xóa tài khoản thành công </div>
            <?php endif; ?>
        </div>

    </div>



    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditUserModal()">&times;</span>

            <h2>Sửa thông tin người dùng</h2>

            <form id="editUserForm">
                <input type="hidden" name="id" id="edit_user_id">

                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="status" id="edit_user_status">
                        <option value="1">Hoạt động</option>
                        <option value="0">Ngừng</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="edit_user_role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary-save">Cập nhật</button>
            </form>
        </div>
    </div>
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const FRONT_URL = '<?= FRONT_URL ?>';
    </script>
    <script src="<?= FRONT_URL ?>/js/customers.js"></script>
</body>


</html>