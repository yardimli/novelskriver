@extends('layouts.app')

@section('title', 'Profile - Novelskriver')

@php
    // A simple list of European countries
    $europeanCountries = ['Austria', 'Belgium', 'Bulgaria', 'Croatia', 'Cyprus', 'Czech Republic', 'Denmark', 'Estonia', 'Finland', 'France', 'Germany', 'Greece', 'Hungary', 'Ireland', 'Italy', 'Latvia', 'Lithuania', 'Luxembourg', 'Malta', 'Netherlands', 'Poland', 'Portugal', 'Romania', 'Slovakia', 'Slovenia', 'Spain', 'Sweden', 'United Kingdom'];
    // Split name for the form fields
    $nameParts = explode(' ', $user->name, 2);
    $firstName = $user->first_name ?? $nameParts[0] ?? '';
    $lastName = $user->last_name ?? ($nameParts[1] ?? '');
@endphp

@section('content')
    <section class="py-lg-7 py-5 bg-light-subtle">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-4">
                    {{-- User Info & Sidebar Navigation --}}
                    @include('dashboard.partials.sidebar', ['active' => 'profile'])
                </div>
                
                <div class="col-lg-9 col-md-8">
                    <div class="mb-4">
                        <h1 class="mb-0 h3">Profile</h1>
                    </div>
                    
                    @if (session('status') === 'profile-updated')
                        <div class="alert alert-success mb-4">
                            Profile saved successfully.
                        </div>
                    @endif
                    
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-lg-5">
                            <div class="mb-5">
                                <h4 class="mb-1">Profile Picture</h4>
                                <p class="mb-0 fs-6">Upload a picture to make your profile stand out and let people recognize you easily!</p>
                            </div>
                            <div class="d-flex align-items-center">
                                {{-- UPDATED IMG TAG --}}
                                <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=8b3dff&color=fff' }}" alt="avatar" class="avatar avatar-lg rounded-circle">
                                <div class="ms-4">
                                    <h5 class="mb-0">Your photo</h5>
                                    <small>Allowed *.jpeg, *.jpg, *.png, *.gif max size of 4 MB</small>
                                    {{-- Note: File upload logic for manual uploads needs to be implemented separately --}}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-lg-5">
                            <div class="mb-5">
                                <h4 class="mb-1">Account Information</h4>
                                <p class="mb-0 fs-6">Edit your personal information and address.</p>
                            </div>
                            <form class="row g-3" method="post" action="{{ route('profile.update') }}">
                                @csrf
                                @method('patch')
                                
                                <div class="col-lg-6 col-md-12">
                                    <label for="profileFirstNameInput" class="form-label">First Name</label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="profileFirstNameInput" name="first_name" value="{{ old('first_name', $firstName) }}" required>
                                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 col-md-12">
                                    <label for="profileLastNameInput" class="form-label">Last Name</label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="profileLastNameInput" name="last_name" value="{{ old('last_name', $lastName) }}" required>
                                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-12">
                                    <label for="profileEmailInput" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="profileEmailInput" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6">
                                    <label for="profilePhoneInput" class="form-label">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="profilePhoneInput" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+44 123 456 7890">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6">
                                    <label for="profileBirthdayInput" class="form-label">Birthday</label>
                                    <input type="date" class="form-control @error('birthday') is-invalid @enderror" id="profileBirthdayInput" name="birthday" value="{{ old('birthday', optional($user->birthday)->format('Y-m-d')) }}">
                                    @error('birthday')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-12">
                                    <label for="profileAddressInput" class="form-label">Address Line</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" id="profileAddressInput" name="address" value="{{ old('address', $user->address) }}">
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-3">
                                    <label for="profileCountryInput" class="form-label">Country</label>
                                    <select class="form-select @error('country') is-invalid @enderror" id="profileCountryInput" name="country">
                                        <option value="">Choose...</option>
                                        @foreach($europeanCountries as $country)
                                            <option value="{{ $country }}" @if(old('country', $user->country) == $country) selected @endif>{{ $country }}</option>
                                        @endforeach
                                    </select>
                                    @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-3">
                                    <label for="profileStateInput" class="form-label">State / Region</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" id="profileStateInput" name="state" value="{{ old('state', $user->state) }}">
                                    @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-3">
                                    <label for="profileCityInput" class="form-label">City</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" id="profileCityInput" name="city" value="{{ old('city', $user->city) }}">
                                    @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-3">
                                    <label for="profilezipInput" class="form-label">Zip/Code</label>
                                    <input type="text" class="form-control @error('zip') is-invalid @enderror" id="profilezipInput" name="zip" value="{{ old('zip', $user->zip) }}">
                                    @error('zip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 mt-4">
                                    <button class="btn btn-primary" type="submit">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
