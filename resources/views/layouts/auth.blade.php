<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="csrf-token" content="{{ csrf_token() }}">
	
	<!-- Favicon icon-->
	<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('theme/assets/images/favicon/apple-touch-icon.png') }}" />
	<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('theme/assets/images/favicon/favicon-32x32.png') }}" />
	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('theme/assets/images/favicon/favicon-16x16.png') }}" />
	<link rel="manifest" href="{{ asset('theme/assets/images/favicon/site.webmanifest') }}" />
	<link rel="mask-icon" href="{{ asset('theme/assets/images/favicon/block-safari-pinned-tab.svg') }}" color="#8b3dff" />
	<link rel="shortcut icon" href="{{ asset('theme/assets/images/favicon/favicon.ico') }}" />
	<meta name="msapplication-TileColor" content="#8b3dff" />
	<meta name="msapplication-config" content="{{ asset('theme/assets/images/favicon/tile.xml') }}" />
	
	<!-- Color modes -->
	<script src="{{ asset('theme/assets/js/vendors/color-modes.js') }}"></script>
	
	<!-- Libs CSS -->
	<link href="{{ asset('theme/assets/libs/simplebar/dist/simplebar.min.css') }}" rel="stylesheet" />
	<link href="{{ asset('theme/assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet" />
	
	<!-- Box icons -->
	<link rel="stylesheet" href="{{ asset('theme/assets/fonts/css/boxicons.min.css') }}" />
	
	<!-- Theme CSS -->
	<link rel="stylesheet" href="{{ asset('theme/assets/css/theme.min.css') }}">
	
	<title>@yield('title') - {{ config('app.name', 'NovelWriter') }}</title>
</head>
<body>
<main>
	<!-- Page Content -->
	<div class="position-relative h-100">
		<div class="container d-flex flex-wrap justify-content-center align-items-center vh-100 w-lg-50 position-lg-absolute">
			<div class="row justify-content-center">
				<div class="w-100 align-self-end col-12">
					@yield('content')
				</div>
			</div>
		</div>
		<div class="position-fixed top-0 end-0 w-50 h-100 d-none d-xl-block vh-100" style="background-image: url({{ asset('theme/assets/images/sign-in/authentication-img.jpg') }}); background-position: center; background-repeat: no-repeat; background-size: cover"></div>
	</div>
	
	<!-- Theme switcher -->
	<div class="position-absolute start-0 bottom-0 m-4">
		<div class="dropdown">
			<button class="btn btn-light btn-icon rounded-circle d-flex align-items-center" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme (auto)">
				<i class="bi theme-icon-active"></i>
				<span class="visually-hidden bs-theme-text">Toggle theme</span>
			</button>
			<ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bs-theme-text">
				<li>
					<button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
						<i class="bi theme-icon bi-sun-fill"></i>
						<span class="ms-2">Light</span>
					</button>
				</li>
				<li>
					<button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
						<i class="bi theme-icon bi-moon-stars-fill"></i>
						<span class="ms-2">Dark</span>
					</button>
				</li>
				<li>
					<button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
						<i class="bi theme-icon bi-circle-half"></i>
						<span class="ms-2">Auto</span>
					</button>
				</li>
			</ul>
		</div>
	</div>
</main>

<!-- Libs JS -->
<script src="{{ asset('theme/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('theme/assets/libs/simplebar/dist/simplebar.min.js') }}"></script>
<script src="{{ asset('theme/assets/libs/headhesive/dist/headhesive.min.js') }}"></script>

<!-- Theme JS -->
<script src="{{ asset('theme/assets/js/theme.min.js') }}"></script>
<script src="{{ asset('theme/assets/js/vendors/password.js') }}"></script>

@stack('scripts')
</body>
</html>
