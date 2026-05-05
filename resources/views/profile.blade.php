@extends('layouts.app')
@section('content')
<div class="container">
    <h4 class="mb-4 fw-bold">Profile Settings</h4>
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="profile-card card p-4 mb-4">
        <h5 class="mb-3">Account Details</h5>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input name="full_name" class="form-control" value="{{ $user->full_name }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" value="{{ $user->email }}" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input name="phone" class="form-control" value="{{ $user->phone }}" required>
            </div>
            <button class="btn btn-primary w-100 mt-2">Update Profile</button>
        </form>
    </div>

    <div class="profile-card card p-4">
        <h5 class="mb-3">Change Password</h5>
        <form method="POST" action="{{ route('profile.password') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="new_password_confirmation" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100 mt-2">Update Password</button>
        </form>
    </div>
</div>
@endsection
