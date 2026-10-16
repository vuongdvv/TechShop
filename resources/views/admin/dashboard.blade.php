@extends('admin.layout')

@section('title', 'TechShop Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ $frontUrl }}/admin/asset/Dashboard.css">
@endpush

@section('content')
<div class="admin-wrapper">
    @include('admin.partials.sidebar')

    <div class="main">
        <div class="topbar">
            <h1>Xin chào, {{ $adminName }}</h1>
        </div>

        <div class="dashboard">
            <div class="card" onclick="window.location.href='{{ $frontUrl }}/admin/orders/index.php'">
                <i class="fa fa-receipt"></i>
                <h3>Tổng đơn hàng</h3>
                <div class="value">{{ $totalOrders }}</div>
            </div>

            <div class="card">
                <i class="fa fa-money-bill-wave"></i>
                <h3>Doanh thu</h3>
                <div class="value">{{ number_format($totalRevenue, 0, ',', '.') }}₫</div>
            </div>

            <div class="card" onclick="window.location.href='{{ $frontUrl }}/admin/products/product.php'">
                <i class="fa fa-box"></i>
                <h3>Sản phẩm</h3>
                <div class="value">{{ $totalProducts }}</div>
            </div>

            <div class="card" onclick="window.location.href='{{ $frontUrl }}/admin/customers/customer.php'">
                <i class="fa fa-users"></i>
                <h3>Khách hàng</h3>
                <div class="value">{{ $totalUsers }}</div>
            </div>
        </div>

        <div class="dashboard-extended">
            <div class="card-box revenue-card">
                <div class="card-header">
                    <div>
                        <h3>Tổng quan về doanh thu</h3>
                        <p>Doanh thu 7 ngày gần nhất</p>
                    </div>
                </div>
                <canvas id="revenueChart" height="100"></canvas>
            </div>

            <div class="card-box top-card">
                <h3>Sản phẩm bán chạy nhất</h3>
                <p class="sub">Laptop phổ biến nhất trong tuần</p>
                @foreach($topProducts as $row)
                <div class="top-item">
                    <div class="product-info">
                        <div class="product-image">
                            @if(!empty($row->image_url))
                            <img src="{{ $frontUrl }}/assets/images/products/{{ $row->image_url }}" alt="">
                            @else
                            <img src="{{ $frontUrl }}/assets/images/products/no-image.png" alt="">
                            @endif
                        </div>
                        <div>
                            <strong>{{ $row->name }}</strong>
                            <div class="category">{{ $row->category_name ?? 'Chưa phân loại' }}</div>
                        </div>
                    </div>
                    <div class="sales">{{ $row->total_sold }}<span>Bán ra</span></div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card-box recent-card">
            <div class="card-header">
                <div>
                    <h3>Đơn đặt hàng gần đây</h3>
                    <p>Danh sách khách hàng mua hàng mới nhất</p>
                </div>
            </div>

            <table class="modern-table">
                <thead>
                    <tr>
                        <th>MÃ ĐƠN HÀNG</th>
                        <th>KHÁCH HÀNG</th>
                        <th>SẢN PHẨM</th>
                        <th>GIÁ</th>
                        <th>TRẠNG THÁI</th>
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
                    @foreach($recentOrders as $order)
                    <tr>
                        <td>#ORD-{{ $order->id }}</td>
                        <td>{{ $order->customer }}</td>
                        <td>{{ $order->product_name }}</td>
                        <td>{{ number_format($order->total_price, 0, ',', '.') }}₫</td>
                        <td>
                            <span class="status-badge {{ strtolower($order->display_status) }}">
                                {{ $statusLabels[$order->display_status] ?? 'Không xác định' }}
                            </span>
                        </td>
                        <td><a href="{{ $frontUrl }}/admin/orders/detail.php?id={{ $order->id }}" class="btn-view">Xem</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const revenueData = @json($revenueByDay);
    const ctx = document.getElementById('revenueChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Doanh thu',
                data: revenueData,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + '₫';
                        }
                    }
                }
            }
        }
    });
</script>
@endpush