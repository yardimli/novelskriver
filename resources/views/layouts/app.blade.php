<!doctype html>
<html lang="en">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	
	<link rel="stylesheet" href="{{ asset('theme/assets/libs/swiper/swiper-bundle.min.css') }}" />
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
	
	<!-- Scroll Cue -->
	<link rel="stylesheet" href="{{ asset('theme/assets/libs/scrollcue/scrollCue.css') }}" />
	
	<!-- Box icons -->
	<link rel="stylesheet" href="{{ asset('theme/assets/fonts/css/boxicons.min.css') }}" />
	
	<!-- Theme CSS -->
	<link rel="stylesheet" href="{{ asset('theme/assets/css/theme.min.css') }}">
	
	<title>@yield('title', 'Landing AI Studio - Responsive Website Template')</title>
</head>
<body>

@include('partials.header')

<main>
	@yield('content')
</main>

@include('partials.footer')

</body>
</html>
