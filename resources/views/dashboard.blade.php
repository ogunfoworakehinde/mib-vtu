@extends('layouts.app')
@section('content')

@php
    $user = auth()->user();
@endphp

{{-- ========== WALLET CARD (Opay style) ========== --}}
<div class="mb-4">
    <div class="wallet-card p-3 rounded-4 shadow-lg position-relative overflow-hidden">
        {{-- Decorative blobs – non‑interactive --}}
        <div style="position:absolute; top:-25px; right:-25px; width:110px; height:110px; background:rgba(255,255,255,0.12); border-radius:50%; pointer-events:none; z-index:0;"></div>
        <div style="position:absolute; bottom:-35px; left:-35px; width:90px; height:90px; background:rgba(255,255,255,0.08); border-radius:50%; pointer-events:none; z-index:0;"></div>

        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index:1;">
            <div>
                <small class="text-white-50 fw-semibold">Wallet Balance</small>
                <h2 class="mb-0 fw-bold" id="balanceDisplay">₦{{ number_format($user->wallet_balance,2) }}</h2>
            </div>
            <button class="btn btn-sm btn-outline-light rounded-circle" id="toggleBalance" style="width:42px;height:42px; z-index:2; position:relative;">
                <i class="bi bi-eye fs-5" id="toggleIcon"></i>
            </button>
        </div>
        <div class="mt-3 position-relative" style="z-index:1;">
            <button class="btn btn-light btn-sm px-3 rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#fundModal">
                <i class="bi bi-plus-circle"></i> Fund Wallet
            </button>
        </div>
    </div>
</div>

{{-- ========== QUICK ACTIONS ========== --}}
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="quick-action" data-bs-toggle="modal" data-bs-target="#dataModal">
            <i class="bi bi-wifi d-block"></i><span class="small fw-medium">Data</span>
        </div>
    </div>
    <div class="col-4">
        <div class="quick-action" data-bs-toggle="modal" data-bs-target="#airtimeModal">
            <i class="bi bi-phone d-block"></i><span class="small fw-medium">Airtime</span>
        </div>
    </div>
    <div class="col-4">
        <div class="quick-action" onclick="showToast('Coming soon', 'warning')">
            <i class="bi bi-tv d-block"></i><span class="small fw-medium">TV</span>
        </div>
    </div>
</div>

{{-- ========== RECENT TRANSACTIONS ========== --}}
<h5 class="mb-3 fw-bold">Recent Transactions</h5>
@forelse($transactions as $t)
<div class="card mb-2 p-3 shadow-sm" style="cursor:pointer" onclick="viewTransaction({{ $t->id }})">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <strong>{{ $t->network }}</strong>
            <span class="text-muted small">{{ $t->plan_name }}</span><br>
            <small class="text-muted">{{ $t->phone }}</small>
        </div>
        <div class="text-end">
            <span class="badge bg-{{ $t->status=='success'?'success':'warning' }}">{{ $t->status }}</span>
            <br><strong>₦{{ number_format($t->amount,2) }}</strong>
        </div>
    </div>
</div>
@empty
<p class="text-muted text-center py-4">No transactions yet.</p>
@endforelse

{{-- ========== MODALS ========== --}}
{{-- Fund Modal --}}
<div class="modal fade" id="fundModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content p-3">
    <div class="modal-header"><h5 class="modal-title">Fund Wallet</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <input type="number" id="amount" class="form-control mb-3" placeholder="Amount (₦)" min="100">
        <button id="payBtn" class="btn btn-primary w-100 rounded-pill">Pay with Paystack</button>
    </div>
  </div></div>
</div>

{{-- Data Modal --}}
<div class="modal fade" id="dataModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content p-3">
    <div class="modal-header"><h5 class="modal-title">Buy Data</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <select id="dataNetwork" class="form-select mb-3"><option value="">Select Network</option></select>
        <select id="dataPlan" class="form-select mb-3"><option value="">Select Plan</option></select>
        <input type="text" id="dataPhone" class="form-control mb-3" placeholder="Phone number (11 digits)">
        <button id="buyDataBtn" class="btn btn-primary w-100 rounded-pill">Purchase</button>
    </div>
  </div></div>
</div>

{{-- Airtime Modal --}}
<div class="modal fade" id="airtimeModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content p-3">
    <div class="modal-header"><h5 class="modal-title">Buy Airtime</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <select id="airtimeNetwork" class="form-select mb-3"><option value="">Select Network</option></select>
        <input type="text" id="airtimePhone" class="form-control mb-3" placeholder="Phone number">
        <input type="number" id="airtimeAmount" class="form-control mb-3" placeholder="Amount (₦50 minimum)">
        <button id="buyAirtimeBtn" class="btn btn-primary w-100 rounded-pill">Buy Airtime</button>
    </div>
  </div></div>
</div>

{{-- Receipt Modal --}}
<div class="modal fade" id="receiptModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content p-3">
    <div class="modal-header"><h5 class="modal-title">Transaction Receipt</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="receiptContent">Loading...</div>
  </div></div>
</div>

{{-- ========== SCRIPTS (inlined to avoid push issues) ========== --}}
<script>
// GLOBALS
const userEmail = '{{ $user->email }}';
const paystackKey = '{{ config("services.paystack.public") }}';

// ---------- SHOW / HIDE BALANCE (no class toggling) ----------
const balanceEl = document.getElementById('balanceDisplay');
const toggleBtn = document.getElementById('toggleBalance');
const toggleIcon = document.getElementById('toggleIcon');
const realBalance = '₦{{ number_format($user->wallet_balance,2) }}';
let visible = true;

toggleBtn.addEventListener('click', function(e) {
    e.stopPropagation(); // ensure no parent handlers interfere
    visible = !visible;
    if (visible) {
        balanceEl.innerText = realBalance;
        toggleIcon.className = 'bi bi-eye fs-5';
    } else {
        balanceEl.innerText = '****';
        toggleIcon.className = 'bi bi-eye-slash fs-5';
    }
});

// ---------- FUND WALLET ----------
document.getElementById('payBtn').addEventListener('click', () => {
    let amount = document.getElementById('amount').value;
    if(!amount || amount < 100) return showToast('Minimum ₦100', 'warning');
    PaystackPop.setup({
        key: paystackKey,
        email: userEmail,
        amount: amount * 100,
        ref: 'PS-'+Math.floor(Math.random()*1000000000),
        callback: function(response){
            fetch('{{ route("wallet.verify") }}', {
                method:'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken},
                body: JSON.stringify({reference: response.reference})
            }).then(r=>r.json()).then(data => {
                if(data.success) location.reload();
                else showToast(data.error || 'Verification failed', 'danger');
            });
        },
        onClose: () => showToast('Payment cancelled', 'info')
    }).openIframe();
});

// ---------- LOAD NETWORKS ----------
fetch('{{ route("data.networks") }}')
.then(r => r.json())
.then(data => {
    let opt = '<option value="">Select</option>';
    if(Array.isArray(data)){
        data.forEach(n => opt += `<option value="${n.id||n.code||n.name}">${n.name}</option>`);
    }
    document.getElementById('dataNetwork').innerHTML = opt;
    document.getElementById('airtimeNetwork').innerHTML = opt;
});

// ---------- DATA PLANS ----------
document.getElementById('dataNetwork').addEventListener('change', function(){
    fetch(`/data/plans?network_id=${this.value}`)
    .then(r => r.json())
    .then(data => {
        let opt = '<option value="">Select Plan</option>';
        data.forEach(p => opt += `<option value="${p.code}" data-price="${p.price}">${p.name} – ₦${p.price}</option>`);
        document.getElementById('dataPlan').innerHTML = opt;
    });
});

// ---------- BUY DATA ----------
document.getElementById('buyDataBtn').addEventListener('click', () => {
    let net = document.getElementById('dataNetwork').value;
    let plan = document.getElementById('dataPlan').value;
    let phone = document.getElementById('dataPhone').value;
    if(!net || !plan || !phone) return showToast('Fill all fields', 'warning');
    fetch('{{ route("data.buy") }}', {
        method:'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken},
        body: JSON.stringify({network:net, plan_code:plan, phone:phone})
    }).then(r=>r.json()).then(d => {
        if(d.success) { showToast(d.message,'success'); setTimeout(()=>location.reload(),1500); }
        else showToast(d.message,'danger');
    });
});

// ---------- BUY AIRTIME ----------
document.getElementById('buyAirtimeBtn').addEventListener('click', () => {
    let net = document.getElementById('airtimeNetwork').value;
    let phone = document.getElementById('airtimePhone').value;
    let amount = document.getElementById('airtimeAmount').value;
    if(!net || !phone || !amount) return showToast('Fill all fields', 'warning');
    fetch('{{ route("airtime.buy") }}', {
        method:'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken},
        body: JSON.stringify({network:net, phone:phone, amount:amount})
    }).then(r=>r.json()).then(d => {
        if(d.success) { showToast(d.message,'success'); setTimeout(()=>location.reload(),1500); }
        else showToast(d.message,'danger');
    });
});

// ---------- TRANSACTION RECEIPT ----------
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

{{-- No need for @push --}}
@endsection
