{{-- This file contains the content for a single codex entry window. --}}
<div class="p-4 flex flex-col h-full codex-entry-window-content" data-entry-id="{{ $codexEntry->id }}" data-entry-title="{{ $codexEntry->title }}">
	<div class="flex-grow flex gap-4 overflow-y-auto">
		{{-- Image Section --}}
		<div class="w-1/3 flex-shrink-0">
			<div class="codex-image-container aspect-square bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden transition-opacity duration-300">
				<img src="{{ $codexEntry->image_url }}" alt="Image for {{ $codexEntry->title }}" class="w-full h-full object-cover">
			</div>
			<div class="mt-2 space-y-2">
				{{-- MODIFIED: This button now triggers the upload modal. --}}
				<button type="button" class="js-codex-upload-image w-full text-sm px-3 py-1.5 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded-md transition-colors">Upload New Image</button>
				{{-- MODIFIED: This button now triggers the AI generation modal. --}}
				<button type="button" class="js-codex-generate-ai w-full text-sm px-3 py-1.5 bg-teal-500 hover:bg-teal-600 text-white rounded-md transition-colors">Generate with AI</button>
			</div>
		</div>
		
		{{-- Details Section --}}
		<div class="w-2/3 prose prose-sm dark:prose-invert max-w-none">
			<h2>{{ $codexEntry->title }}</h2>
			@if($codexEntry->description)
				<p class="lead">{{ $codexEntry->description }}</p>
			@endif
			
			@if($codexEntry->content)
				{!! nl2br(e($codexEntry->content)) !!}
			@else
				<p class="text-gray-500 italic">No detailed content available.</p>
			@endif
		</div>
	</div>
	
	{{-- NEW: Modals for AI Generation and Image Upload --}}
	
	<!-- AI Generation Modal -->
	<div id="ai-modal-{{ $codexEntry->id }}" class="js-ai-modal fixed inset-0 bg-black/60 z-[9998] flex items-center justify-center p-4 hidden" aria-labelledby="ai-modal-title-{{ $codexEntry->id }}" role="dialog" aria-modal="true">
		<div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md overflow-hidden">
			<div class="p-4 border-b dark:border-gray-700 flex justify-between items-center">
				<h3 id="ai-modal-title-{{ $codexEntry->id }}" class="text-lg font-semibold">Generate Image with AI</h3>
				<button type="button" class="js-close-modal text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl leading-none" aria-label="Close">&times;</button>
			</div>
			<form class="js-ai-form" novalidate>
				<div class="p-6 space-y-4">
					<div>
						<label for="prompt-{{ $codexEntry->id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prompt</label>
						<textarea id="prompt-{{ $codexEntry->id }}" name="prompt" rows="4" class="mt-1 block w-full rounded-md dark:bg-gray-700 dark:border-gray-600 focus:ring-teal-500 focus:border-teal-500" placeholder="A detailed portrait of a character..."></textarea>
					</div>
				</div>
				<div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-t dark:border-gray-700 flex justify-end items-center gap-3">
					<button type="button" class="js-close-modal px-4 py-2 rounded-md text-sm bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600">Cancel</button>
					<button type="submit" class="js-ai-submit-btn bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-md text-sm flex items-center justify-center gap-2 w-28">
						<span class="js-btn-text">Generate</span>
						<span class="js-spinner hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
					</button>
				</div>
			</form>
		</div>
	</div>
	
	<!-- Image Upload Modal -->
	<div id="upload-modal-{{ $codexEntry->id }}" class="js-upload-modal fixed inset-0 bg-black/60 z-[9998] flex items-center justify-center p-4 hidden" aria-labelledby="upload-modal-title-{{ $codexEntry->id }}" role="dialog" aria-modal="true">
		<div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md overflow-hidden">
			<div class="p-4 border-b dark:border-gray-700 flex justify-between items-center">
				<h3 id="upload-modal-title-{{ $codexEntry->id }}" class="text-lg font-semibold">Upload New Image</h3>
				<button type="button" class="js-close-modal text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl leading-none" aria-label="Close">&times;</button>
			</div>
			<form class="js-upload-form" novalidate>
				<div class="p-6 space-y-4">
					<div class="js-image-preview-container hidden aspect-square w-1/2 mx-auto bg-gray-100 dark:bg-gray-700 rounded-md overflow-hidden">
						<img src="" alt="Image preview" class="js-image-preview w-full h-full object-cover">
					</div>
					<label for="image-upload-{{ $codexEntry->id }}" class="block w-full px-3 py-6 text-center border-2 border-dashed rounded-md cursor-pointer hover:border-gray-400 dark:border-gray-600 dark:hover:border-gray-500">
						<span class="js-file-name text-gray-600 dark:text-gray-400">Click to select a file</span>
						<input id="image-upload-{{ $codexEntry->id }}" name="image" type="file" class="hidden" accept="image/png, image/jpeg, image/gif, image/webp">
					</label>
					<p class="text-xs text-center text-gray-500">PNG, JPG, GIF, WEBP up to 2MB.</p>
				</div>
				<div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-t dark:border-gray-700 flex justify-end items-center gap-3">
					<button type="button" class="js-close-modal px-4 py-2 rounded-md text-sm bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600">Cancel</button>
					<button type="submit" class="js-upload-submit-btn bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-md text-sm flex items-center justify-center gap-2 w-28 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
						<span class="js-btn-text">Upload</span>
						<span class="js-spinner hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
