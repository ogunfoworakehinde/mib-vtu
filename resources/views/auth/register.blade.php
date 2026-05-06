@extends('layouts.auth')
@section('title', 'Register')

@section('content')
<h5 class="text-center mb-3 fw-semibold">Create Account</h5>
<p class="text-center text-muted mb-4">Join {{ config('app.name') }} today.</p>

<form id="regForm" method="POST" action="{{ route('register') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    
    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="full_name" class="form-control" placeholder="John Doe" required>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Email address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Phone Number</label>
        <input type="text" name="phone" class="form-control" placeholder="08012345678" required>
    </div>
    
    <div class="mb-3 position-relative">
        <label class="form-label">Password</label>
        <input type="password" name="password" id="regPassword" class="form-control" placeholder="Minimum 6 characters" required>
        <span class="password-toggle" id="regToggleIcon" onclick="togglePassword('regPassword','regToggleIcon')">
            <i class="bi bi-eye-slash"></i>
        </span>
    </div>
    
    <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Register</button>
    
    <div class="text-center">
        <a href="{{ route('login') }}" class="text-decoration-none fw-medium">Already have an account?</a>
    </div>
</form>

<div id="regMsg" class="mt-3"></div>

@push('scripts')
<script>
document.getElementById('regForm').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    fetch('{{ route("register") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if(data.message) {
            document.getElementById('regMsg').innerHTML = '<div class="alert alert-success py-2">' + data.message + '</div>';
        } else if(data.error) {
            document.getElementById('regMsg').innerHTML = '<div class="alert alert-danger py-2">' + data.error + '</div>';
        } else if(data.errors) {
            let errors = '';
            for(let key in data.errors) {
                errors += data.errors[key].join('<br>') + '<br>';
            }
            document.getElementById('regMsg').innerHTML = '<div class="alert alert-danger py-2">' + errors + '</div>';
        }
    })
    .catch(() => {
        // Fallback to normal form submission if AJAX fails
        document.getElementById('regForm').submit();
    });
});
</script>
@endpush
@endsection
