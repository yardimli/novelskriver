{{-- MODIFIED: Added a button to open the "New Codex Entry" modal. --}}
<div class="p-4 space-y-4">
	<div class="px-1 pb-2 border-b border-gray-200 dark:border-gray-700">
		<button type="button" class="js-open-new-codex-modal w-full text-sm px-3 py-1.5 bg-teal-500 hover:bg-teal-600 text-white rounded-md transition-colors flex items-center justify-center gap-2">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
				<path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
			</svg>
			New Entry
		</button>
	</div>
	
	@forelse($novel->codexCategories as $category)
		{{-- MODIFIED: Added an ID to the category container for easier targeting by JS. --}}
		<div id="codex-category-{{ $category->id }}">
			<h3 class="text-lg font-bold text-teal-600 dark:text-teal-400 sticky top-0 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm py-2 -mx-1 px-1">
				{{ $category->name }}
				{{-- MODIFIED: Added a span with a class for the count to be updatable by JS. --}}
				<span class="js-codex-category-count text-sm font-normal text-gray-500 dark:text-gray-400 ml-2">({{ $category->entries_count }} {{ Str::plural('item', $category->entries_count) }})</span>
			</h3>
			{{-- MODIFIED: Added a class to the entries list container for easier targeting by JS. --}}
			<div class="js-codex-entries-list mt-2 space-y-2">
				@forelse($category->entries as $entry)
					{{-- MODIFIED: Made this button draggable to allow linking to chapters. --}}
					<button type="button"
					        class="js-open-codex-entry js-draggable-codex w-full text-left p-2 rounded bg-gray-100 dark:bg-gray-700/50 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500 flex items-start gap-3"
					        data-entry-id="{{ $entry->id }}"
					        data-entry-title="{{ $entry->title }}"
					        draggable="true">
						@if($entry->image)
							<img src="{{ $entry->thumbnail_url }}" alt="Thumbnail for {{ $entry->title }}" class="w-12 h-12 object-cover rounded flex-shrink-0 bg-gray-300 dark:bg-gray-600 pointer-events-none"> {{-- pointer-events-none on image helps drag event fire on button --}}
						@endif
						<div class="flex-grow min-w-0 pointer-events-none"> {{-- pointer-events-none on children helps drag event fire on button --}}
							<h4 class="font-semibold truncate">{{ $entry->title }}</h4>
							@if($entry->description)
								<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $entry->description }}</p>
							@endif
						</div>
					</button>
				@empty
					<p class="text-sm text-gray-500 px-2">No entries in this category yet.</p>
				@endforelse
			</div>
		</div>
	@empty
		<p class="text-center text-gray-500">No codex categories found for this novel.</p>
	@endforelse
</div>
