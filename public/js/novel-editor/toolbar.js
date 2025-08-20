/**
 * NEW: This module manages the top toolbar for text formatting and AI actions.
 * It listens for text selections and enables/disables buttons accordingly.
 */

let currentSelection = null;

/**
 * Initializes the top toolbar functionality.
 */
export function setupTopToolbar() {
	const toolbar = document.getElementById('top-toolbar');
	if (!toolbar) return;
	
	const allButtons = toolbar.querySelectorAll('.js-toolbar-btn');
	const wordCountEl = document.getElementById('js-word-count');
	
	// --- Selection Handling ---
	document.addEventListener('selectionchange', () => {
		const selection = document.getSelection();
		
		if (!selection || selection.rangeCount === 0) {
			disableAllButtons();
			return;
		}
		
		const range = selection.getRangeAt(0);
		const container = range.commonAncestorContainer.parentElement?.closest('[contenteditable="true"]');
		
		if (container && !selection.isCollapsed) {
			// Text is selected inside an editable area
			currentSelection = selection;
			allButtons.forEach(btn => (btn.disabled = false));
			
			// Store context for AI actions
			const windowContent = container.closest('.codex-entry-window-content');
			if (windowContent) {
				toolbar.dataset.entryId = windowContent.dataset.entryId;
			} else {
				delete toolbar.dataset.entryId;
			}
			
			// Update word count
			updateWordCount(selection);
			
		} else {
			// Selection is collapsed or outside an editable area
			currentSelection = null;
			disableAllButtons();
			updateWordCount(null);
			delete toolbar.dataset.entryId;
		}
	});
	
	// --- Button Click Handling ---
	toolbar.addEventListener('click', (event) => {
		const target = event.target;
		
		// Standard formatting commands
		const commandBtn = target.closest('.js-toolbar-btn[data-command]');
		if (commandBtn) {
			document.execCommand(commandBtn.dataset.command, false, null);
			return;
		}
		
		// Highlight color selection
		const highlightOption = target.closest('.js-highlight-option');
		if (highlightOption) {
			applyHighlight(currentSelection, highlightOption.dataset.bg);
			highlightOption.closest('.js-dropdown').classList.add('hidden');
			return;
		}
		
		// Toggle dropdowns
		const dropdownToggle = target.closest('.js-dropdown-container > .js-toolbar-btn');
		if (dropdownToggle) {
			toggleDropdown(dropdownToggle.parentElement.querySelector('.js-dropdown'));
			return;
		}
		
		// AI Apply button
		const aiApplyBtn = target.closest('.js-ai-apply-btn');
		if (aiApplyBtn) {
			handleAiAction(aiApplyBtn, currentSelection);
			aiApplyBtn.closest('.js-dropdown').classList.add('hidden');
			return;
		}
	});
	
	// Close dropdowns if clicking outside
	document.addEventListener('click', (event) => {
		if (!toolbar.contains(event.target)) {
			toolbar.querySelectorAll('.js-dropdown').forEach(d => d.classList.add('hidden'));
		}
	});
	
	function disableAllButtons() {
		allButtons.forEach(btn => (btn.disabled = true));
	}
	
	function updateWordCount(selection) {
		if (!wordCountEl) return;
		if (!selection) {
			wordCountEl.textContent = 'No text selected';
			return;
		}
		const text = selection.toString().trim();
		const count = text.length > 0 ? text.split(/\s+/).length : 0;
		wordCountEl.textContent = `${count} word${count !== 1 ? 's' : ''} selected`;
	}
}

/**
 * Toggles the visibility of a dropdown menu.
 * @param {HTMLElement} dropdown - The dropdown element to toggle.
 */
function toggleDropdown(dropdown) {
	const allDropdowns = dropdown.closest('#top-toolbar').querySelectorAll('.js-dropdown');
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
	
	const range = selection.getRangeAt(0);
	const selectedText = range.extractContents();
	const span = document.createElement('span');
	
	if (highlightClass === 'transparent') {
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
	const dropdown = button.closest('.js-dropdown');
	const model = dropdown.querySelector('.js-llm-model-select').value;
	const entryId = document.getElementById('top-toolbar').dataset.entryId;
	
	if (!entryId) {
		alert('Could not determine the context for this action. Please ensure you are editing a codex entry.');
		return;
	}
	
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
