<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/footer.css">
<footer class="footer">
    <div class="footer-container container">
        <div class="footer-col">
            <div class="footer-logo">
                <span class="logo-icon">💻</span>
                <span class="logo-text">TechStore</span>
            </div>
            <p class="footer-desc">
                Địa chỉ tin cậy cho những tín đồ công nghệ.
                Chúng tôi mang đến những sản phẩm laptop chất lượng nhất.
            </p>
        </div>

        <div class="footer-col">
            <h4>Sản phẩm</h4>
            <ul>
                <li><a href="{{ $frontUrl }}/product/list.php">Laptops</a></li>
                <li><a href="{{ $frontUrl }}/product/list.php">Phụ kiện</a></li>
                <li><a href="{{ $frontUrl }}/product/list.php">Máy cũ giá rẻ</a></li>
                <li><a href="{{ $frontUrl }}/product/list.php">Xả kho</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Hỗ trợ</h4>
            <ul>
                <li><a href="#">Trung tâm bảo hành</a></li>
                <li><a href="#">Hướng dẫn mua hàng</a></li>
                <li><a href="#">Chính sách đổi trả</a></li>
                <li><a href="#">Liên hệ</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Đăng ký nhận tin</h4>
            <form class="newsletter-form" method="post" action="#">
                <input type="email" name="email" placeholder="Email của bạn" required>
                <button type="submit">
                    <svg width="18" height="18" viewBox="0 0 24 24">
                        <path fill="white" d="M2 21L23 12L2 3V10L17 12L2 14V21Z" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</footer>