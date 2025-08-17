@extends('layouts.app')

@section('title', 'Create a new Novel')

@push('styles')
	<style>
      .form-container {
          max-width: 700px;
          margin: 0 auto;
      }
      .book-details-card {
          background-color: var(--bs-card-bg);
          border: 1px solid var(--bs-border-color);
          border-radius: 0.5rem;
          padding: 2rem;
      }
      .input-group .btn {
          border-color: var(--bs-border-color);
      }
      .series-index-input {
          max-width: 150px;
      }
	</style>
@endpush

@section('content')
	<section class="py-5 py-md-8">
		<div class="container">
			<div class="form-container">
				<div class="text-center mb-5">
					<h1 class="display-5 fw-bold">Create a new Novel</h1>
					<p class="lead text-muted">Wonderful! You're about to write your next masterpiece, so let's get started.</p>
				</div>
				
				<form action="{{ route('novels.store') }}" method="POST">
					@csrf
					<div class="book-details-card shadow-sm">
						<h4 class="mb-4 text-uppercase fw-bold" style="font-size: 0.9rem; letter-spacing: 1px;">Book Details</h4>
						
						<!-- Title -->
						<div class="mb-4">
							<label for="title" class="form-label">Title</label>
							<div class="input-group">
								<input type="text" class="form-control form-control-lg" id="title" name="title" value="{{ old('title') }}" required>
								<button class="btn btn-outline-secondary" type="button" id="surprise-me-btn">Surprise me</button>
							</div>
							@error('title')
							<div class="text-danger mt-1 small">{{ $message }}</div>
							@enderror
						</div>
						
						<!-- Author -->
						<div class="mb-4">
							<label for="author" class="form-label">Author / Pen name</label>
							<div class="input-group">
								<input type="text" class="form-control form-control-lg" id="author" name="author" value="{{ old('author', $user->name) }}" required>
								<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Select</button>
								<ul class="dropdown-menu dropdown-menu-end">
									@forelse ($authorList as $authorName)
										<li><a class="dropdown-item" href="#">{{ $authorName }}</a></li>
									@empty
										<li><span class="dropdown-item disabled">No previous authors found.</span></li>
									@endforelse
									@if($authorList->isNotEmpty())
										<li><hr class="dropdown-divider"></li>
										<li><a class="dropdown-item text-danger" id="clear-author-btn" href="#">Clear Selection</a></li>
									@endif
								</ul>
							</div>
							@error('author')
							<div class="text-danger mt-1 small">{{ $message }}</div>
							@enderror
						</div>
						
						<!-- Series -->
						<div class="row align-items-end">
							<div class="col-sm-7 mb-3 mb-sm-0">
								<label for="series_id" class="form-label">Series (optional)</label>
								<div class="input-group">
									<select class="form-select form-select-lg" id="series_id" name="series_id">
										<option value="">—</option>
										@foreach ($seriesList as $series)
											<option value="{{ $series->id }}" {{ old('series_id') == $series->id ? 'selected' : '' }}>{{ $series->title }}</option>
										@endforeach
									</select>
									<button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#newSeriesModal">New Series</button>
								</div>
								@error('series_id')
								<div class="text-danger mt-1 small">{{ $message }}</div>
								@enderror
							</div>
							<div class="col-sm-5">
								<label for="series_index" class="form-label">Series index</label>
								<input type="text" class="form-control form-control-lg series-index-input" id="series_index" name="series_index" value="{{ old('series_index') }}" placeholder="Book #">
								@error('series_index')
								<div class="text-danger mt-1 small">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>
					
					<div class="d-flex justify-content-between mt-5">
						<a href="{{ url()->previous(route('dashboard')) }}" class="btn btn-light btn-lg">
							<i class="bi bi-arrow-left me-1"></i>Back
						</a>
						<button type="submit" class="btn btn-dark btn-lg">
							<i class="bi bi-journal-check me-2"></i>Create Novel
						</button>
					</div>
				</form>
			</div>
		</div>
	</section>
	
	<!-- New Series Modal -->
	<div class="modal fade" id="newSeriesModal" tabindex="-1" aria-labelledby="newSeriesModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="newSeriesModalLabel">Create a New Series</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form id="newSeriesForm" onsubmit="return false;">
						<div class="mb-3">
							<label for="new_series_title" class="form-label">Series Title</label>
							<input type="text" class="form-control" id="new_series_title" required>
							<div class="invalid-feedback" id="newSeriesError"></div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id="saveNewSeriesBtn">Save Series</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('scripts')
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			const csrfToken = '{{ csrf_token() }}';
			
			// Surprise Me - Generate Title
			const surpriseMeBtn = document.getElementById('surprise-me-btn');
			const titleInput = document.getElementById('title');
			surpriseMeBtn.addEventListener('click', function () {
				this.disabled = true;
				this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
				fetch('{{ route("novels.generate-title") }}', {
					method: 'POST',
					headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
				})
					.then(response => response.json())
					.then(data => {
						if (data.title) titleInput.value = data.title;
						else console.error(data.error || 'An unknown error occurred.');
					})
					.catch(error => console.error('Error:', error))
					.finally(() => {
						this.disabled = false;
						this.innerHTML = 'Surprise me';
					});
			});
			
			// Author Dropdown Logic
			const authorInput = document.getElementById('author');
			document.querySelectorAll('.dropdown-menu a.dropdown-item').forEach(item => {
				item.addEventListener('click', function(e) {
					e.preventDefault();
					if(this.id === 'clear-author-btn') {
						authorInput.value = '';
					} else {
						authorInput.value = this.textContent;
					}
				});
			});
			
			// New Series Modal Logic
			const saveNewSeriesBtn = document.getElementById('saveNewSeriesBtn');
			const newSeriesModal = new bootstrap.Modal(document.getElementById('newSeriesModal'));
			const newSeriesTitleInput = document.getElementById('new_series_title');
			const seriesSelect = document.getElementById('series_id');
			const newSeriesError = document.getElementById('newSeriesError');
			
			saveNewSeriesBtn.addEventListener('click', function () {
				const title = newSeriesTitleInput.value.trim();
				if (!title) {
					newSeriesTitleInput.classList.add('is-invalid');
					newSeriesError.textContent = 'Series title cannot be empty.';
					return;
				}
				this.disabled = true;
				this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
				fetch('{{ route("series.store") }}', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
					body: JSON.stringify({ title: title })
				})
					.then(async response => {
						const data = await response.json();
						if (!response.ok) throw data;
						return data;
					})
					.then(data => {
						seriesSelect.appendChild(new Option(data.title, data.id, true, true));
						newSeriesTitleInput.value = '';
						newSeriesTitleInput.classList.remove('is-invalid');
						newSeriesModal.hide();
					})
					.catch(error => {
						newSeriesTitleInput.classList.add('is-invalid');
						newSeriesError.textContent = (error.errors && error.errors.title) ? error.errors.title[0] : 'An unexpected error occurred.';
					})
					.finally(() => {
						this.disabled = false;
						this.innerHTML = 'Save Series';
					});
			});
			document.getElementById('newSeriesModal').addEventListener('hidden.bs.modal', function () {
				newSeriesTitleInput.value = '';
				newSeriesTitleInput.classList.remove('is-invalid');
			});
		});
	</script>
@endpush
