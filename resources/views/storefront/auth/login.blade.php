@extends('storefront.layout')

@section('title', 'Đăng nhập - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/login.css">
@endpush

@section('content')
<main>
    <div class="login-page">
        <div class="login-box">
            <h2>Đăng nhập</h2>
            <p class="login-desc">Chào mừng bạn quay lại {{ $siteName }}</p>

            @if(session('error'))
            <div class="alert">{{ session('error') }}</div>
            @endif

            <form method="post" action="{{ $frontUrl }}/auth/login.php">
                @csrf
                <div class="form-group">
                    <label>Email/Username</label>
                    <input type="text" name="email" required placeholder="user@gmail.com" value="{{ old('email') }}">
                </div>

                <div class="form-group">
                    <label>Mật khẩu</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" required placeholder="••••••••">
                        <i class="fa-regular fa-eye-slash toggle-password" onclick="togglePassword()"></i>
                    </div>
                </div>

                <button type="submit" class="btn-login">Đăng nhập</button>
            </form>

            <div class="login-footer">
                <a href="{{ $frontUrl }}/home.php">← Về trang chủ</a>
                <a href="{{ $frontUrl }}/auth/register.php" class="register-link">Đăng ký</a>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.querySelector('.toggle-password');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }
</script>
@endpush