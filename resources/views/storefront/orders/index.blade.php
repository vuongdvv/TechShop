@extends('storefront.layout')

@section('title', 'Đơn hàng của tôi - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/style.css">
<link rel="stylesheet" href="{{ $frontUrl }}/assets/css/cart.css">
<style>
    .status-pending {
        color: #6c757d;
        font-weight: 600;
    }

    .status-paid {
        color: #28a745;
        font-weight: 600;
    }

    .status-shipped {
        color: #fd7e14;
        font-weight: 600;
    }

    .status-completed {
        color: #007bff;
        font-weight: 600;
    }

    .status-cancel {
        color: #dc3545;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<main>
    <div class="container">
        <div class="breadcrumbs">
            <a href="{{ $frontUrl }}/home.php">Trang Chủ</a> >
            <span>Đơn hàng của tôi</span>
        </div>
        <h2 class="page-title">📦 Đơn hàng của tôi</h2>

        @if($orders->isEmpty())
        <p>Bạn chưa có đơn hàng nào.</p>
        @else
        <table class="cart-table">
            <thead>
                <tr>
                    <td data-label="Mã đơn">Mã đơn</td>
                    <td data-label="Ngày đặt">Ngày đặt</td>
                    <td data-label="Tổng tiền">Tổng tiền</td>
                    <td data-label="Trạng thái">Trạng thái</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                @php
                $statusMap = [
                'pending' => ['Chờ xử lý', 'status-pending'],
                'paid' => ['Đã thanh toán', 'status-paid'],
                'shipped' => ['Đang giao', 'status-shipped'],
                'completed' => ['Hoàn tất', 'status-completed'],
                'cancel' => ['Đã hủy', 'status-cancel'],
                ];
                [$statusText, $statusClass] = $statusMap[$order->status] ?? ['Chờ xử lý', 'status-pending'];
                @endphp
                <tr>
                    <td>#{{ $order->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ number_format((float) $order->total_price, 0, ',', '.') }} ₫</td>
                    <td><span class="{{ $statusClass }}">{{ $statusText }}</span></td>
                    <td>
                        <a href="{{ $frontUrl . '/orders/detail.php?id=' . $order->id }}" class="btn-detail">Xem chi tiết</a>
                        @if($order->status === 'pending')
                        <button type="button" class="btn-cancel js-cancel-order" data-order-id="{{ $order->id }}">Hủy đơn hàng</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</main>
@endsection

@push('scripts')
<script>
    const cancelOrderUrl = "{{ $frontUrl }}/orders/cancel_order.php";

    function cancelOrder(orderId) {
        if (!confirm('Bạn có chắc muốn hủy đơn này không?')) return;

        fetch(cancelOrderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: 'order_id=' + orderId
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Hủy đơn thành công');
                    location.reload();
                } else {
                    alert(data.message || 'Không thể hủy đơn');
                }
            })
            .catch(() => alert('Có lỗi xảy ra'));
    }

    document.querySelectorAll('.js-cancel-order').forEach((button) => {
        button.addEventListener('click', () => {
            cancelOrder(parseInt(button.dataset.orderId || '0', 10));
        });
    });
</script>
@endpush