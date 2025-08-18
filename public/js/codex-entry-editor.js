/**
 * Codex Entry Window Interaction Manager
 *
 * Handles AI image generation and manual uploads for codex entry windows.
 * Uses event delegation on the main #desktop element to work with dynamically created windows.
 */
document.addEventListener('DOMContentLoaded', () => {
	const desktop = document.getElementById('desktop');
	if (!desktop) return;
	
	// --- AI Image Generation ---
	desktop.addEventListener('click', async (event) => {
		const generateBtn = event.target.closest('.js-codex-generate-ai');
		if (!generateBtn) return;
		
		const windowEl = generateBtn.closest('.codex-entry-window-content');
		const entryId = windowEl.dataset.entryId;
		const prompt = window.prompt('Enter a prompt for the AI image:', `A detailed portrait of ${windowEl.dataset.entryTitle}, fantasy art.`);
		
		if (!prompt || prompt.trim() === '') return;
		
		const imageContainer = windowEl.querySelector('.codex-image-container');
		const imgEl = imageContainer.querySelector('img');
		const originalBtnContent = generateBtn.innerHTML;
		
		// Show loading state
		generateBtn.disabled = true;
		generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...';
		imageContainer.classList.add('opacity-50');
		
		try {
			const response = await fetch(`/codex-entries/${entryId}/generate-image`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
					'Accept': 'application/json',
				},
				body: JSON.stringify({ prompt: prompt }),
			});
			
			const data = await response.json();
			
			if (!response.ok) {
				throw new Error(data.message || 'An unknown error occurred.');
			}
			
			// Update image source with a cache-busting query parameter
			imgEl.src = data.image_url + '?t=' + new Date().getTime();
			
		} catch (error) {
			console.error('AI Image Generation Error:', error);
			alert('Failed to generate image: ' + error.message);
		} finally {
			// Restore button and image state
			generateBtn.disabled = false;
			generateBtn.innerHTML = originalBtnContent;
			imageContainer.classList.remove('opacity-50');
		}
	});
	
	// Note: Manual image upload functionality can be added here following a similar pattern.
	// 1. Add a file input.
	// 2. Listen for its 'change' event.
	// 3. Create a FormData object.
	// 4. POST it to a new '/codex-entries/{id}/upload-image' endpoint.
	// 5. Update the img src on success.
});
