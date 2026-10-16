@extends('admin.layout')

@section('title', 'Quản lý khách hàng')

@push('styles')
<link rel="stylesheet" href="{{ $frontUrl }}/admin/asset/customer.css">
<link rel="stylesheet" href="{{ $frontUrl }}/admin/asset/product.css">
@endpush

@section('content')
<div class="admin-wraper">
    @include('admin.partials.sidebar')

    <div class="admin-container">
        <div class="page-header">
            <div>
                <h2>Quản lý người dùng</h2>
                <p>Quản lý và theo dõi thông tin người dùng</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="card">
                <p>Tổng người dùng</p>
                <h3>{{ $totalCustomers }}</h3>
            </div>
            <div class="card">
                <p>Người dùng mới tháng này</p>
                <h3>{{ $newCustomers }}</h3>
            </div>
            <div class="card">
                <p>Người dùng hoạt động</p>
                <h3>{{ $activeUsers }}</h3>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Khách hàng</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Đơn hàng</th>
                        <th>Tổng chi tiêu</th>
                        <th>Trạng thái</th>
                        <th>Vai trò</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $row)
                    <tr data-user-id="{{ $row->id }}">
                        <td>{{ $row->full_name }}</td>
                        <td>{{ $row->email }}</td>
                        <td>{{ $row->phone }}</td>
                        <td>{{ $row->total_orders }}</td>
                        <td>{{ number_format($row->total_spent, 0, ',', '.') }} đ</td>
                        <td>
                            @if((int)$row->status === 1)
                            <span class="status active-status">Hoạt động</span>
                            @else
                            <span class="status inactive-status">Ngừng</span>
                            @endif
                        </td>
                        <td class="user-role">{{ $row->role }}</td>
                        <td>
                            <button class="btn btn-edit" data-user-id="{{ $row->id }}" onclick="openEditUserModal(this.dataset.userId)">Sửa</button>
                            <a href="{{ $frontUrl }}/admin/customers/customer.php?delete={{ $row->id }}" onclick="return confirm('Bạn có chắc muốn xóa khách hàng này?')" class="btn-delete"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(request()->query('success'))
        <div id="toast" class="toast-success">✔ Xóa tài khoản thành công</div>
        @endif
    </div>
</div>

<div id="editUserModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditUserModal()">&times;</span>
        <h2>Sửa thông tin người dùng</h2>
        <form id="editUserForm">
            @csrf
            <input type="hidden" name="id" id="edit_user_id">
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="status" id="edit_user_status">
                    <option value="1">Hoạt động</option>
                    <option value="0">Ngừng</option>
                </select>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" id="edit_user_role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn-primary-save">Cập nhật</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const BASE_URL = '{{ $baseUrl }}';
    const FRONT_URL = '{{ $frontUrl }}';
</script>
<script src="{{ $frontUrl }}/js/customers.js"></script>
@endpush