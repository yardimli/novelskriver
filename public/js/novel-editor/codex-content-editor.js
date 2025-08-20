/**
 * MODIFIED: Manages in-place editing for codex entry windows, specifically
 * the debounced saving of content. Toolbar logic is now in toolbar.js.
 */

// Debounce timer map to handle multiple windows independently.
const debounceTimers = new Map();

/**
 * Initializes the codex content editor functionality.
 * @param {HTMLElement} desktop - The main desktop element to attach listeners to.
 */
export function setupCodexContentEditor(desktop) {
	// --- Debounced Saving ---
	desktop.addEventListener('input', (event) => {
		const target = event.target;
		// MODIFIED: Listen for input on any contenteditable field, not just codex-specific ones.
		const editable = target.closest('[contenteditable="true"], .js-codex-title-input');
		if (!editable) return;
		
		const windowContent = editable.closest('.codex-entry-window-content');
		if (!windowContent) return;
		
		const entryId = windowContent.dataset.entryId;
		if (!entryId) return;
		
		// Clear existing timer for this specific entry
		if (debounceTimers.has(entryId)) {
			clearTimeout(debounceTimers.get(entryId));
		}
		
		// Set a new timer
		const timer = setTimeout(() => {
			saveCodexEntry(windowContent);
			debounceTimers.delete(entryId);
		}, 2000); // 2-second delay
		
		debounceTimers.set(entryId, timer);
	});
}

/**
 * Saves the content of a codex entry window via API call.
 * @param {HTMLElement} windowContent - The main content element of the codex window.
 */
async function saveCodexEntry(windowContent) {
	const entryId = windowContent.dataset.entryId;
	const titleInput = windowContent.querySelector('.js-codex-title-input');
	const descriptionDiv = windowContent.querySelector('.js-codex-editable[data-name="description"]');
	const contentDiv = windowContent.querySelector('.js-codex-editable[data-name="content"]');
	
	const data = {
		title: titleInput.value,
		description: descriptionDiv.innerText, // Use innerText to get clean text without HTML
		content: contentDiv.innerHTML, // Use innerHTML to preserve formatting
	};
	
	try {
		const response = await fetch(`/codex-entries/${entryId}`, {
			method: 'PATCH',
			headers: {
				'Content-Type': 'application/json',
				'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
				'Accept': 'application/json',
			},
			body: JSON.stringify(data),
		});
		
		if (!response.ok) {
			const errorData = await response.json();
			throw new Error(errorData.message || 'Failed to save codex entry.');
		}
		// console.log(`Codex entry ${entryId} saved.`); // Optional success feedback
	} catch (error) {
		console.error('Error saving codex entry:', error);
		alert('Error: Could not save changes to codex entry.');
	}
}
