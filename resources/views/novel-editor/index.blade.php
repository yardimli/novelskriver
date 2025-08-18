<!doctype html>
{{-- MODIFIED: Removed lang attribute and added a class for theme management. --}}
<html class="">
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
	
	{{-- NEW: Inline script to set theme from localStorage immediately, preventing FOUC. --}}
	<script>
		if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
			document.documentElement.classList.add('dark');
		} else {
			document.documentElement.classList.remove('dark');
		}
	</script>
	
	{{-- REMOVED: The old Bootstrap color-modes.js is no longer needed. --}}
	
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
	
	{{-- NEW: Theme switcher button added to the right side of the taskbar. --}}
	<button id="theme-toggle" type="button" class="ml-auto text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-sm p-2.5">
		<svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
		<svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zM5 11a1 1 0 100-2H4a1 1 0 100 2h1zM8 16a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1z"></path></svg>
	</button>
</div>

{{-- Hidden templates for window content, which will be read by JavaScript --}}
<template id="outline-window-template">
	@include('novel-editor.partials.outline-window', ['novel' => $novel])
</template>

<template id="codex-window-template">
	@include('novel-editor.partials.codex-window', ['novel' => $novel])
</template>

</body>
{{-- MODIFIED: Added closing html tag for W3C compliance. --}}
</html>
