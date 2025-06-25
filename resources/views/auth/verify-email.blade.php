@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
    <div class="text-center mb-7">
        <a href="{{ route('home') }}"><img src="{{ asset('theme/assets/images/logo/brand-icon.svg') }}" alt="brand" class="mb-3" /></a>
        <h1 class="mb-1">Verify Your Email Address</h1>
        <p class="mb-4">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </p>
    </div>
    
    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success mb-4" role="alert">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif
    
    <div class="d-flex align-items-center justify-content-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    Resend Verification Email
                </button>
            </div>
        </form>
        
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link text-secondary">
                Log Out
            </button>
        </form>
    </div>
@endsection
