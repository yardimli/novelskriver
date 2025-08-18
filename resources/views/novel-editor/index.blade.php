<!doctype html>
{{-- MODIFIED: Added html tag with lang attribute to enable theme switching. --}}
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	
	<title>Editing: {{ $novel->title }} - Novelskriver</title>
	
	<!-- Favicon icon-->
	<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('theme/assets/images/favicon/apple-touch-icon.png') }}" />
	<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('theme/assets/images/favicon/favicon-32x32.png') }}" />
	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('theme/assets/images/favicon/favicon-16x16.png') }}" />
	<link rel="manifest" href="{{ asset('theme/assets/images/favicon/site.webmanifest') }}" />
	<link rel="mask-icon" href="{{ asset('theme/assets/images/favicon/block-safari-pinned-tab.svg') }}" color="#8b3dff" />
	<link rel="shortcut icon" href="{{ asset('theme/assets/images/favicon/favicon.ico') }}" />
	<meta name="msapplication-TileColor" content="#8b3dff" />
	<meta name="msapplication-config" content="{{ asset('theme/assets/images/favicon/tile.xml') }}" />
	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">
	
	<!-- Color modes - to respect the website's dark/light theme -->
	<script src="{{ asset('theme/assets/js/vendors/color-modes.js') }}"></script>
	
	<!-- Libs CSS -->
	<link href="{{ asset('theme/assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet" />
	<link rel="stylesheet" href="{{ asset('theme/assets/fonts/css/boxicons.min.css') }}" />
	
	<script src="{{ asset('/js/novel-editor.js') }}"></script>
	
	@vite(['resources/css/editor.css'])

</head>
<body class="h-full bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 overflow-hidden">

{{-- The main "desktop" area where windows will live --}}
<div id="desktop" class="relative w-full h-full">
	{{-- Windows will be dynamically inserted here by JavaScript --}}
</div>

{{-- The "taskbar" at the bottom for minimized windows --}}
<div id="taskbar" class="absolute bottom-0 left-0 w-full h-12 bg-white/80 dark:bg-black/80 backdrop-blur-sm flex items-center px-2 gap-2 z-50 border-t border-gray-200 dark:border-gray-700">
	{{-- Minimized windows will be dynamically inserted here --}}
</div>

{{-- Hidden templates for window content, which will be read by JavaScript --}}
<template id="outline-window-template">
	@include('novel-editor.partials.outline-window', ['novel' => $novel])
</template>

<template id="codex-window-template">
	@include('novel-editor.partials.codex-window', ['novel' => $novel])
</template>

</body>
{{-- MODIFIED: Added closing html tag. --}}
</html>
