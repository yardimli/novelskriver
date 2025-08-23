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
					<div class="d-flex justify-content-between align-items-center mb-4">
						<div>
							<h1 class="mb-0 h3">Hey, {{ Auth::user()->name }}! Welcome to Novelskriver.</h1>
						</div>
						<a href="{{ route('novels.create') }}" class="btn btn-dark">
							<i class="bi bi-plus-circle me-1"></i> Create New
						</a>
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
					
					@if ($novelsWithoutSeries->isEmpty() && $seriesWithNovels->isEmpty())
						<div class="text-center p-5 border rounded bg-white">
							<h4>You haven't started any novels yet.</h4>
							<p class="text-muted">Ready to begin your next masterpiece?</p>
							<a href="{{ route('novels.create') }}" class="btn btn-primary mt-3">Create Your First Novel</a>
						</div>
					@else
						<!-- Novels without a series -->
						@if ($novelsWithoutSeries->isNotEmpty())
							<div class="mb-3">
								<h4 class="mb-0">Standalone Novels</h4>
							</div>
							
							<div class="row g-4 mb-5">
								@foreach ($novelsWithoutSeries as $novel)
									<div class="col-lg-4 col-md-6">
										<div class="card border-0 shadow-sm h-100 d-flex flex-column">
											<div class="card-body d-flex flex-column">
												@php
													$coverImage = $novel->images->first();
												@endphp
												<img
													src="{{ $coverImage && $coverImage->thumbnail_local_path ? Illuminate\Support\Facades\Storage::url($coverImage->thumbnail_local_path) : asset('/images/book-placeholder.png') }}"
													class="novel-card-img-top w-100 mb-3" alt="{{ $novel->title }} Cover" style="aspect-ratio: 2/3; object-fit: cover; border-radius: 0.25rem;">
												<div class="flex-grow-1">
													<h5 class="novel-card-title">{{ $novel->title }}</h5>
													<p class="novel-card-date mt-0 text-muted small">by {{ $novel->author }}</p>
												</div>
												<div class="mt-auto pt-3 border-top">
													@if($novel->chapters_count === 0)
														<button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#fillNovelModal" data-novel-id="{{ $novel->id }}" data-novel-title="{{ $novel->title }}">
															<i class="bi bi-magic me-2"></i>Fill with AI
														</button>
													@else
														<a href="{{ route('novels.edit', $novel) }}" class="btn btn-outline-secondary w-100">
															<i class="bi bi-pencil-square me-2"></i>Edit Novel
														</a>
													@endif
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
								<h4 class="mb-0">{{ $group['series']->title }}</h4>
							</div>
							
							<div class="row g-4 mb-5">
								@foreach ($group['novels'] as $novel)
									<div class="col-lg-4 col-md-6">
										<div class="card border-0 shadow-sm h-100 d-flex flex-column">
											<div class="card-body d-flex flex-column">
												@php
													$coverImage = $novel->images->first();
												@endphp
												<img
													src="{{ $coverImage && $coverImage->thumbnail_local_path ? Illuminate\Support\Facades\Storage::url($coverImage->thumbnail_local_path) : asset('/images/book-placeholder.png') }}"
													class="novel-card-img-top w-100 mb-3" alt="{{ $novel->title }} Cover" style="aspect-ratio: 2/3; object-fit: cover; border-radius: 0.25rem;">
												<div class="flex-grow-1">
													<h5 class="novel-card-title">{{ $novel->title }}</h5>
													<p class="novel-card-date mt-0 text-muted small">by {{ $novel->author }}</p>
												</div>
												<div class="mt-auto pt-3 border-top">
													@if($novel->chapters_count === 0)
														<button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#fillNovelModal" data-novel-id="{{ $novel->id }}" data-novel-title="{{ $novel->title }}">
															<i class="bi bi-magic me-2"></i>Fill with AI
														</button>
													@else
														<a href="{{ route('novels.edit', $novel) }}" class="btn btn-outline-secondary w-100">
															<i class="bi bi-pencil-square me-2"></i>Edit Novel
														</a>
													@endif
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
	
	{{-- NEW: Include the modal for filling novel structure --}}
	@include('partials.fill-novel-modal')
@endsection

@push('scripts')
	{{-- NEW: JavaScript to handle the modal and form submission --}}
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			const fillNovelModal = document.getElementById('fillNovelModal');
			if (!fillNovelModal) return;
			
			const generateBtn = document.getElementById('generateStructureBtn');
			const form = document.getElementById('fillNovelForm');
			const errorAlert = document.getElementById('fill-error-alert');
			const csrfToken = '{{ csrf_token() }}';
			
			fillNovelModal.addEventListener('show.bs.modal', function (event) {
				const button = event.relatedTarget;
				const novelId = button.getAttribute('data-novel-id');
				const novelTitle = button.getAttribute('data-novel-title');
				
				const modalNovelIdInput = fillNovelModal.querySelector('#modal_novel_id');
				const modalNovelTitle = fillNovelModal.querySelector('#modal_novel_title');
				
				modalNovelIdInput.value = novelId;
				modalNovelTitle.textContent = novelTitle;
				errorAlert.classList.add('d-none'); // Hide error on modal open
			});
			
			generateBtn.addEventListener('click', function() {
				const novelId = document.getElementById('modal_novel_id').value;
				if (!novelId) {
					showError('Novel ID is missing. Please close the modal and try again.');
					return;
				}
				
				const formData = new FormData(form);
				const data = Object.fromEntries(formData.entries());
				
				setLoading(true);
				
				fetch(`/novels/${novelId}/generate-structure`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': csrfToken,
						'Accept': 'application/json',
					},
					body: JSON.stringify(data),
				})
					.then(async response => {
						const responseData = await response.json();
						if (!response.ok) {
							throw responseData;
						}
						return responseData;
					})
					.then(data => {
						if (data.success) {
							// Success! Reload the page to see the changes.
							window.location.reload();
						} else {
							showError(data.message || 'An unknown error occurred.');
						}
					})
					.catch(error => {
						console.error('Error:', error);
						const errorMessage = error.message || (error.errors ? Object.values(error.errors).flat().join(' ') : 'An unexpected error occurred. Check the console for details.');
						showError(errorMessage);
					})
					.finally(() => {
						setLoading(false);
					});
			});
			
			function setLoading(isLoading) {
				const spinner = generateBtn.querySelector('.spinner-border');
				if (isLoading) {
					generateBtn.disabled = true;
					spinner.classList.remove('d-none');
					errorAlert.classList.add('d-none');
				} else {
					generateBtn.disabled = false;
					spinner.classList.add('d-none');
				}
			}
			
			function showError(message) {
				errorAlert.textContent = message;
				errorAlert.classList.remove('d-none');
			}
		});
	</script>
@endpush
