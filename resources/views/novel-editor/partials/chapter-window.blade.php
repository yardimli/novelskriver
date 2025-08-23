<div class="p-4 flex flex-col h-full chapter-window-content select-text js-chapter-drop-zone transition-colors duration-300" data-chapter-id="{{ $chapter->id }}">
	<div class="prose prose-sm dark:prose-invert max-w-none flex-shrink-0">
		{{-- NEW: Display Act and Chapter number for context. --}}
		@if($chapter->section)
			<h3 class="text-sm font-semibold uppercase tracking-wider text-indigo-500 dark:text-indigo-400">
				Act {{ $chapter->section->order }} &ndash; Chapter {{ $chapter->order }}
			</h3>
		@endif
		<h2>{{ $chapter->title }}</h2>
		@if($chapter->summary)
			<p class="lead">{{ $chapter->summary }}</p>
		@endif
	</div>
	
	{{-- Section to display linked codex entries as tags. --}}
	<div class="js-codex-tags-wrapper mt-4 flex-shrink-0 border-t border-gray-200 dark:border-gray-700 pt-3 @if($chapter->codexEntries->isEmpty()) hidden @endif">
		<div class="js-codex-tags-container flex flex-wrap gap-2">
			@foreach($chapter->codexEntries as $entry)
				<div class="js-codex-tag group/tag relative inline-flex items-center gap-2 bg-gray-200 dark:bg-gray-700 rounded-full pr-2" data-entry-id="{{ $entry->id }}">
					{{-- This button opens the codex entry window --}}
					<button type="button"
					        class="js-open-codex-entry flex items-center gap-2 pl-1 pr-2 py-1 rounded-full hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
					        data-entry-id="{{ $entry->id }}"
					        data-entry-title="{{ $entry->title }}">
						<img src="{{ $entry->thumbnail_url }}" alt="Thumbnail for {{ $entry->title }}" class="w-5 h-5 object-cover rounded-full flex-shrink-0">
						<span class="js-codex-tag-title text-xs font-medium">{{ $entry->title }}</span>
					</button>
					{{-- This button removes the link, appearing on hover --}}
					<button type="button"
					        class="js-remove-codex-link absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover/tag:opacity-100 transition-opacity"
					        data-chapter-id="{{ $chapter->id }}"
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
	
	<div class="flex-grow">
	</div>
</div>
