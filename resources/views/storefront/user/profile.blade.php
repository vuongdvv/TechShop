@extends('storefront.layout')

@section('title', 'Thông tin cá nhân - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/profile.css">
@endpush

@section('content')
<main>
    <div class="main-container">
        <h2>Thông tin cá nhân</h2>

        <form method="POST" action="{{ $frontUrl }}/user/profile.php" autocomplete="off">
            @csrf
            <div class="form-group">
                <label>Họ tên</label>
                <input type="text" name="full_name" value="{{ old('full_name', $variant->full_name ?? '') }}">
            </div>

            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" name="phone" value="{{ old('phone', $variant->phone ?? '') }}">
            </div>

            <div class="form-group">
                <label>Tỉnh/Thành phố</label>
                <input type="text" name="city" value="{{ old('city', $variant->city ?? '') }}">
            </div>

            <div class="form-group">
                <label>Địa chỉ chi tiết</label>
                <textarea name="address_detail">{{ old('address_detail', $variant->address_detail ?? '') }}</textarea>
            </div>

            @if(session('error'))
            <p class="error">{{ session('error') }}</p>
            @endif

            <button type="submit" class="btn-submit">Cập nhật</button>
            <a class="back" href="{{ $frontUrl }}/home.php"><span>Quay lại</span></a>
        </form>

        @if(request()->query('success'))
        <div id="toast" class="toast-success">Cập nhật thông tin thành công!</div>
        @endif
    </div>
</main>
@endsection

@push('scripts')
<script>
    setTimeout(function() {
        const toast = document.getElementById('toast');
        if (toast) toast.remove();
        if (window.location.search.includes('success')) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }, 3000);
</script>
@endpush