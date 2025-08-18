<div class="p-4 space-y-4">
	@forelse($novel->sections as $section)
		{{-- MODIFIED: Added hover effect for better interactivity. --}}
		<div class="p-3 rounded-lg bg-gray-100 dark:bg-gray-700/50 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
			<h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ $section->order }}. {{ $section->title }}</h3>
			@if($section->description)
				<p class="text-sm italic text-gray-600 dark:text-gray-400 mt-1">{{ $section->description }}</p>
			@endif
			
			<div class="mt-3 pl-4 border-l-2 border-gray-300 dark:border-gray-600 space-y-2">
				@forelse($section->chapters as $chapter)
					{{-- MODIFIED: Added hover effect for better interactivity. --}}
					<div class="p-2 rounded bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
						<h4 class="font-semibold">{{ $chapter->order }}. {{ $chapter->title }}</h4>
						@if($chapter->summary)
							<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $chapter->summary }}</p>
						@endif
					</div>
				@empty
					<p class="text-sm text-gray-500">No chapters in this section yet.</p>
				@endforelse
			</div>
		</div>
	@empty
		<p class="text-center text-gray-500">No sections found for this novel.</p>
	@endforelse
</div>
