@extends('layouts.app')
@section('content')
<h4 class="mb-3 fw-bold">Transaction History</h4>
@if($transactions->count())
    @foreach($transactions as $t)
    <div class="card mb-2 p-3 shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                @if($t instanceof \App\Models\VtuTransaction)
                    <strong>{{ $t->network }}</strong>
                    <span class="text-muted small">{{ $t->plan_name ?? $t->service_type }}</span><br>
                    <small class="text-muted">{{ $t->phone }}</small>
                @else
                    <strong>Wallet Funding</strong><br>
                    <small class="text-muted">{{ $t->description }}</small>
                @endif
            </div>
            <div class="text-end">
                <span class="badge bg-{{ $t->status=='success'?'success':'warning' }}">{{ $t->status }}</span>
                <br>
                <strong>₦{{ number_format($t->amount,2) }}</strong>
                <br>
                <small class="text-muted">{{ $t->created_at->format('d M Y, h:i A') }}</small>
            </div>
        </div>
        @if($t instanceof \App\Models\VtuTransaction)
        <div class="text-end mt-1">
            <a href="#" onclick="viewTransaction({{ $t->id }})" class="text-decoration-none small">View Receipt</a>
        </div>
        @endif
    </div>
    @endforeach
    {{ $transactions->links() }}
@else
    <p class="text-muted text-center py-4">No transactions found.</p>
@endif

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content p-3">
    <div class="modal-header"><h5 class="modal-title">Transaction Receipt</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="receiptContent">Loading...</div>
  </div></div>
</div>

@push('scripts')
<script>
async function viewTransaction(id) {
    document.getElementById('receiptContent').innerHTML = 'Loading...';
    const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
    modal.show();
    try {
        let res = await fetch(`/transactions/${id}`, {
            headers: {'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json'}
        });
        let data = await res.json();
        if(data.error) {
            document.getElementById('receiptContent').innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
        } else {
            document.getElementById('receiptContent').innerHTML = `
                <p><strong>Reference:</strong> ${data.reference}</p>
                <p><strong>Service:</strong> ${data.service_type.toUpperCase()} - ${data.network}</p>
                <p><strong>Phone:</strong> ${data.phone}</p>
                <p><strong>Plan:</strong> ${data.plan_name || 'N/A'}</p>
                <p><strong>Amount:</strong> ₦${parseFloat(data.amount).toFixed(2)}</p>
                <p><strong>Status:</strong> <span class="badge bg-${data.status=='success'?'success':'warning'}">${data.status}</span></p>
                <p><strong>Date:</strong> ${data.created_at}</p>
            `;
        }
    } catch(e) {
        document.getElementById('receiptContent').innerHTML = 'Failed to load.';
    }
}
</script>
@endpush
@endsection
