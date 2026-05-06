@extends('layouts.auth')
@section('title', 'Login')

@section('content')
<h5 class="text-center mb-3 fw-semibold">Sign In</h5>
<p class="text-center text-muted mb-4">Welcome back! Enter your details.</p>

<form id="loginForm" method="POST" action="{{ route('login') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    
    <div class="mb-3">
        <label class="form-label">Email address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
    </div>
    
    <div class="mb-3 position-relative">
        <label class="form-label">Password</label>
        <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Enter your password" required>
        <span class="password-toggle" id="loginToggleIcon" onclick="togglePassword('loginPassword','loginToggleIcon')">
            <i class="bi bi-eye-slash"></i>
        </span>
    </div>
    
    <div class="mb-3 form-check">
        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
        <label class="form-check-label" for="remember">Remember me</label>
    </div>
    
    <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Sign In</button>
    
    <div class="text-center">
        <a href="{{ route('register') }}" class="text-decoration-none fw-medium">Create an account</a>
    </div>
</form>

<div id="loginMsg" class="mt-3"></div>

@push('scripts')
<script>
document.getElementById('loginForm').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    fetch('{{ route("login") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if(data.redirect) {
            window.location.href = data.redirect;
        } else {
            document.getElementById('loginMsg').innerHTML = '<div class="alert alert-danger py-2">' + (data.error || data.message) + '</div>';
        }
    })
    .catch(() => {
        // If AJAX fails, the form will be submitted normally because we have action and method attributes
        document.getElementById('loginForm').submit();
    });
});
</script>
@endpush
@endsection
