@extends('layouts.app')
@section('content')
<div class="container">
    <h4 class="fw-bold mb-4">Users Management</h4>
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Balance</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->full_name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone }}</td>
                    <td><span class="badge bg-{{ $user->role=='admin'?'primary':'secondary' }}">{{ $user->role }}</span></td>
                    <td><span class="badge bg-{{ $user->status=='active'?'success':'danger' }}">{{ $user->status }}</span></td>
                    <td>₦{{ number_format($user->wallet_balance,2) }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUser{{ $user->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @foreach($users as $user)
    <!-- Edit User Modal -->
    <div class="modal fade" id="editUser{{ $user->id }}" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered"><div class="modal-content p-3">
        <div class="modal-header"><h5>Edit User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                <div class="mb-3"><label class="form-label">Full Name</label><input name="full_name" class="form-control" value="{{ $user->full_name }}" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="{{ $user->email }}" required></div>
                <div class="mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ $user->phone }}" required></div>
                <div class="mb-3"><label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="user" {{ $user->role=='user'?'selected':'' }}>User</option>
                        <option value="admin" {{ $user->role=='admin'?'selected':'' }}>Admin</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ $user->status=='active'?'selected':'' }}>Active</option>
                        <option value="suspended" {{ $user->status=='suspended'?'selected':'' }}>Suspended</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">New Password (leave blank to keep)</label><input type="password" name="new_password" class="form-control"></div>
                <button class="btn btn-primary w-100">Save Changes</button>
            </form>
        </div>
      </div></div>
    </div>
    @endforeach
</div>
@endsection
