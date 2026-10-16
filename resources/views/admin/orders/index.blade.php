@extends('admin.layout')

@section('title', 'Quản lý đơn hàng')

@push('styles')
<link rel="stylesheet" href="{{ $frontUrl }}/admin/asset/OrdersIndex.css">
@endpush

@section('content')
<div class="admin-wrapper">
    @include('admin.partials.sidebar')

    <div class="admin-container">
        <h2 class="page-title">📦 Quản lý đơn hàng</h2>

        @if($orders->isEmpty())
        <p>Chưa có đơn hàng nào.</p>
        @else
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>SĐT</th>
                    <th>Số Sản Phẩm</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @php
                $statusLabels = [
                'pending' => 'Chờ xử lý',
                'paid' => 'Đã thanh toán',
                'shipped' => 'Đang giao',
                'completed' => 'Hoàn thành',
                'cancel' => 'Đã hủy',
                ];
                @endphp
                @foreach($orders as $order)
                <tr>
                    <td>#{{ $order->id }}</td>
                    <td>{{ e($order->customer_name) }}</td>
                    <td>{{ e($order->customer_phone) }}</td>
                    <td>{{ $order->total_items }}</td>
                    <td>{{ number_format($order->total_price, 0, ',', '.') }} ₫</td>
                    <td><span class="order-status status-{{ e($order->display_status) }}">{{ $statusLabels[$order->display_status] ?? 'Không xác định' }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</td>
                    <td><a href="{{ $frontUrl }}/admin/orders/detail.php?id={{ $order->id }}" class="btn-view">Xem</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection