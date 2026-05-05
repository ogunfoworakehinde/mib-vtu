@extends('layouts.app')
@section('content')
<h4 class="mb-3 fw-bold">Contact Support</h4>
<div class="card p-4">
    <p><i class="bi bi-envelope-fill me-2"></i> <strong>Email:</strong> {{ config('app.email') }}</p>
    <p><i class="bi bi-telephone-fill me-2"></i> <strong>Phone:</strong> {{ config('app.phone') }}</p>
    <p class="mt-3">We are available 24/7 to assist you with any issues.</p>
    <a href="mailto:{{ config('app.email') }}" class="btn btn-primary mt-2">
        <i class="bi bi-envelope"></i> Send Email
    </a>
</div>
@endsection
