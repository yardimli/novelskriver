<div class="modal fade" id="fillNovelModal" tabindex="-1" aria-labelledby="fillNovelModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="fillNovelModalLabel">Fill Novel Structure with AI</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="fillNovelForm" onsubmit="return false;">
					<input type="hidden" name="novel_id" id="modal_novel_id">
					<p class="text-muted">Generating structure for: <strong id="modal_novel_title" class="text-dark"></strong></p>
					
					<div class="mb-3">
						<label for="book_about" class="form-label">What is the book about?</label>
						<textarea class="form-control" id="book_about" name="book_about" rows="4" placeholder="e.g., A space detective investigates a murder on a Mars colony, only to uncover a conspiracy that threatens all of humanity." required></textarea>
					</div>
					
					<div class="row">
						<div class="col-md-6 mb-3">
							<label for="book_structure" class="form-label">Book Structure</label>
							<select class="form-select" id="book_structure" name="book_structure" required>
								@forelse($structures as $file => $name)
									<option value="{{ $file }}">{{ Str::title(str_replace(['-', '_'], ' ', $name)) }}</option>
								@empty
									<option value="" disabled>No structure files found.</option>
								@endforelse
							</select>
						</div>
						<div class="col-md-6 mb-3">
							<label for="language" class="form-label">Language</label>
							<select class="form-select" id="language" name="language" required>
								@foreach($languages as $lang)
									<option value="{{ $lang }}" {{ $lang === 'English' ? 'selected' : '' }}>{{ $lang }}</option>
								@endforeach
							</select>
						</div>
					</div>
					
					<div class="mb-3">
						<label for="llm_model" class="form-label">LLM Model</label>
						<select class="form-select" id="llm_model" name="llm_model" required>
							@forelse($llmModels as $model)
								<option value="{{ $model }}" {{ $model === env('OPEN_ROUTER_MODEL', 'openai/gpt-4o-mini') ? 'selected' : '' }}>{{ $model }}</option>
							@empty
								<option value="" disabled>No models available.</option>
							@endforelse
						</select>
					</div>
					<div id="fill-error-alert" class="alert alert-danger d-none mt-3" role="alert"></div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="generateStructureBtn">
					<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
					Generate
				</button>
			</div>
		</div>
	</div>
</div>
