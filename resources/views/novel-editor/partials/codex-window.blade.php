<div class="p-4 space-y-4">
	@forelse($novel->codexCategories as $category)
		<div id="codex-category-{{ $category->id }}">
			<h3 class="text-lg font-bold text-teal-600 dark:text-teal-400 sticky top-0 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm py-2 -mx-1 px-1">
				{{ $category->name }}
				<span class="js-codex-category-count text-sm font-normal text-gray-500 dark:text-gray-400 ml-2">({{ $category->entries_count }} {{ Str::plural('item', $category->entries_count) }})</span>
			</h3>
			<div class="js-codex-entries-list mt-2 space-y-2">
				@forelse($category->entries as $entry)
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
