<link href="{{ $frontUrl }}/assets/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="{{ $frontUrl }}/assets/css/navbar.css" rel="stylesheet">
<link href="{{ $frontUrl }}/assets/css/style.css" rel="stylesheet">

@if(session('toast_success'))
<div id="toast-success">{{ session('toast_success') }}</div>
@endif

<header class="site-header">
    <div class="container header-inner d-flex align-items-center justify-content-between">
        <a href="{{ $frontUrl }}/home.php" class="logo d-flex align-items-center">
            <span class="logo-icon me-1">💻</span>
            <strong>TechStore</strong>
        </a>

        <nav class="menu d-none d-md-flex align-items-center">
            <a href="{{ $frontUrl }}/home.php" class="{{ request()->is('home.php') ? 'active' : '' }}">Trang chủ</a>
            <a href="{{ $frontUrl }}/product/list.php" class="{{ request()->is('product/*') ? 'active' : '' }}">Sản phẩm</a>
            <a href="#">Hỗ trợ</a>
            <a href="{{ $frontUrl }}/orders/index.php" class="{{ request()->is('orders/*') ? 'active' : '' }}">Đơn hàng</a>
        </nav>

        <div class="header-right d-flex align-items-center gap-3">
            <form action="{{ $frontUrl }}/product/list.php" method="get" class="search-form">
                <div class="search-box d-flex align-items-center">
                    <i class="fa fa-search"></i>
                    <input type="text" name="keyword" placeholder="Tìm kiếm..." value="{{ request('keyword', '') }}">
                </div>
            </form>

            <a href="{{ $frontUrl }}/cart/index.php" class="icon-btn position-relative">
                <i class="fa fa-shopping-cart"></i>
                @if($cartCount > 0)
                <span class="cart-badge">{{ $cartCount }}</span>
                @endif
            </a>

            @if($sessionUserId)
            <div class="user-info d-flex align-items-center gap-2">
                <a href="{{ $frontUrl }}/user/profile.php">
                    <span class="hello-text">Xin chào, {{ $sessionUserName }}</span>
                </a>
                <a href="{{ $frontUrl }}/auth/logout.php" class="icon-btn">
                    <i class="fa fa-sign-out-alt"></i>
                </a>
            </div>
            @else
            <a href="{{ $frontUrl }}/auth/login.php" class="icon-btn">
                <i class="fa fa-user"></i>
            </a>
            @endif
        </div>
    </div>
</header>

<script>
    const BASE_URL = '{{ $baseUrl }}';
    const FRONT_URL = '{{ $frontUrl }}';
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

    setTimeout(() => {
        const toast = document.getElementById('toast-success');
        if (toast) toast.remove();
    }, 3000);

    function addToCart(productId) {
        fetch(FRONT_URL + '/cart/add.php?id=' + productId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    if (data.message === 'Chua dang nhap') {
                        window.location.href = FRONT_URL + '/auth/login.php';
                    }
                    return;
                }

                const badge = document.querySelector('.cart-badge');
                if (badge) {
                    badge.textContent = data.cartCount;
                } else {
                    const cartLink = document.querySelector('.icon-btn.position-relative');
                    if (cartLink) {
                        const span = document.createElement('span');
                        span.className = 'cart-badge';
                        span.textContent = data.cartCount;
                        cartLink.appendChild(span);
                    }
                }

                const toast = document.createElement('div');
                toast.id = 'toast-success';
                toast.textContent = data.message;
                document.body.prepend(toast);
                setTimeout(() => toast.remove(), 3000);
            });
    }

    document.addEventListener('click', (event) => {
        const btn = event.target.closest('.js-add-to-cart');
        if (!btn) {
            return;
        }

        event.preventDefault();
        const productId = parseInt(btn.dataset.productId || '0', 10);
        if (productId > 0) {
            addToCart(productId);
        }
    });
</script>