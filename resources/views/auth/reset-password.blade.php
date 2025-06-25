@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <div class="text-center mb-7">
        <a href="{{ route('home') }}"><img src="{{ asset('theme/assets/images/logo/brand-icon.svg') }}" alt="brand" /></a>
        <h1 class="mb-1">Set New Password</h1>
        <p class="mb-0">Please enter your email and new password.</p>
    </div>
    
    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="alert alert-danger mb-3" role="alert">
            <ul class="list-unstyled mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        
        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="email" />
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="password-field position-relative">
                <input type="password" class="form-control fakePassword" id="password" name="password" required autocomplete="new-password" />
                <span><i class="bi bi-eye-slash passwordToggler"></i></span>
            </div>
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <div class="password-field position-relative">
                <input type="password" class="form-control fakePassword" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" />
                <span><i class="bi bi-eye-slash passwordToggler"></i></span>
            </div>
        </div>
        <div class="d-grid mb-4">
            <button class="btn btn-primary" type="submit">Reset Password</button>
        </div>
        <div class="text-center">
            <a href="{{ route('login') }}" class="icon-link icon-link-hover">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z" />
                </svg>
                <span>Back to Login</span>
            </a>
        </div>
    </form>
@endsection
