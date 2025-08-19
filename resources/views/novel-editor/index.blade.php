<!doctype html>
<html class="h-full">
<head>
	<meta charset="utf-t" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	
	<title>Editing: {{ $novel->title }} - Novelskriver</title>
	
	<!-- Favicon icon-->
	<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/theme/assets/images/favicon/apple-touch-icon.png') }}" />
	<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/theme/assets/images/favicon/favicon-32x32.png') }}" />
	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('/theme/assets/images/favicon/favicon-16x16.png') }}" />
	<link rel="manifest" href="{{ asset('/theme/assets/images/favicon/site.webmanifest') }}" />
	<link rel="mask-icon" href="{{ asset('/theme/assets/images/favicon/block-safari-pinned-tab.svg') }}" color="#8b3dff" />
	<link rel="shortcut icon" href="{{ asset('/theme/assets/images/favicon/favicon.ico') }}" />
	<meta name="msapplication-TileColor" content="#8b3dff" />
	<meta name="msapplication-config" content="{{ asset('/theme/assets/images/favicon/tile.xml') }}" />
	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">
	
	<script>
		if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
			document.documentElement.classList.add('dark');
		} else {
			document.documentElement.classList.remove('dark');
		}
	</script>
	
	<!-- Libs CSS -->
	<link href="{{ asset('theme/assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet" />
	<link rel="stylesheet" href="{{ asset('theme/assets/fonts/css/boxicons.min.css') }}" />
	
	<script type="module" src="{{ asset('/js/novel-editor/main.js') }}"></script>
	<script src="{{ asset('/js/codex-entry-editor.js') }}"></script>
	
	@vite(['resources/css/editor.css'])

</head>
{{-- MODIFIED: Added data-editor-state attribute to pass initial state to JavaScript. --}}
<body class="h-full bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 overflow-hidden"
      data-novel-id="{{ $novel->id }}"
      data-editor-state="{{ json_encode($novel->editor_state) }}">

{{-- MODIFIED: The main container is now a "viewport" which clips the oversized desktop.
     It has overflow-hidden to act as the camera frame for the canvas. --}}
<div id="viewport" class="relative w-full h-full z-10 overflow-hidden">
	{{-- MODIFIED: The "desktop" is now a huge, absolutely positioned canvas inside the viewport.
	     Its position and scale will be manipulated by JavaScript for panning and zooming. --}}
	<div id="desktop" class="absolute" style="width: 5000px; height: 5000px; transform-origin: 0 0;">
		{{-- Windows will be dynamically inserted here by JavaScript --}}
	</div>
</div>

<div id="taskbar" class="absolute bottom-0 left-0 w-full h-12 bg-white/80 dark:bg-black/80 backdrop-blur-sm flex items-center px-2 gap-2 z-50 border-t border-gray-200 dark:border-gray-700">
	
	{{-- "Open Windows" menu button and popup. --}}
	<div class="relative">
		<button id="open-windows-btn" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-sm p-2.5">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25A2.25 2.25 0 0 1 5.25 3h9.75a2.25 2.25 0 0 1 2.25 2.25Z" /></svg>
		</button>
		<div id="open-windows-menu" class="hidden absolute bottom-full mb-2 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden z-60">
			<ul id="open-windows-list" class="max-h-80 overflow-y-auto">
				{{-- Window list will be populated by JS --}}
			</ul>
		</div>
	</div>
	
	<div id="minimized-windows-container" class="flex items-center gap-2 flex-grow min-w-0">
		{{-- Minimized windows will be dynamically inserted here --}}
	</div>
	
	{{-- NEW: Zoom controls group --}}
	<div class="ml-auto flex items-center gap-1">
		<button id="zoom-out-btn" type="button" title="Zoom Out" class="text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-sm p-2.5">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
		</button>
		<button id="zoom-fit-btn" type="button" title="Fit to View" class="text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-sm p-2.5">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5M15 15l5.25 5.25" /></svg>
		</button>
		<button id="zoom-in-btn" type="button" title="Zoom In" class="text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-sm p-2.5">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
		</button>
		
		{{-- Theme switcher button --}}
		<button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-sm p-2.5">
			<svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
			<svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zM5 11a1 1 0 100-2H4a1 1 0 100 2h1zM8 16a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1z"></path></svg>
		</button>
	</div>
</div>

{{-- Hidden templates for window content, which will be read by JavaScript --}}
<template id="outline-window-template">
	@include('novel-editor.partials.outline-window', ['novel' => $novel])
</template>

<template id="codex-window-template">
	@include('novel-editor.partials.codex-window', ['novel' => $novel])
</template>

</body>
</html>
