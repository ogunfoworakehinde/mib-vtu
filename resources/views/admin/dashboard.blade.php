@extends('layouts.app')
@section('content')
<div class="container">
    <h4 class="fw-bold mb-4">Admin Dashboard</h4>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card p-4 text-center shadow-sm">
                <i class="bi bi-people-fill fs-1 text-primary"></i>
                <h5 class="mt-2">{{ $totalUsers }}</h5>
                <small class="text-muted">Total Users</small>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card p-4 text-center shadow-sm">
                <i class="bi bi-arrow-left-right fs-1 text-success"></i>
                <h5 class="mt-2">{{ $totalTransactions }}</h5>
                <small class="text-muted">Transactions</small>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card p-4 text-center shadow-sm">
                <i class="bi bi-cash-stack fs-1 text-warning"></i>
                <h5 class="mt-2">₦{{ number_format($totalRevenue,2) }}</h5>
                <small class="text-muted">Revenue</small>
            </div>
        </div>
    </div>
    <div class="d-flex gap-3">
        <a href="{{ route('admin.users') }}" class="btn btn-primary"><i class="bi bi-people"></i> Manage Users</a>
        <a href="{{ route('admin.transactions') }}" class="btn btn-outline-primary"><i class="bi bi-list-ul"></i> View Transactions</a>
    </div>
</div>
@endsection
