@extends('layouts.app')
@section('content')

@php
    $user = auth()->user();
@endphp

{{-- ========== WALLET CARD (Unchanged) ========== --}}
<div class="mb-4">
    <div class="wallet-card p-3 rounded-4 shadow-lg position-relative overflow-hidden">
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
            <br><small class="text-muted">{{ $t->created_at->format('d M, h:i A') }}</small>
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

{{-- ========== FUND MODAL (unchanged) ========== --}}
<div class="modal fade" id="fundModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content p-3">
    <div class="modal-header"><h5 class="modal-title">Fund Wallet</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <input type="number" id="amount" class="form-control mb-3" placeholder="Amount (₦)" min="100">
        <button id="payBtn" class="btn btn-primary w-100 rounded-pill">Pay with Paystack</button>
    </div>
  </div></div>
</div>

{{-- ========== DATA MODAL (FULLSCREEN BELOW HEADER + MTN COMBINE + CATEGORIES) ========== --}}
<div class="modal fade" id="dataModal" tabindex="-1">
  <div class="modal-dialog modal-fullscreen-below-header">
    <div class="modal-content border-0 rounded-4 shadow-lg p-3" style="border-radius: 16px!important;">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Buy Data</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-semibold">Phone Number</label>
          <input type="text" id="dataPhone" class="form-control form-control-lg rounded-3" placeholder="08012345678" list="dataRecentNumbers" autocomplete="off">
          <datalist id="dataRecentNumbers"></datalist>
          <div id="detectedNetworkBadge" class="mt-2 d-none">
            <span class="badge bg-light text-dark border" id="detectedNetworkText"></span>
          </div>
        </div>

        <label class="form-label fw-semibold">Select Network</label>
        <div id="networkCardsContainer" class="row g-2 mb-3">
          <!-- Dynamically filled -->
        </div>

        <div id="plansContainer" class="d-none">
          <label class="form-label fw-semibold">Select Plan</label>
          <div id="planCategoriesContainer">
            <!-- Categories dynamically filled -->
          </div>
        </div>

        <button id="buyDataBtn" class="btn btn-primary w-100 rounded-pill mt-3 py-2 fw-semibold" disabled>
          <i class="bi bi-wifi me-2"></i> Purchase
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ========== AIRTIME MODAL (unchanged) ========== --}}
<div class="modal fade" id="airtimeModal" tabindex="-1">
  <div class="modal-dialog modal-fullscreen-below-header">
    <div class="modal-content border-0 rounded-4 shadow-lg p-3" style="border-radius: 16px!important;">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Buy Airtime</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-semibold">Phone Number</label>
          <input type="text" id="airtimePhone" class="form-control form-control-lg rounded-3" placeholder="08012345678" list="airtimeRecentNumbers" autocomplete="off">
          <datalist id="airtimeRecentNumbers"></datalist>
          <div id="airtimeDetectedBadge" class="mt-2 d-none">
            <span class="badge bg-light text-dark border" id="airtimeDetectedText"></span>
          </div>
        </div>

        <label class="form-label fw-semibold">Select Network</label>
        <div id="airtimeNetworkCards" class="row g-2 mb-3">
          <!-- Dynamically filled -->
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Amount (₦)</label>
          <input type="number" id="airtimeAmount" class="form-control form-control-lg rounded-3" placeholder="Minimum ₦50">
        </div>

        <button id="buyAirtimeBtn" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold" disabled>
          <i class="bi bi-phone me-2"></i> Purchase Airtime
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Receipt Modal (unchanged) --}}
<div class="modal fade" id="receiptModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content p-3">
    <div class="modal-header"><h5 class="modal-title">Transaction Receipt</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="receiptContent">Loading...</div>
  </div></div>
</div>

{{-- ========== CUSTOM CSS ========== --}}
<style>
    /* Fixed modals below header */
    .modal-fullscreen-below-header {
        position: fixed;
        top: var(--header-height, 56px);
        left: 0;
        right: 0;
        bottom: 0;
        width: auto;
        height: auto;
        margin: 0;
        max-width: 100%;
        transform: none;
        display: flex;
        align-items: flex-start;
        overflow-y: auto;
    }
    .modal-fullscreen-below-header .modal-content {
        max-height: calc(100vh - var(--header-height, 56px));
        overflow-y: auto;
        border-radius: 0;
        box-shadow: none;
    }

    .network-card {
        border: 2px solid transparent;
        border-radius: 12px;
        padding: 12px 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: var(--card-bg);
        box-shadow: var(--shadow-sm);
        position: relative;
    }
    .network-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    .network-card.selected {
        border-color: var(--primary);
        background: rgba(var(--primary-rgb), 0.05);
    }
    .network-card.locked {
        cursor: not-allowed;
        opacity: 0.6;
    }
    .network-card.locked.selected {
        opacity: 1;
        cursor: default;
    }
    .network-card i {
        font-size: 24px;
        color: var(--primary);
    }
    .network-card .name {
        font-weight: 600;
        font-size: 14px;
        margin-top: 4px;
    }
    .plan-card {
        border: 2px solid transparent;
        border-radius: 12px;
        padding: 12px 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: var(--card-bg);
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: center;
    }
    .plan-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    .plan-card.selected {
        border-color: var(--primary);
        background: rgba(var(--primary-rgb), 0.05);
    }
    .plan-card .plan-amount {
        font-weight: 700;
        font-size: 16px;
    }
    .plan-card .plan-label {
        font-size: 12px;
        color: var(--text-muted);
    }
    .category-header {
        font-weight: 700;
        font-size: 15px;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
        color: var(--primary);
        border-bottom: 1px solid var(--primary);
        padding-bottom: 4px;
    }
</style>

{{-- ========== SCRIPTS ========== --}}
<script>
// GLOBALS
const userEmail = '{{ $user->email }}';
const paystackKey = '{{ config("services.paystack.public") }}';

// ---------- SHOW / HIDE BALANCE (unchanged) ----------
const balanceEl = document.getElementById('balanceDisplay');
const toggleBtn = document.getElementById('toggleBalance');
const toggleIcon = document.getElementById('toggleIcon');
const realBalance = '₦{{ number_format($user->wallet_balance,2) }}';
let visible = true;

toggleBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    visible = !visible;
    if (visible) {
        balanceEl.innerText = realBalance;
        toggleIcon.className = 'bi bi-eye fs-5';
    } else {
        balanceEl.innerText = '****';
        toggleIcon.className = 'bi bi-eye-slash fs-5';
    }
});

// ---------- FUND WALLET (unchanged) ----------
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

// ---------- RECENT NUMBERS MANAGEMENT ----------
const MAX_RECENT = 5;

function getRecentNumbers() {
    const stored = localStorage.getItem('vtu_recent_phones');
    return stored ? JSON.parse(stored) : [];
}

function saveRecentNumber(phone) {
    let recent = getRecentNumbers();
    recent = recent.filter(p => p !== phone);
    recent.unshift(phone);
    recent = recent.slice(0, MAX_RECENT);
    localStorage.setItem('vtu_recent_phones', JSON.stringify(recent));
    updatePhoneLists();
}

function updatePhoneLists() {
    const recent = getRecentNumbers();
    [document.getElementById('dataRecentNumbers'), document.getElementById('airtimeRecentNumbers')].forEach(list => {
        if (!list) return;
        list.innerHTML = '';
        recent.forEach(phone => {
            const option = document.createElement('option');
            option.value = phone;
            list.appendChild(option);
        });
    });
}

updatePhoneLists();

// ---------- NETWORK HELPERS ----------
const networkIcons = {
    'mtn': 'bi bi-phone-fill text-warning',
    'glo': 'bi bi-phone-fill text-success',
    'airtel': 'bi bi-phone-fill text-danger',
    '9mobile': 'bi bi-phone-fill text-info'
};

function getShortCode(name, identifier) {
    const lower = (name + ' ' + identifier).toLowerCase();
    if (lower.includes('mtn')) return 'mtn';
    if (lower.includes('glo')) return 'glo';
    if (lower.includes('airtel')) return 'airtel';
    if (lower.includes('9mobile')) return '9mobile';
    return null;
}

const networkPrefixes = {
    '0703': 'mtn', '0706': 'mtn', '0803': 'mtn', '0806': 'mtn', '0810': 'mtn', '0813': 'mtn', '0814': 'mtn', '0816': 'mtn', '0903': 'mtn', '0906': 'mtn',
    '0705': 'glo', '0805': 'glo', '0807': 'glo', '0811': 'glo', '0815': 'glo', '0905': 'glo',
    '0701': 'airtel', '0708': 'airtel', '0802': 'airtel', '0808': 'airtel', '0812': 'airtel', '0901': 'airtel', '0902': 'airtel', '0907': 'airtel',
    '0809': '9mobile', '0817': '9mobile', '0818': '9mobile', '0908': '9mobile', '0909': '9mobile'
};

function detectNetwork(phoneNumber) {
    if (phoneNumber.startsWith('0') || phoneNumber.startsWith('+234')) {
        let formatted = phoneNumber.substring(phoneNumber.length - 10);
        formatted = '0' + formatted.substring(formatted.length - 10);
        const prefix = formatted.substring(0, 4);
        return networkPrefixes[prefix] || null;
    }
    return null;
}

let selectedDataNetwork = null;   // for MTN this will be the combined identifier 'mtn'
let selectedDataPlan = null;
let selectedAirtimeNetwork = null;
let dataNetworkLocked = false;
let airtimeNetworkLocked = false;

// ---------- CATEGORIZE PLAN ----------
function categorizePlan(label) {
    const str = label.toLowerCase();
    // Match patterns like "1 day", "2 days", "3 day", "7 days", "14 days", etc.
    const daysMatch = str.match(/(\d+)\s*days?/i);
    const weeksMatch = str.match(/(\d+)\s*weeks?/i);
    const monthsMatch = str.match(/(\d+)\s*months?/i);
    const yearMatch = str.match(/(\d+)\s*years?/i);
    const dayWordMatch = str.match(/(\d+)\s*day\b/i);

    if (yearMatch) return 'Yearly';
    if (monthsMatch) {
        const m = parseInt(monthsMatch[1]);
        return m >= 2 ? 'Bi-Monthly' : 'Monthly';
    }
    if (weeksMatch) return 'Weekly';
    if (daysMatch) {
        const d = parseInt(daysMatch[1]);
        if (d <= 3) return 'Daily';
        if (d <= 7) return 'Weekly';
        if (d <= 14) return 'Weekly';
        return 'Monthly';
    }
    if (dayWordMatch) {
        const d = parseInt(dayWordMatch[1]);
        if (d <= 3) return 'Daily';
        return 'Monthly';
    }
    // Fallback based on keywords
    if (str.includes('month')) return 'Monthly';
    if (str.includes('week')) return 'Weekly';
    if (str.includes('year')) return 'Yearly';
    return 'Other';
}

// ---------- LOAD DATA NETWORKS (MTN combined) ----------
function loadDataNetworks() {
    fetch('{{ route("data.networks") }}')
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('networkCardsContainer');
        if (!Array.isArray(data) || data.length === 0) {
            container.innerHTML = '<div class="col-12 text-muted">No networks available</div>';
            return;
        }
        // Group MTN entries
        const mtnItems = data.filter(n => getShortCode(n.name, n.identifier) === 'mtn');
        const nonMtnItems = data.filter(n => getShortCode(n.name, n.identifier) !== 'mtn');
        const networksToShow = [];
        // Combine MTN into one card if more than one MTN entry
        if (mtnItems.length > 0) {
            networksToShow.push({
                name: 'MTN',
                shortCode: 'mtn',
                combined: true,
                identifiers: mtnItems.map(n => n.identifier)  // e.g., ['mtn_gifting_data', 'mtn_data_share']
            });
        }
        // Add all other networks
        nonMtnItems.forEach(n => {
            networksToShow.push({
                name: n.name,
                identifier: n.identifier,
                shortCode: getShortCode(n.name, n.identifier),
                combined: false
            });
        });

        container.innerHTML = '';
        networksToShow.forEach(n => {
            const iconClass = networkIcons[n.shortCode] || 'bi bi-phone-fill text-secondary';
            const card = document.createElement('div');
            card.className = 'col-4 col-md-3';
            const uniqueId = n.combined ? 'mtn' : n.identifier;
            card.innerHTML = `
                <div class="network-card" data-network="${uniqueId}" data-shortcode="${n.shortCode}" ${n.combined ? 'data-combined="true"' : ''} data-identifiers='${JSON.stringify(n.identifiers || [n.identifier])}'>
                    <i class="${iconClass} fs-3"></i>
                    <div class="name">${n.name}</div>
                </div>
            `;
            const clickHandler = function() {
                if (dataNetworkLocked) {
                    showToast('Network is locked to detected number. Clear the number to change.', 'warning');
                    return;
                }
                document.querySelectorAll('#networkCardsContainer .network-card').forEach(el => el.classList.remove('selected'));
                this.classList.add('selected');
                selectedDataNetwork = this.dataset.network;
                document.getElementById('buyDataBtn').disabled = true;
                // Load plans – if MTN combined, fetch and merge both
                if (this.dataset.combined === 'true') {
                    const identifiers = JSON.parse(this.dataset.identifiers);
                    loadCombinedDataPlans(identifiers);
                } else {
                    loadDataPlans(this.dataset.network);
                }
            };
            card.querySelector('.network-card').addEventListener('click', clickHandler);
            container.appendChild(card);
        });
    });
}

// ---------- LOAD DATA PLANS (single network) ----------
function loadDataPlans(networkId) {
    const plansContainer = document.getElementById('plansContainer');
    const planCategories = document.getElementById('planCategoriesContainer');
    planCategories.innerHTML = '<div class="col-12 text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></div>';
    plansContainer.classList.remove('d-none');

    fetch(`/data/plans?network_id=${networkId}`)
    .then(r => r.json())
    .then(plans => {
        renderPlans(plans);
    });
}

// ---------- LOAD COMBINED MTN PLANS ----------
function loadCombinedDataPlans(identifiers) {
    const plansContainer = document.getElementById('plansContainer');
    const planCategories = document.getElementById('planCategoriesContainer');
    planCategories.innerHTML = '<div class="col-12 text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></div>';
    plansContainer.classList.remove('d-none');

    const promises = identifiers.map(id =>
        fetch(`/data/plans?network_id=${id}`).then(r => r.json())
    );
    Promise.all(promises)
    .then(results => {
        // merge all plans, remove duplicate plan codes
        let merged = [];
        const seenCodes = new Set();
        results.forEach(arr => {
            if (Array.isArray(arr)) {
                arr.forEach(p => {
                    if (!seenCodes.has(p.code)) {
                        seenCodes.add(p.code);
                        merged.push(p);
                    }
                });
            }
        });
        renderPlans(merged);
    })
    .catch(() => {
        planCategories.innerHTML = '<div class="col-12 text-muted">Failed to load plans.</div>';
    });
}

// ---------- RENDER PLANS WITH CATEGORIES ----------
function renderPlans(plans) {
    const planCategories = document.getElementById('planCategoriesContainer');
    planCategories.innerHTML = '';
    if (!Array.isArray(plans) || plans.length === 0) {
        planCategories.innerHTML = '<div class="col-12 text-muted">No plans available for this network</div>';
        return;
    }

    // Group by category
    const categories = {};
    plans.forEach(p => {
        const cat = categorizePlan(p.name);
        if (!categories[cat]) categories[cat] = [];
        categories[cat].push(p);
    });

    // Order categories
    const catOrder = ['Daily', 'Weekly', 'Monthly', 'Bi-Monthly', 'Yearly', 'Other'];
    const sortedCats = Object.keys(categories).sort((a, b) => catOrder.indexOf(a) - catOrder.indexOf(b));

    sortedCats.forEach(cat => {
        const catDiv = document.createElement('div');
        catDiv.innerHTML = `<div class="category-header">${cat}</div>`;
        planCategories.appendChild(catDiv);

        const rowDiv = document.createElement('div');
        rowDiv.className = 'row g-2';
        categories[cat].forEach(p => {
            const card = document.createElement('div');
            card.className = 'col-6 col-md-4';
            card.innerHTML = `
                <div class="plan-card" data-code="${p.code}" data-price="${p.price}">
                    <div class="plan-amount">₦${p.price}</div>
                    <div class="plan-label">${p.name}</div>
                </div>
            `;
            card.querySelector('.plan-card').addEventListener('click', function() {
                document.querySelectorAll('#planCategoriesContainer .plan-card').forEach(el => el.classList.remove('selected'));
                this.classList.add('selected');
                selectedDataPlan = { code: this.dataset.code, price: this.dataset.price };
                document.getElementById('buyDataBtn').disabled = false;
            });
            rowDiv.appendChild(card);
        });
        planCategories.appendChild(rowDiv);
    });
}

// ---------- LOAD AIRTIME NETWORKS ----------
function loadAirtimeNetworks() {
    fetch('{{ route("airtime.networks") }}')
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('airtimeNetworkCards');
        if (!Array.isArray(data) || data.length === 0) {
            container.innerHTML = '<div class="col-12 text-muted">No networks available</div>';
            return;
        }
        container.innerHTML = '';
        data.forEach(n => {
            const icon = networkIcons[n.id] || 'bi bi-phone-fill text-secondary';
            const card = document.createElement('div');
            card.className = 'col-4 col-md-3';
            card.innerHTML = `
                <div class="network-card" data-network="${n.id}" data-name="${n.name}">
                    <i class="${icon} fs-3"></i>
                    <div class="name">${n.name}</div>
                </div>
            `;
            const clickHandler = function() {
                if (airtimeNetworkLocked) {
                    showToast('Network is locked to detected number. Clear the number to change.', 'warning');
                    return;
                }
                document.querySelectorAll('#airtimeNetworkCards .network-card').forEach(el => el.classList.remove('selected'));
                this.classList.add('selected');
                selectedAirtimeNetwork = this.dataset.network;
                document.getElementById('buyAirtimeBtn').disabled = false;
            };
            card.querySelector('.network-card').addEventListener('click', clickHandler);
            container.appendChild(card);
        });
    });
}

// ---------- AUTO-DETECT AND LOCK (unchanged) ----------
// Data modal auto-detect
document.getElementById('dataPhone').addEventListener('input', function() {
    const phone = this.value.trim();
    const badge = document.getElementById('detectedNetworkBadge');
    const text = document.getElementById('detectedNetworkText');
    const shortCode = detectNetwork(phone);

    if (shortCode && phone.length >= 10) {
        badge.classList.remove('d-none');
        text.textContent = `Detected: ${shortCode.toUpperCase()}`;
        dataNetworkLocked = true;
        const cards = document.querySelectorAll('#networkCardsContainer .network-card');
        cards.forEach(card => {
            card.classList.remove('selected', 'locked');
            if (card.dataset.shortcode === shortCode) {
                card.classList.add('selected');
                card.classList.remove('locked');
                selectedDataNetwork = card.dataset.network;
                // Trigger plan loading
                if (card.dataset.combined === 'true') {
                    const identifiers = JSON.parse(card.dataset.identifiers);
                    loadCombinedDataPlans(identifiers);
                } else {
                    loadDataPlans(card.dataset.network);
                }
            } else {
                card.classList.add('locked');
            }
        });
    } else {
        badge.classList.add('d-none');
        dataNetworkLocked = false;
        document.querySelectorAll('#networkCardsContainer .network-card').forEach(card => card.classList.remove('locked', 'selected'));
        selectedDataNetwork = null;
        document.getElementById('plansContainer').classList.add('d-none');
        document.getElementById('buyDataBtn').disabled = true;
    }
});

// Airtime modal auto-detect
document.getElementById('airtimePhone').addEventListener('input', function() {
    const phone = this.value.trim();
    const badge = document.getElementById('airtimeDetectedBadge');
    const text = document.getElementById('airtimeDetectedText');
    const shortCode = detectNetwork(phone);

    if (shortCode && phone.length >= 10) {
        badge.classList.remove('d-none');
        text.textContent = `Detected: ${shortCode.toUpperCase()}`;
        airtimeNetworkLocked = true;
        const cards = document.querySelectorAll('#airtimeNetworkCards .network-card');
        cards.forEach(card => {
            card.classList.remove('selected', 'locked');
            if (card.dataset.network === shortCode) {
                card.classList.add('selected');
                card.classList.remove('locked');
                selectedAirtimeNetwork = shortCode;
                document.getElementById('buyAirtimeBtn').disabled = false;
            } else {
                card.classList.add('locked');
            }
        });
    } else {
        badge.classList.add('d-none');
        airtimeNetworkLocked = false;
        document.querySelectorAll('#airtimeNetworkCards .network-card').forEach(card => card.classList.remove('locked', 'selected'));
        selectedAirtimeNetwork = null;
        document.getElementById('buyAirtimeBtn').disabled = true;
    }
});

// ---------- BUY DATA (unchanged) ----------
document.getElementById('buyDataBtn').addEventListener('click', () => {
    if (!selectedDataNetwork || !selectedDataPlan) return showToast('Select network and plan', 'warning');
    const phone = document.getElementById('dataPhone').value.trim();
    if (!phone || phone.length < 10) return showToast('Enter a valid phone number', 'warning');

    const btn = document.getElementById('buyDataBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing';

    fetch('{{ route("data.buy") }}', {
        method:'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken},
        body: JSON.stringify({network: selectedDataNetwork, plan_code: selectedDataPlan.code, phone: phone})
    })
    .then(r => r.json())
    .then(d => {
        if(d.success) {
            saveRecentNumber(phone);
            showToast(d.message || 'Data purchased', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(d.message || d.error || 'Purchase failed', 'danger');
        }
    })
    .catch(() => showToast('Network error', 'danger'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-wifi me-2"></i> Purchase';
    });
});

// ---------- BUY AIRTIME (unchanged) ----------
document.getElementById('buyAirtimeBtn').addEventListener('click', () => {
    if (!selectedAirtimeNetwork) return showToast('Select a network', 'warning');
    const phone = document.getElementById('airtimePhone').value.trim();
    const amount = document.getElementById('airtimeAmount').value.trim();
    if (!phone || phone.length < 10) return showToast('Enter a valid phone number', 'warning');
    if (!amount || amount < 50) return showToast('Minimum ₦50', 'warning');

    const btn = document.getElementById('buyAirtimeBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing';

    fetch('{{ route("airtime.buy") }}', {
        method:'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken},
        body: JSON.stringify({network: selectedAirtimeNetwork, phone: phone, amount: amount})
    })
    .then(r => r.json())
    .then(d => {
        if(d.success) {
            saveRecentNumber(phone);
            showToast(d.message || 'Airtime sent', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(d.message || d.error || 'Purchase failed', 'danger');
        }
    })
    .catch(() => showToast('Network error', 'danger'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-phone me-2"></i> Purchase Airtime';
    });
});

// ---------- TRANSACTION RECEIPT (unchanged) ----------
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDataNetworks();
    loadAirtimeNetworks();
});
</script>

{{-- No need for @push --}}
@endsection
