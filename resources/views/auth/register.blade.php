@extends('layouts.auth')

@section('title', 'Sign Up')

@section('content')
    <div class="text-center mb-7">
        <a href="{{ route('home') }}"><img src="{{ asset('theme/assets/images/logo/brand-icon.svg') }}" alt="brand" class="mb-3" /></a>
        <h1 class="mb-1">Create Account</h1>
        <p class="mb-0">
            Already have an account? <a href="{{ route('login') }}" class="text-primary">Sign in</a>
        </p>
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
    
    <form method="POST" action="{{ route('register') }}" class="mb-6">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label"> Name <span class="text-danger">*</span> </label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
        </div>
        <div class="mb-3">
            <label for="email" class="form-label"> Email <span class="text-danger">*</span> </label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" />
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
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="terms" name="terms" required />
                <label class="form-check-label ms-2" for="terms">
                    I agree to the <a href="#">Terms of Use</a> & <a href="#">Privacy Policy</a>
                </label>
            </div>
        </div>
        <div class="d-grid">
            <button class="btn btn-primary" type="submit">Sign Up</button>
        </div>
    </form>
    <span>Sign up with your social network.</span>
    <div class="d-grid mt-3">
        <a href="{{ route('social.login', 'google') }}" class="btn btn-google">
            <span class="me-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-google" viewBox="0 0 16 16">
                    <path d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z" />
                </svg>
            </span>
            Continue with Google
        </a>
    </div>
    <div class="text-center mt-7">
        <div class="small mb-3 mb-lg-0 text-body-tertiary">
            Copyright © <span class="text-primary"><a href="{{ route('home') }}">NovelWriter</a></span>
        </div>
    </div>
@endsection
