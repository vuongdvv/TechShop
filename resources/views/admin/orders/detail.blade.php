@extends('admin.layout')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@push('styles')
<link rel="stylesheet" href="{{ $frontUrl }}/admin/asset/OrdersDetail.css">
@endpush

@section('content')
<div class="admin-wrapper">
    @include('admin.partials.sidebar')
    <div class="admin-container">
        <div class="breadcrumbs">
            <a href="{{ $frontUrl }}/admin/dashboard.php">Dashboard</a> >
            <a href="{{ $frontUrl }}/admin/orders/index.php">Đơn hàng</a> >
            <span>Chi tiết đơn hàng</span>
        </div>

        <h2 class="page-title">Chi tiết đơn hàng #{{ $order->id }}</h2>

        <div class="box">
            <div class="info-row"><strong>Khách hàng:</strong> {{ e($order->customer_name) }}</div>
            <div class="info-row"><strong>SĐT:</strong> {{ e($order->customer_phone) }}</div>
            <div class="info-row"><strong>Địa chỉ:</strong> {{ e($order->customer_address . ', ' . ($order->city ?? '')) }}</div>
            <div class="info-row"><strong>Ngày tạo:</strong> {{ $order->created_at }}</div>
            <div class="info-row"><strong>Thanh toán:</strong> {{ strtoupper(e($order->payment_method)) }}</div>

            @php
            $statusMap = [
            'pending' => ['status-gray', 'Chờ xử lý'],
            'paid' => ['status-green', 'Đã thanh toán'],
            'shipped' => ['status-orange', 'Đang giao'],
            'completed' => ['status-blue', 'Hoàn tất'],
            'cancel' => ['status-red', 'Đã huỷ đơn'],
            ];
            [$class, $text] = $statusMap[$order->status] ?? ['status-gray', 'Chờ xử lý'];
            @endphp
            <div class="info-row"><strong>Trạng thái:</strong> <span class="status-badge {{ $class }}">{{ $text }}</span></div>
        </div>

        <div class="box">
            <table>
                <thead>
                    <tr>
                        <th class="product">Sản phẩm</th>
                        <th class="price">Giá</th>
                        <th class="quantity">Số lượng</th>
                        <th class="subtotal">Tạm tính</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td class="product">
                            <img src="{{ !empty($item->image_url) ? $frontUrl . '/assets/images/products/' . $item->image_url : $frontUrl . '/assets/images/products/no-image.png' }}">
                            {{ e($item->name) }}
                        </td>
                        <td>{{ number_format($item->price, 0, ',', '.') }} ₫</td>
                        <td class="quantity">{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }} ₫</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total">Tổng tiền: {{ number_format($order->total_price, 0, ',', '.') }} ₫</div>
        </div>

        <div class="box">
            <form action="{{ $frontUrl }}/admin/orders/update_status.php" method="post">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                @php
                $labels = [
                'pending' => 'Đơn mới tạo',
                'paid' => 'Đã thanh toán',
                'shipped' => 'Đang giao',
                'completed' => 'Hoàn thành',
                'cancel' => 'Đã huỷ đơn hàng',
                ];
                $current = $order->status;
                @endphp
                <select name="status">
                    @foreach($labels as $value => $label)
                    @php
                    $disabled = '';
                    $allowed = $allowedNextStatus[$current] ?? [];
                    if ($value !== $current && !in_array($value, $allowed, true)) {
                    $disabled = 'disabled';
                    }
                    @endphp
                    <option value="{{ $value }}" {{ $value === $current ? 'selected' : '' }} {{ $disabled }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-save">Cập nhật</button>
            </form>
        </div>
    </div>
</div>
@endsection