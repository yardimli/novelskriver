@extends('layouts.auth')

@section('title', 'Confirm Password')

@section('content')
    <div class="text-center mb-7">
        <a href="{{ route('home') }}"><img src="{{ asset('theme/assets/images/logo/brand-icon.svg') }}" alt="brand" class="mb-3" /></a>
        <h1 class="mb-1">Confirm Password</h1>
        <p class="mb-4">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
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
    
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="password-field position-relative">
                <input type="password" class="form-control fakePassword" id="password" name="password" required autocomplete="current-password" autofocus />
                <span><i class="bi bi-eye-slash passwordToggler"></i></span>
            </div>
        </div>
        
        <div class="d-grid">
            <button class="btn btn-primary" type="submit">Confirm</button>
        </div>
    </form>
@endsection
