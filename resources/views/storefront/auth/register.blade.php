@extends('storefront.layout')

@section('title', 'Đăng ký - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/register.css">
@endpush

@section('content')
<main>
    <div class="register-page">
        <div class="register-box">
            <h2>Đăng ký</h2>
            <p class="register-desc">Tạo tài khoản mới tại {{ $siteName }}</p>

            @if(session('error'))
            <div class="alert error">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ $frontUrl }}/auth/register.php" autocomplete="off">
                @csrf
                <div class="form-group">
                    <label>Họ và tên</label>
                    <input type="text" name="full_name" required placeholder="Nguyễn Văn A" value="{{ old('full_name') }}">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="example@gmail.com" value="{{ old('email') }}">
                </div>

                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" required placeholder="0123456789" value="{{ old('phone') }}">
                </div>

                <div class="form-group">
                    <label>Mật khẩu</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" required placeholder="••••••••">
                        <i class="fa-regular fa-eye-slash toggle-password" data-target="password"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>Xác nhận mật khẩu</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm" id="confirm" required placeholder="••••••••">
                        <i class="fa-regular fa-eye-slash toggle-password" data-target="confirm"></i>
                    </div>
                </div>

                <button type="submit" class="btn-register">Đăng ký</button>
            </form>

            <div class="register-footer">
                <p>Đã có tài khoản?</p>
                <a href="{{ $frontUrl }}/auth/login.php">Đăng nhập</a>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', function() {
            const input = document.getElementById(this.dataset.target);
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            }
        });
    });
</script>
@endpush