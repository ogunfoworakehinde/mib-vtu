@extends('layouts.app')
@section('content')
<h4 class="mb-3 fw-bold">Transaction History</h4>
@if($transactions->count())
    @foreach($transactions as $t)
    <div class="card mb-2 p-3" onclick="viewTransaction({{ $t->id }})" style="cursor:pointer">
        <div class="d-flex justify-content-between">
            <div>
                <strong>{{ $t->network }}</strong> <small class="text-muted">{{ $t->plan_name }}</small><br>
                <small>{{ $t->phone }}</small>
            </div>
            <div class="text-end">
                <span class="badge bg-{{ $t->status=='success'?'success':'warning' }}">{{ $t->status }}</span><br>
                ₦{{ number_format($t->amount,2) }}
            </div>
        </div>
    </div>
    @endforeach
    {{ $transactions->links() }}
@else
    <p class="text-muted text-center py-4">No transactions found.</p>
@endif

<!-- Receipt Modal (reuse same as dashboard) -->
<div class="modal fade" id="receiptModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3">
      <div class="modal-header"><h5>Receipt</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="receiptContent">Loading...</div>
    </div>
  </div>
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
                <p><strong>Ref:</strong> ${data.reference}</p>
                <p><strong>Type:</strong> ${data.service_type.toUpperCase()} – ${data.network}</p>
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
