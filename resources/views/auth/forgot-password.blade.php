@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <div class="text-center mb-7">
        <a href="{{ route('home') }}"><img src="{{ asset('theme/assets/images/logo/brand-icon.svg') }}" alt="brand" class="mb-3" /></a>
        <h1 class="mb-1">Forgot Password?</h1>
        <p class="mb-4">
            {{ __('No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </p>
    </div>
    
    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success mb-3" role="alert">
            {{ session('status') }}
        </div>
    @endif
    
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
    
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label"> Email <span class="text-danger">*</span> </label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus />
        </div>
        
        <div class="d-grid mb-4">
            <button class="btn btn-primary" type="submit">Email Password Reset Link</button>
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
