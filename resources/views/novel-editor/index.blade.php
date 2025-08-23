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
	<script type="module" src="{{ asset('/js/novel-editor/toolbar.js') }}"></script>
	<script src="{{ asset('/js/novel-editor/codex-entry-editor.js') }}"></script>
	
	@vite(['resources/css/editor.css'])

</head>
<body class="h-full bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 overflow-hidden select-none flex flex-col"
      data-novel-id="{{ $novel->id }}"
      data-editor-state="{{ json_encode($novel->editor_state) }}">

{{-- NEW: Top toolbar, always visible. --}}
<div id="top-toolbar" class="flex-shrink-0 h-12 bg-white/80 dark:bg-black/80 backdrop-blur-sm flex items-center px-4 gap-4 z-50 border-b border-gray-200 dark:border-gray-700">
	{{-- History Section --}}
	<div class="flex items-center gap-1">
		<button type="button" class="js-toolbar-btn" data-command="undo" title="Undo" disabled>
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z"/><path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z"/></svg>
		</button>
		<button type="button" class="js-toolbar-btn" data-command="redo" title="Redo" disabled>
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/><path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966a.25.25 0 0 1 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/></svg>
		</button>
	</div>
	<div class="w-px h-6 bg-gray-300 dark:bg-gray-600"></div> {{-- Divider --}}
	{{-- Formatting Section --}}
	<div class="flex items-center gap-1">
		<button type="button" class="js-toolbar-btn font-bold" data-command="bold" title="Bold" disabled>B</button>
		<button type="button" class="js-toolbar-btn italic" data-command="italic" title="Italic" disabled>I</button>
		<button type="button" class="js-toolbar-btn underline" data-command="underline" title="Underline" disabled>U</button>
		<div class="relative js-dropdown-container">
			<button type="button" class="js-toolbar-btn" title="Highlight" disabled>
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M15.823 2.215a.5.5 0 0 0-.693-.693l-1.359.235-2.533-2.533a.5.5 0 0 0-.707 0L8.294 1.453a.5.5 0 0 0 0 .707l2.533 2.533-.235 1.359a.5.5 0 0 0 .693.693l1.55-1.55 2.43 2.43a.5.5 0 0 0 .707 0l2.235-2.235a.5.5 0 0 0 0-.707L15.823 2.215zm-1.06 1.06-2.43-2.43 2.235-2.235 2.43 2.43-2.235 2.235z"/><path d="M1.25 9.25a.25.25 0 0 1 .25-.25h10.5a.25.25 0 0 1 0 .5H1.5a.25.25 0 0 1-.25-.25zM1.25 12.25a.25.25 0 0 1 .25-.25h10.5a.25.25 0 0 1 0 .5H1.5a.25.25 0 0 1-.25-.25zM1.25 15.25a.25.25 0 0 1 .25-.25h10.5a.25.25 0 0 1 0 .5H1.5a.25.25 0 0 1-.25-.25z"/></svg>
			</button>
			<div class="js-dropdown absolute top-full mt-2 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded p-1 hidden flex-col gap-1 w-32 shadow-lg">
				<button class="js-highlight-option p-1 rounded w-full text-left flex items-center gap-2 text-xs hover:bg-gray-100 dark:hover:bg-gray-700" data-bg="highlight-yellow">
					<span class="w-4 h-4 rounded-full" style="background-color: #fef08a;"></span> Yellow
				</button>
				<button class="js-highlight-option p-1 rounded w-full text-left flex items-center gap-2 text-xs hover:bg-gray-100 dark:hover:bg-gray-700" data-bg="highlight-green">
					<span class="w-4 h-4 rounded-full" style="background-color: #a7f3d0;"></span> Green
				</button>
				<button class="js-highlight-option p-1 rounded w-full text-left flex items-center gap-2 text-xs hover:bg-gray-100 dark:hover:bg-gray-700" data-bg="highlight-blue">
					<span class="w-4 h-4 rounded-full" style="background-color: #bfdbfe;"></span> Blue
				</button>
				<button class="js-highlight-option p-1 rounded w-full text-left flex items-center gap-2 text-xs hover:bg-gray-100 dark:hover:bg-gray-700" data-bg="highlight-red">
					<span class="w-4 h-4 rounded-full" style="background-color: #fecaca;"></span> Red
				</button>
				<button class="js-highlight-option p-1 rounded w-full text-left text-xs hover:bg-gray-100 dark:hover:bg-gray-700" data-bg="transparent">
					None
				</button>
			</div>
		</div>
	</div>
	<div class="w-px h-6 bg-gray-300 dark:bg-gray-600"></div> {{-- Divider --}}
	{{-- AI Tools Section --}}
	<div class="flex items-center gap-1">
		@foreach(['Expand', 'Rephrase', 'Shorten'] as $action)
			<div class="relative js-dropdown-container">
				<button type="button" class="js-toolbar-btn js-ai-action-btn text-sm px-3" disabled>{{ $action }}</button>
				<div class="js-dropdown absolute top-full mt-2 w-48 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded p-2 hidden flex-col gap-2 shadow-lg">
					<select class="js-llm-model-select text-black dark:text-white dark:bg-gray-700 text-xs rounded p-1 w-full">
						<option value="{{ env('OPEN_ROUTER_MODEL', 'openai/gpt-4o-mini') }}">Default Model</option>
						<option value="openai/gpt-4o-mini">GPT-4o Mini</option>
						<option value="anthropic/claude-3.5-sonnet">Claude 3.5 Sonnet</option>
						<option value="google/gemini-flash-1.5">Gemini 1.5 Flash</option>
					</select>
					<button class="js-ai-apply-btn bg-teal-500 hover:bg-teal-600 text-white rounded text-xs py-1.5 w-full" data-action="{{ strtolower($action) }}">Apply</button>
				</div>
			</div>
		@endforeach
	</div>
	<div class="flex-grow"></div> {{-- Spacer --}}
	{{-- Word Count Section --}}
	<div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
		<span id="js-word-count">No text selected</span>
	</div>
</div>

<div id="viewport" class="relative flex-grow z-10 overflow-hidden">
	<div id="desktop" class="absolute" style="width: 5000px; height: 5000px; transform-origin: 0 0;">
	</div>
</div>

<div id="taskbar" class="flex-shrink-0 h-12 bg-white/80 dark:bg-black/80 backdrop-blur-sm flex items-center px-2 gap-2 z-50 border-t border-gray-200 dark:border-gray-700">
	
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
	
	{{-- Zoom controls group with new 100% zoom button --}}
	<div class="ml-auto flex items-center gap-1">
		<button id="zoom-out-btn" type="button" title="Zoom Out" class="text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-sm p-2.5">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
		</button>
		<button id="zoom-100-btn" type="button" title="Zoom to 100%" class="text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-sm p-2.5">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 017.5 5.25h9a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25-2.25h-9a2.25 2.25 0 01-2.25-2.25v-9z" /></svg>
		</button>
		<button id="zoom-in-btn" type="button" title="Zoom In" class="text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-sm p-2.5">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
		</button>
		<button id="zoom-fit-btn" type="button" title="Fit to View" class="text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-sm p-2.5">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5M15 15l5.25 5.25" /></svg>
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

{{-- Modal for creating a new codex entry. --}}
<div id="new-codex-entry-modal" class="js-new-codex-modal fixed inset-0 bg-black/60 z-[9998] flex items-center justify-center p-4 hidden" aria-labelledby="new-codex-modal-title" role="dialog" aria-modal="true">
	<div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl overflow-hidden">
		<div class="p-4 border-b dark:border-gray-700 flex justify-between items-center">
			<h3 id="new-codex-modal-title" class="text-lg font-semibold">Create New Codex Entry</h3>
			<button type="button" class="js-close-new-codex-modal text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl leading-none" aria-label="Close">&times;</button>
		</div>
		<form id="new-codex-entry-form" novalidate>
			<div class="p-6 max-h-[70vh] overflow-y-auto space-y-4">
				{{-- General Error Message --}}
				<div id="new-codex-error-container" class="hidden p-3 bg-red-100 dark:bg-red-900/50 border border-red-400 dark:border-red-600 rounded-md text-sm text-red-700 dark:text-red-200"></div>
				
				{{-- Title --}}
				<div>
					<label for="new-codex-title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title <span class="text-red-500">*</span></label>
					<input type="text" id="new-codex-title" name="title" class="mt-1 block w-full rounded-md dark:bg-gray-700 dark:border-gray-600 focus:ring-teal-500 focus:border-teal-500" required>
					<p class="js-error-message mt-1 text-xs text-red-500 hidden"></p>
				</div>
				
				{{-- Category --}}
				<div>
					<label for="new-codex-category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category <span class="text-red-500">*</span></label>
					<select id="new-codex-category" name="codex_category_id" class="mt-1 block w-full rounded-md dark:bg-gray-700 dark:border-gray-600 focus:ring-teal-500 focus:border-teal-500">
						<option value="">Select a category...</option>
						@foreach($novel->codexCategories as $category)
							<option value="{{ $category->id }}">{{ $category->name }}</option>
						@endforeach
						<option value="new">-- Create New Category --</option>
					</select>
					<p class="js-error-message mt-1 text-xs text-red-500 hidden"></p>
				</div>
				
				{{-- New Category Name Input --}}
				<div id="new-category-wrapper" class="hidden pl-4 border-l-4 border-teal-500">
					<label for="new-category-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Category Name</label>
					<input type="text" id="new-category-name" name="new_category_name" class="mt-1 block w-full rounded-md dark:bg-gray-700 dark:border-gray-600 focus:ring-teal-500 focus:border-teal-500">
					<p class="js-error-message mt-1 text-xs text-red-500 hidden"></p>
				</div>
				
				{{-- Description --}}
				<div>
					<label for="new-codex-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description (Short Summary)</label>
					<textarea id="new-codex-description" name="description" rows="2" class="mt-1 block w-full rounded-md dark:bg-gray-700 dark:border-gray-600 focus:ring-teal-500 focus:border-teal-500"></textarea>
					<p class="js-error-message mt-1 text-xs text-red-500 hidden"></p>
				</div>
				
				{{-- Content --}}
				<div>
					<label for="new-codex-content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Content (Detailed Information)</label>
					<textarea id="new-codex-content" name="content" rows="5" class="mt-1 block w-full rounded-md dark:bg-gray-700 dark:border-gray-600 focus:ring-teal-500 focus:border-teal-500"></textarea>
					<p class="js-error-message mt-1 text-xs text-red-500 hidden"></p>
				</div>
				
				{{-- Image Upload --}}
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Image (Optional)</label>
					<div class="mt-1">
						<input type="file" id="new-codex-image" name="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 dark:file:bg-teal-900/50 dark:file:text-teal-300 dark:hover:file:bg-teal-900" accept="image/png, image/jpeg, image/gif, image/webp">
						<p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Image can be uploaded or generated with AI from the desktop later.</p>
						<p class="js-error-message mt-1 text-xs text-red-500 hidden"></p>
					</div>
				</div>
			</div>
			<div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-t dark:border-gray-700 flex justify-end items-center gap-3">
				<button type="button" class="js-close-new-codex-modal px-4 py-2 rounded-md text-sm bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600">Cancel</button>
				<button type="submit" class="js-new-codex-submit-btn bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-md text-sm flex items-center justify-center gap-2 w-28">
					<span class="js-btn-text">Create</span>
					<span class="js-spinner hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
				</button>
			</div>
		</form>
	</div>
</div>

</body>
</html>
