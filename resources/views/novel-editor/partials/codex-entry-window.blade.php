<div class="p-4 flex flex-col h-full codex-entry-window-content select-text js-codex-drop-zone transition-colors duration-300" data-entry-id="{{ $codexEntry->id }}" data-entry-title="{{ $codexEntry->title }}">
	
	<div class="flex-grow flex gap-4 overflow-hidden">
		<div class="w-1/3 flex-shrink-0">
			<div class="codex-image-container aspect-square bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden transition-opacity duration-300">
				<img src="{{ $codexEntry->image_url }}" alt="Image for {{ $codexEntry->title }}" class="w-full h-full object-cover">
			</div>
			<div class="mt-2 space-y-2">
				<button type="button" class="js-codex-upload-image w-full text-sm px-3 py-1.5 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded-md transition-colors">Upload New Image</button>
				<button type="button" class="js-codex-generate-ai w-full text-sm px-3 py-1.5 bg-teal-500 hover:bg-teal-600 text-white rounded-md transition-colors">Generate with AI</button>
			</div>
		</div>
		
		{{-- Details Section --}}
		<div class="w-2/3 flex flex-col min-w-0">
			<div class="flex-shrink-0 prose-sm dark:prose-invert max-w-none">
				<input type="text" name="title" value="{{ $codexEntry->title }}" class="js-codex-title-input text-2xl font-bold w-full bg-transparent border-0 p-0 focus:ring-0 focus:border-b-2 focus:border-teal-500" placeholder="Codex Entry Title">
				
				<div class="js-codex-editable lead mt-2" data-name="description" contenteditable="true" data-placeholder="Enter a short summary...">{{ $codexEntry->description }}</div>
			</div>
			
			<div class="mt-4 flex-grow overflow-y-auto prose prose-sm dark:prose-invert max-w-none js-codex-editable" data-name="content" contenteditable="true" data-placeholder="Enter detailed content here...">
				@if($codexEntry->content)
					{!! $codexEntry->content !!}
				@else
					{{-- Empty for placeholder to work --}}
				@endif
			</div>
		</div>
	</div>
	
	{{-- Section to display linked codex entries as tags. --}}
	<div class="js-codex-tags-wrapper mt-4 flex-shrink-0 border-t border-gray-200 dark:border-gray-700 pt-3 @if($codexEntry->linkedEntries->isEmpty()) hidden @endif">
		<h4 class="text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-2">Linked Entries</h4>
		<div class="js-codex-tags-container flex flex-wrap gap-2">
			@foreach($codexEntry->linkedEntries as $entry)
				<div class="js-codex-tag group/tag relative inline-flex items-center gap-2 bg-gray-200 dark:bg-gray-700 rounded-full pr-2" data-entry-id="{{ $entry->id }}">
					<button type="button"
					        class="js-open-codex-entry flex items-center gap-2 pl-1 pr-2 py-1 rounded-full hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
					        data-entry-id="{{ $entry->id }}"
					        data-entry-title="{{ $entry->title }}">
						<img src="{{ $entry->thumbnail_url }}" alt="Thumbnail for {{ $entry->title }}" class="w-5 h-5 object-cover rounded-full flex-shrink-0">
						<span class="js-codex-tag-title text-xs font-medium">{{ $entry->title }}</span>
					</button>
					<button type="button"
					        class="js-remove-codex-codex-link absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover/tag:opacity-100 transition-opacity"
					        data-parent-entry-id="{{ $codexEntry->id }}"
					        data-entry-id="{{ $entry->id }}"
					        title="Unlink this entry">
						<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" viewBox="0 0 16 16">
							<path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
						</svg>
					</button>
				</div>
			@endforeach
		</div>
	</div>
	
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
