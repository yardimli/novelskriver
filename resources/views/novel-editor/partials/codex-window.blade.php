<div class="p-4 space-y-4">
	@forelse($novel->codexCategories as $category)
		<div>
			{{-- MODIFIED: Adjusted sticky header background for better visibility in both modes. --}}
			<h3 class="text-lg font-bold text-teal-600 dark:text-teal-400 sticky top-0 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm py-2 -mx-1 px-1">
				{{ $category->name }}
				<span class="text-sm font-normal text-gray-500 dark:text-gray-400 ml-2">({{ $category->entries_count }} {{ Str::plural('item', $category->entries_count) }})</span>
			</h3>
			<div class="mt-2 space-y-2">
				@forelse($category->entries as $entry)
					{{-- MODIFIED: Changed div to a button to make it clickable and added data attributes. --}}
					<button type="button"
					        class="js-open-codex-entry w-full text-left p-2 rounded bg-gray-100 dark:bg-gray-700/50 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500"
					        data-entry-id="{{ $entry->id }}"
					        data-entry-title="{{ $entry->title }}">
						<h4 class="font-semibold">{{ $entry->title }}</h4>
						@if($entry->description)
							<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $entry->description }}</p>
						@endif
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
