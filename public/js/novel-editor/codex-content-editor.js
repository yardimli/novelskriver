/**
 * NEW: Manages in-place editing for codex entry windows, including
 * debounced saving, a floating toolbar for text formatting, and AI text processing.
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
		const editable = target.closest('.js-codex-editable, .js-codex-title-input');
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
	
	// --- Floating Toolbar Logic ---
	// MODIFIED: The toolbar is now a single, global element in the document body.
	const toolbar = document.getElementById('codex-floating-toolbar');
	let currentSelection = null;
	
	if (toolbar) {
		// MODIFIED: Prevent toolbar buttons from stealing focus, which would hide the toolbar.
		toolbar.addEventListener('mousedown', (event) => {
			event.preventDefault();
		});
	}
	
	// Show/hide toolbar on text selection
	document.addEventListener('selectionchange', () => {
		if (!toolbar) return;
		
		const selection = document.getSelection();
		if (!selection || selection.rangeCount === 0) {
			return;
		}
		
		const range = selection.getRangeAt(0);
		const container = range.commonAncestorContainer.parentElement?.closest('.js-codex-editable');
		
		if (container && !selection.isCollapsed) {
			const windowContent = container.closest('.codex-entry-window-content');
			const entryId = windowContent.dataset.entryId;
			
			// Store the current entry's context on the toolbar itself.
			toolbar.dataset.entryId = entryId;
			currentSelection = selection;
			positionToolbar(toolbar, range);
			updateToolbarState(toolbar, selection);
			toolbar.classList.remove('hidden');
			
		} else {
			if (!toolbar.classList.contains('hidden')) {
				// Hide all dropdowns before hiding the toolbar
				toolbar.querySelectorAll('.js-highlight-dropdown, .js-ai-dropdown').forEach(d => d.classList.add('hidden'));
				toolbar.classList.add('hidden');
				currentSelection = null;
			}
		}
	});
	
	// MODIFIED: Listen on document.body as the toolbar is no longer a child of #desktop.
	document.body.addEventListener('click', (event) => {
		const target = event.target;
		
		// Standard formatting commands
		const editBtn = target.closest('.js-codex-edit-btn');
		if (editBtn && editBtn.dataset.command) {
			document.execCommand(editBtn.dataset.command, false, null);
			return;
		}
		
		// Highlight color selection
		const highlightOption = target.closest('.js-highlight-option');
		if (highlightOption) {
			applyHighlight(currentSelection, highlightOption.dataset.bg);
			highlightOption.closest('.js-highlight-dropdown').classList.add('hidden');
			return;
		}
		
		// Toggle dropdowns
		const highlightToggle = target.closest('.js-highlight-dropdown-container > button');
		if (highlightToggle) {
			toggleDropdown(highlightToggle.parentElement.querySelector('.js-highlight-dropdown'));
			return;
		}
		const aiToggle = target.closest('.js-ai-action-btn');
		if (aiToggle) {
			toggleDropdown(aiToggle.parentElement.querySelector('.js-ai-dropdown'));
			return;
		}
		
		// AI Apply button
		const aiApplyBtn = target.closest('.js-ai-apply-btn');
		if (aiApplyBtn) {
			handleAiAction(aiApplyBtn, currentSelection);
			aiApplyBtn.closest('.js-ai-dropdown').classList.add('hidden');
			return;
		}
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

/**
 * MODIFIED: Positions the floating toolbar above the selected text range, relative to the viewport.
 * @param {HTMLElement} toolbar - The toolbar element.
 * @param {Range} range - The selected text range.
 */
function positionToolbar(toolbar, range) {
	const rect = range.getBoundingClientRect();
	
	// Position relative to the viewport.
	let top = rect.top - toolbar.offsetHeight - 10;
	let left = rect.left + (rect.width / 2) - (toolbar.offsetWidth / 2);
	
	// Boundary checks to keep it on screen.
	if (top < 10) { // If it would go off the top, place it below the selection.
		top = rect.bottom + 10;
	}
	if (left < 10) { // Prevent going off-screen left.
		left = 10;
	}
	if (left + toolbar.offsetWidth > window.innerWidth - 10) { // Prevent going off-screen right.
		left = window.innerWidth - toolbar.offsetWidth - 10;
	}
	
	toolbar.style.top = `${top}px`;
	toolbar.style.left = `${left}px`;
}

/**
 * Updates the state of the toolbar (e.g., word count).
 * @param {HTMLElement} toolbar - The toolbar element.
 * @param {Selection} selection - The current text selection.
 */
function updateToolbarState(toolbar, selection) {
	const text = selection.toString().trim();
	const wordCount = text.length > 0 ? text.split(/\s+/).length : 0;
	const wordCountEl = toolbar.querySelector('.js-word-count');
	wordCountEl.textContent = `${wordCount} word${wordCount !== 1 ? 's' : ''} selected`;
}

/**
 * Toggles the visibility of a dropdown menu within the toolbar.
 * @param {HTMLElement} dropdown - The dropdown element to toggle.
 */
function toggleDropdown(dropdown) {
	const allDropdowns = dropdown.closest('.js-codex-toolbar').querySelectorAll('.js-highlight-dropdown, .js-ai-dropdown');
	allDropdowns.forEach(d => {
		if (d !== dropdown) {
			d.classList.add('hidden');
		}
	});
	dropdown.classList.toggle('hidden');
}

/**
 * Applies a background and text color highlight to the current selection.
 * @param {Selection} selection - The current text selection.
 * @param {string} highlightClass - The CSS class to apply for the highlight.
 */
function applyHighlight(selection, highlightClass) {
	if (!selection || selection.rangeCount === 0) return;
	
	// A more robust way than execCommand for highlighting.
	const range = selection.getRangeAt(0);
	const selectedText = range.extractContents();
	const span = document.createElement('span');
	
	if (highlightClass === 'transparent') {
		// This is to remove highlighting. We need to unwrap spans.
		// A simple implementation: just insert the text back without a span.
		const tempDiv = document.createElement('div');
		tempDiv.appendChild(selectedText);
		range.insertNode(document.createTextNode(tempDiv.textContent));
	} else {
		span.className = highlightClass;
		span.appendChild(selectedText);
		range.insertNode(span);
	}
	
	selection.removeAllRanges();
	selection.addRange(range);
}

/**
 * Handles the AI text processing action.
 * @param {HTMLButtonElement} button - The "Apply" button that was clicked.
 * @param {Selection} selection - The current text selection.
 */
async function handleAiAction(button, selection) {
	if (!selection || selection.isCollapsed) {
		alert('Please select some text to process.');
		return;
	}
	
	const text = selection.toString();
	const action = button.dataset.action;
	const dropdown = button.closest('.js-ai-dropdown');
	const model = dropdown.querySelector('.js-llm-model-select').value;
	// MODIFIED: Get the entry ID from the toolbar's dataset, as it's no longer inside the window.
	const entryId = button.closest('.js-codex-toolbar').dataset.entryId;
	
	button.textContent = '...';
	button.disabled = true;
	
	try {
		const response = await fetch(`/codex-entries/${entryId}/process-text`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
				'Accept': 'application/json',
			},
			body: JSON.stringify({ text, action, model }),
		});
		
		const data = await response.json();
		if (!response.ok) throw new Error(data.message || 'AI processing failed.');
		
		// Replace selected text with the AI response
		const range = selection.getRangeAt(0);
		range.deleteContents();
		range.insertNode(document.createTextNode(data.text));
	} catch (error) {
		console.error('AI Action Error:', error);
		alert(`Error: ${error.message}`);
	} finally {
		button.textContent = 'Apply';
		button.disabled = false;
	}
}
