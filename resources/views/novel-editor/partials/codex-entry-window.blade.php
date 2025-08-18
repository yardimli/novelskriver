{{-- NEW: This file contains the content for a single codex entry window. --}}
<div class="p-4 flex flex-col h-full codex-entry-window-content" data-entry-id="{{ $codexEntry->id }}" data-entry-title="{{ $codexEntry->title }}">
	<div class="flex-grow flex gap-4 overflow-y-auto">
		{{-- Image Section --}}
		<div class="w-1/3 flex-shrink-0">
			<div class="codex-image-container aspect-square bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden transition-opacity duration-300">
				<img src="{{ $codexEntry->image_url }}" alt="Image for {{ $codexEntry->title }}" class="w-full h-full object-cover">
			</div>
			<div class="mt-2 space-y-2">
				{{-- In a full implementation, an upload button would trigger a hidden file input --}}
				<button type="button" class="w-full text-sm px-3 py-1.5 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded-md transition-colors" disabled title="Upload feature coming soon">Upload New Image</button>
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
</div>
