/**
 * Codex Entry Window Interaction Manager
 *
 * Handles AI image generation and manual uploads for codex entry windows.
 * Uses event delegation on the main #desktop element to work with dynamically created windows.
 */
document.addEventListener('DOMContentLoaded', () => {
	const desktop = document.getElementById('desktop');
	if (!desktop) return;
	
	// --- Modal Management ---
	const openModal = (modal) => {
		if (modal) modal.classList.remove('hidden');
	};
	
	const closeModal = (modal) => {
		if (modal) {
			modal.classList.add('hidden');
			// Reset form state to be clean for next opening
			const form = modal.querySelector('form');
			if (form) {
				form.reset();
				// Special handling for upload preview and button state
				const previewContainer = form.querySelector('.js-image-preview-container');
				if (previewContainer) previewContainer.classList.add('hidden');
				const fileNameSpan = form.querySelector('.js-file-name');
				if (fileNameSpan) fileNameSpan.textContent = 'Click to select a file';
				const submitBtn = form.querySelector('button[type="submit"]');
				if (submitBtn) submitBtn.disabled = true;
			}
		}
	};
	
	/**
	 * Toggles the loading state of a form submission button.
	 * @param {HTMLButtonElement} button The button element.
	 * @param {boolean} isLoading True to show spinner, false to show text.
	 */
	const setButtonLoadingState = (button, isLoading) => {
		const text = button.querySelector('.js-btn-text');
		const spinner = button.querySelector('.js-spinner');
		if (isLoading) {
			button.disabled = true;
			text.classList.add('hidden');
			spinner.classList.remove('hidden');
		} else {
			button.disabled = false;
			text.classList.remove('hidden');
			spinner.classList.add('hidden');
		}
	};
	
	// --- Event Delegation for Modal Triggers and Closers ---
	desktop.addEventListener('click', (event) => {
		const target = event.target;
		// Find the parent window content, if any
		const windowEl = target.closest('.codex-entry-window-content');
		if (!windowEl) return;
		
		const entryId = windowEl.dataset.entryId;
		
		// Open AI Modal
		if (target.closest('.js-codex-generate-ai')) {
			const modal = document.getElementById(`ai-modal-${entryId}`);
			// Pre-fill prompt with a suggestion
			const textarea = modal.querySelector('textarea');
			textarea.value = `A detailed portrait of ${windowEl.dataset.entryTitle}, fantasy art.`;
			openModal(modal);
		}
		
		// Open Upload Modal
		if (target.closest('.js-codex-upload-image')) {
			const modal = document.getElementById(`upload-modal-${entryId}`);
			openModal(modal);
		}
		
		// Close any modal via its close button
		if (target.closest('.js-close-modal')) {
			const modal = target.closest('.js-ai-modal, .js-upload-modal');
			closeModal(modal);
		}
	});
	
	// --- AI Generation Form Submission ---
	desktop.addEventListener('submit', async (event) => {
		if (!event.target.matches('.js-ai-form')) return;
		event.preventDefault();
		
		const form = event.target;
		const modal = form.closest('.js-ai-modal');
		const entryId = modal.id.replace('ai-modal-', '');
		const windowEl = document.querySelector(`.codex-entry-window-content[data-entry-id="${entryId}"]`);
		const submitBtn = form.querySelector('.js-ai-submit-btn');
		const prompt = new FormData(form).get('prompt');
		
		if (!prompt || prompt.trim() === '') {
			alert('Please enter a prompt.');
			return;
		}
		
		setButtonLoadingState(submitBtn, true);
		const imageContainer = windowEl.querySelector('.codex-image-container');
		const imgEl = imageContainer.querySelector('img');
		imageContainer.classList.add('opacity-50');
		
		try {
			const response = await fetch(`/codex-entries/${entryId}/generate-image`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
					'Accept': 'application/json',
				},
				body: JSON.stringify({ prompt }),
			});
			
			const data = await response.json();
			if (!response.ok) throw new Error(data.message || 'An unknown error occurred.');
			
			imgEl.src = data.image_url + '?t=' + new Date().getTime();
			closeModal(modal);
			
		} catch (error) {
			console.error('AI Image Generation Error:', error);
			alert('Failed to generate image: ' + error.message);
		} finally {
			setButtonLoadingState(submitBtn, false);
			imageContainer.classList.remove('opacity-50');
		}
	});
	
	// --- Manual Upload Form Submission ---
	desktop.addEventListener('submit', async (event) => {
		if (!event.target.matches('.js-upload-form')) return;
		event.preventDefault();
		
		const form = event.target;
		const modal = form.closest('.js-upload-modal');
		const entryId = modal.id.replace('upload-modal-', '');
		const windowEl = document.querySelector(`.codex-entry-window-content[data-entry-id="${entryId}"]`);
		const submitBtn = form.querySelector('.js-upload-submit-btn');
		const formData = new FormData(form);
		
		setButtonLoadingState(submitBtn, true);
		const imageContainer = windowEl.querySelector('.codex-image-container');
		const imgEl = imageContainer.querySelector('img');
		imageContainer.classList.add('opacity-50');
		
		try {
			const response = await fetch(`/codex-entries/${entryId}/upload-image`, {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
					'Accept': 'application/json',
				},
				body: formData,
			});
			
			const data = await response.json();
			if (!response.ok) {
				const errorMsg = data.errors?.image?.[0] || data.message || 'Upload failed.';
				throw new Error(errorMsg);
			}
			
			imgEl.src = data.image_url + '?t=' + new Date().getTime();
			closeModal(modal);
			
		} catch (error) {
			console.error('Image Upload Error:', error);
			alert('Failed to upload image: ' + error.message);
		} finally {
			setButtonLoadingState(submitBtn, false);
			imageContainer.classList.remove('opacity-50');
		}
	});
	
	// --- File Input Change for Preview in Upload Modal ---
	desktop.addEventListener('change', (event) => {
		if (!event.target.matches('input[type="file"][name="image"]')) return;
		
		const input = event.target;
		const form = input.closest('form');
		const file = input.files[0];
		const previewContainer = form.querySelector('.js-image-preview-container');
		const previewImg = form.querySelector('.js-image-preview');
		const fileNameSpan = form.querySelector('.js-file-name');
		const submitBtn = form.querySelector('button[type="submit"]');
		
		if (file) {
			const reader = new FileReader();
			reader.onload = (e) => {
				previewImg.src = e.target.result;
				previewContainer.classList.remove('hidden');
			};
			reader.readAsDataURL(file);
			fileNameSpan.textContent = file.name;
			submitBtn.disabled = false;
		} else {
			previewContainer.classList.add('hidden');
			fileNameSpan.textContent = 'Click to select a file';
			submitBtn.disabled = true;
		}
	});
});
