{{-- This file contains the content for a single chapter window. --}}
{{-- MODIFIED: Added `select-text` to re-enable text selection within this window's content area. --}}
<div class="p-4 flex flex-col h-full chapter-window-content select-text" data-chapter-id="{{ $chapter->id }}">
	<div class="prose prose-sm dark:prose-invert max-w-none flex-shrink-0">
		<h2>{{ $chapter->title }}</h2>
		@if($chapter->summary)
			<p class="lead">{{ $chapter->summary }}</p>
		@endif
	</div>
	
	<div class="mt-4 flex-grow overflow-y-auto border-t border-gray-200 dark:border-gray-700 pt-4">
		<h3 class="text-base font-semibold mb-2">Beats</h3>
		@forelse($chapter->beats as $beat)
			<div class="p-2 rounded bg-gray-100 dark:bg-gray-800/50 mb-2">
				{{-- Assuming beat content is plain text. Use {!! !!} if it contains HTML. --}}
				<p>{{ $beat->content }}</p>
			</div>
		@empty
			<p class="text-gray-500 italic">No beats have been written for this chapter yet.</p>
		@endforelse
	</div>
</div>
