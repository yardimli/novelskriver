@extends('layouts.app')

@section('title', 'Dashboard - Novelskriver')

@section('content')
	<section class="py-lg-7 py-5 bg-light-subtle">
		<div class="container">
			<div class="row">
				<div class="col-lg-3 col-md-4">
					@include('dashboard.partials.sidebar', ['active' => 'home'])
				</div>
				
				<div class="col-lg-9 col-md-8">
					{{-- Welcome Header --}}
					<div class="mb-4">
						<h1 class="mb-0 h3">Hey, {{ Auth::user()->name }}! Welcome to Novelskriver.</h1>
					</div>
					
					@if (session('success'))
						<div class="alert alert-success mb-4" role="alert">
							{{ session('success') }}
						</div>
					@endif
					
					
					{{-- Book Stats Section --}}
					<div class="mb-5">
						<h4 class="mb-1">Your Writing Stats</h4>
						<p class="mb-0 fs-6">An overview of your writing progress and achievements. Keep up the great work!</p>
					</div>
					<div class="row mb-5 g-4">
						<div class="col-lg-4 col-md-6">
							<div class="card border-0 shadow-sm">
								<div class="card-body">
									<span>Active Projects</span>
									<h3 class="mb-0 mt-4">3</h3>
								</div>
							</div>
						</div>
						<div class="col-lg-4 col-md-6">
							<div class="card border-0 shadow-sm">
								<div class="card-body">
									<span>Words Written Today</span>
									<h3 class="mb-0 mt-4">1,250</h3>
								</div>
							</div>
						</div>
						<div class="col-lg-4 col-md-6">
							<div class="card border-0 shadow-sm">
								<div class="card-body">
									<span>Total Word Count</span>
									<h3 class="mb-0 mt-4">152,890</h3>
								</div>
							</div>
						</div>
					</div>
					
					{{-- Book Projects Section --}}
					<div class="mb-3">
						<h4 class="mb-0">Continue Your Work</h4>
					</div>
					<div class="row g-4 mb-5">
						<div class="col-lg-4 col-md-6">
							<div class="card border-0 shadow-sm h-100 d-flex flex-column">
								<div class="card-body d-flex flex-column">
									<div class="mb-4">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
										     class="bi bi-book text-primary" viewBox="0 0 16 16">
											<path
												d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.746c-.917-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
										</svg>
									</div>
									<div class="mb-4 flex-grow-1">
										<h5 class="mb-2">The Crimson Cipher</h5>
										<p class="mb-0">Continue writing your epic fantasy novel.</p>
									</div>
									<a href="#" class="icon-link icon-link-hover text-inherit">Open Project
										<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor"
										     class="bi bi-arrow-right" viewBox="0 0 16 16">
											<path fill-rule="evenodd"
											      d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
										</svg>
									</a>
								</div>
							</div>
						</div>
						<div class="col-lg-4 col-md-6">
							<div class="card border-0 shadow-sm h-100 d-flex flex-column">
								<div class="card-body d-flex flex-column">
									<div class="mb-4">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
										     class="bi bi-journal-plus text-success" viewBox="0 0 16 16">
											<path fill-rule="evenodd"
											      d="M8 5.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V10a.5.5 0 0 1-1 0V8.5H6a.5.5 0 0 1 0-1h1.5V6a.5.5 0 0 1 .5-.5z"/>
											<path
												d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2z"/>
											<path
												d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1z"/>
										</svg>
									</div>
									<div class="mb-4 flex-grow-1">
										<h5 class="mb-2">Start a New Project</h5>
										<p class="mb-0">Ready for a new adventure? Begin your next masterpiece.</p>
									</div>
									<a href="#" class="icon-link icon-link-hover text-inherit">Create New
										<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor"
										     class="bi bi-arrow-right" viewBox="0 0 16 16">
											<path fill-rule="evenodd"
											      d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
										</svg>
									</a>
								</div>
							</div>
						</div>
						<div class="col-lg-4 col-md-6">
							<div class="card border-0 shadow-sm h-100 d-flex flex-column">
								<div class="card-body d-flex flex-column">
									<div class="mb-4">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
										     class="bi bi-compass text-warning" viewBox="0 0 16 16">
											<path
												d="M8 16.016a7.5 7.5 0 0 0 1.962-14.74A1 1 0 0 0 9 0H7a1 1 0 0 0-.962 1.276A7.5 7.5 0 0 0 8 16.016zm6.5-7.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z"/>
											<path d="m6.94 7.44 4.95-2.83-2.83 4.95-4.949 2.83 2.828-4.95z"/>
										</svg>
									</div>
									<div class="mb-4 flex-grow-1">
										<h5 class="mb-2">Explore Worldbuilding</h5>
										<p class="mb-0">Flesh out your universe, characters, and lore in the Codex.</p>
									</div>
									<a href="#" class="icon-link icon-link-hover text-inherit">Go to Codex
										<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor"
										     class="bi bi-arrow-right" viewBox="0 0 16 16">
											<path fill-rule="evenodd"
											      d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
										</svg>
									</a>
								</div>
							</div>
						</div>
					</div>
					
					@if ($novelsWithoutSeries->isEmpty() && $seriesWithNovels->isEmpty())
						<div class="empty-state">
							<i class="bi bi-journal-bookmark"></i>
							<h4>You haven't started any novels yet.</h4>
							<p>Ready to begin your next masterpiece?</p>
							<a href="{{ route('novels.create') }}" class="btn btn-dark mt-2">Create Your First Novel</a>
						</div>
					@else
						<!-- Novels without a series -->
						@if ($novelsWithoutSeries->isNotEmpty())
							<div class="mb-3">
								<h4 class="mb-0">No Series
									({{ $novelsWithoutSeries->count() }} {{ Str::plural('book', $novelsWithoutSeries->count()) }})</h4>
							</div>
							
							<div class="row g-4 mb-5">
								@foreach ($novelsWithoutSeries as $novel)
									<div class="col-lg-4 col-md-6 mb-4">
										<div class="card border-0 shadow-sm h-100 d-flex flex-column">
											<div class="card-body d-flex flex-column">
												<div class="novel-card">
													@php
														// Get the first image associated with the novel to use as a cover.
														$coverImage = $novel->images->first();
													@endphp
													<img
														src="{{ $coverImage && $coverImage->thumbnail_local_path ? Illuminate\Support\Facades\Storage::url($coverImage->thumbnail_local_path) : asset('/images/book-placeholder.png') }}"
														class="novel-card-img-top w-100 mb-3" alt="{{ $novel->title }} Cover">
													<div class="mb-4 flex-grow-1">
														<h5 class="novel-card-title">{{ $novel->title }}</h5>
														<p class="novel-card-date mt-0">{{ $novel->created_at->format('F jS') }}</p>
													</div>
												</div>
											</div>
										</div>
									</div>
								@endforeach
							</div>
						@endif
						
						<!-- Novels in series -->
						@foreach ($seriesWithNovels as $group)
							<div class="mb-3">
								<h4 class="mb-0">{{ $group['series']->title }}
									({{ $group['novels']->count() }} {{ Str::plural('book', $group['novels']->count()) }})</h4>
							</div>
							
							<div class="row g-4 mb-5">
								@foreach ($group['novels'] as $novel)
									<div class="col-lg-4 col-md-6 mb-4">
										<div class="card border-0 shadow-sm h-100 d-flex flex-column">
											<div class="card-body d-flex flex-column">
												<div class="novel-card">
													@php
														// Get the first image associated with the novel to use as a cover.
														$coverImage = $novel->images->first();
													@endphp
													<img
														src="{{ $coverImage && $coverImage->thumbnail_local_path ? Illuminate\Support\Facades\Storage::url($coverImage->thumbnail_local_path) : asset('/images/book-placeholder.png') }}"
														class="novel-card-img-top w-100 mb-3" alt="{{ $novel->title }} Cover">
													<div class="mb-4 flex-grow-1">
														<h5 class="novel-card-title">{{ $novel->title }}</h5>
														<p class="novel-card-date mt-0">{{ $novel->created_at->format('F jS') }}</p>
													</div>
												</div>
											</div>
										</div>
									</div>
								@endforeach
							</div>
						@endforeach
					@endif
				
				</div>
			</div>
		
		
		</div>
	</section>
@endsection
