# 💻 TechShop - Website Bán Laptop

Website thương mại điện tử chuyên bán laptop chính hãng, được xây dựng bằng PHP thuần với kiến trúc tách biệt Front-end / Back-end.


## 🛠️ Công nghệ sử dụng

- **Back-end:** PHP 8.x
- **Database:** MySQL (mysqli)
- **Front-end:** HTML, CSS, JavaScript
- **Web Server:** Apache (XAMPP)
- **Thanh toán:** VNPay Sandbox
- **Icons:** Font Awesome 6

## 📁 Cấu trúc dự án

```
lapshop/
├── .htaccess                 # URL rewrite rules
├── config/
│   ├── config.php            # BASE_URL, FRONT_URL, SITE_NAME
│   ├── database.php          # Kết nối MySQL
│   └── vnpay.php             # Cấu hình VNPay
│
├── back-end/                 # Xử lý logic nghiệp vụ
│   ├── home.php              # Query sản phẩm trang chủ
│   ├── admin/                # Quản trị
│   │   ├── admin_auth/       # Đăng nhập/đăng ký admin
│   │   ├── products/         # CRUD sản phẩm
│   │   ├── categories/       # CRUD thương hiệu/danh mục
│   │   ├── customer/         # Quản lý khách hàng
│   │   └── orders/           # Quản lý đơn hàng
│   ├── auth/                 # Đăng nhập/đăng ký/đăng xuất user
│   ├── cart/                 # Giỏ hàng (thêm, sửa, xóa, thanh toán)
│   ├── orders/               # Đơn hàng (xem, chi tiết, hủy)
│   ├── payment/              # Thanh toán VNPay
│   ├── product/              # Query danh sách & chi tiết sản phẩm
│   └── user/                 # Thông tin cá nhân
│
└── front-end/                # Giao diện người dùng
    ├── home.php              # Trang chủ
    ├── admin/                # Giao diện quản trị
    │   ├── dashboard.php     # Bảng điều khiển
    │   ├── sidebar.php       # Menu bên
    │   ├── admin_auth/       # Form đăng nhập admin
    │   ├── products/         # Quản lý sản phẩm (thêm, sửa, danh sách)
    │   ├── categories/       # Quản lý thương hiệu
    │   ├── customers/        # Quản lý khách hàng
    │   └── orders/           # Quản lý đơn hàng
    ├── assets/               # CSS, hình ảnh
    ├── auth/                 # Form đăng nhập/đăng ký
    ├── cart/                 # Giỏ hàng, thanh toán, thành công
    ├── includes/             # Header, footer, functions
    ├── js/                   # JavaScript files
    ├── orders/               # Đơn hàng của tôi
    ├── product/              # Danh sách & chi tiết sản phẩm
    └── user/                 # Hồ sơ cá nhân
```

## ✨ Tính năng

### 👤 Người dùng

- Đăng ký / Đăng nhập / Đăng xuất
- Xem danh sách sản phẩm, lọc theo thương hiệu & danh mục
- Tìm kiếm sản phẩm
- Xem chi tiết sản phẩm (cấu hình, giá, ảnh)
- Thêm vào giỏ hàng / Mua ngay
- Quản lý giỏ hàng (cập nhật số lượng, xóa)
- Thanh toán (COD / VNPay)
- Xem đơn hàng & chi tiết đơn hàng
- Hủy đơn hàng
- Cập nhật thông tin cá nhân

### 🔧 Quản trị viên

- Dashboard tổng quan
- Quản lý sản phẩm (CRUD + upload ảnh)
- Quản lý thương hiệu & danh mục
- Quản lý đơn hàng (cập nhật trạng thái)
- Quản lý khách hàng

## ⚙️ Cài đặt

### Yêu cầu

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8.x)

### Các bước

1. **Clone dự án**

   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/vuongdvv/TechShop.git lapshop
   ```

2. **Tạo database**
   - Mở phpMyAdmin: http://localhost/phpmyadmin
   - Tạo database tên `laptopshop` (utf8mb4_general_ci)
   - Import file SQL (nếu có)

3. **Cấu hình**
   - File `config/database.php`: cập nhật thông tin kết nối nếu cần
   - File `config/config.php`: cập nhật `BASE_URL` nếu đổi tên thư mục
   - File `config/vnpay.php `: thông tin vnpaysandbox

4. **Bật Apache & MySQL** trong XAMPP Control Panel

5. **Truy cập**
   - Website: http://localhost/lapshop
   - Admin: http://localhost/lapshop/admin/dashboard.php

## 🔒 Bảo mật

- Prepared Statements chống SQL Injection
- CSRF Token cho các form
- Session-based authentication
- `htmlspecialchars()` chống XSS
- Phân quyền Admin / User

## 👨‍💻 Tác giả

- **Dương Văn Vương**
- GitHub: [@vuongdvv](https://github.com/vuongdvv)
