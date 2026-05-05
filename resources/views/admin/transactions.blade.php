@extends('layouts.app')
@section('content')
<div class="container">
    <h4 class="fw-bold mb-4">Transaction Logs</h4>
    <form method="GET" class="row g-2 mb-4">
        <div class="col-5 col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="success" {{ request('status')=='success'?'selected':'' }}>Success</option>
                <option value="failed" {{ request('status')=='failed'?'selected':'' }}>Failed</option>
                <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
            </select>
        </div>
        <div class="col-5 col-md-3">
            <select name="service" class="form-select">
                <option value="">All Services</option>
                <option value="data" {{ request('service')=='data'?'selected':'' }}>Data</option>
                <option value="airtime" {{ request('service')=='airtime'?'selected':'' }}>Airtime</option>
            </select>
        </div>
        <div class="col-2 col-md-2">
            <button class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th><th>User</th><th>Service</th><th>Network</th><th>Phone</th><th>Amount</th><th>Status</th><th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $tx)
                <tr>
                    <td>{{ $tx->id }}</td>
                    <td>{{ $tx->user->email ?? 'N/A' }}</td>
                    <td>{{ $tx->service_type }}</td>
                    <td>{{ $tx->network }}</td>
                    <td>{{ $tx->phone }}</td>
                    <td>₦{{ number_format($tx->amount,2) }}</td>
                    <td><span class="badge bg-{{ $tx->status=='success'?'success':($tx->status=='failed'?'danger':'warning') }}">{{ $tx->status }}</span></td>
                    <td>{{ $tx->created_at->format('d M H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $transactions->links() }}
</div>
@endsection
