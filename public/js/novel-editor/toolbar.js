/**
 * NEW: Manages the top toolbar for text editing within contenteditable fields.
 * Handles formatting, highlighting, AI actions, and selection state.
 */

let currentSelectionRange = null;
let activeEditable = null;
const toolbar = document.getElementById('top-toolbar');
const wordCountEl = document.getElementById('js-word-count');

/**
 * Initializes the top toolbar functionality.
 */
export function setupTopToolbar() {
	if (!toolbar) return;
	
	// Listen for selection changes to update toolbar state
	document.addEventListener('selectionchange', handleSelectionChange);
	
	// Add click handlers for toolbar buttons
	toolbar.addEventListener('click', handleToolbarClick);
	
	// Dropdown handling for highlight and AI menus
	document.body.addEventListener('click', handleDropdowns);
}

/**
 * Handles selection changes to update toolbar UI and track the active editable area.
 */
function handleSelectionChange() {
	const selection = window.getSelection();
	// If no selection or selection is collapsed (just a cursor)
	if (!selection || selection.rangeCount === 0 || selection.isCollapsed) {
		updateToolbarState(null);
		currentSelectionRange = null;
		activeEditable = null;
		return;
	}
	
	const range = selection.getRangeAt(0);
	const editable = range.startContainer.parentElement.closest('[contenteditable="true"]');
	
	if (editable) {
		currentSelectionRange = range;
		activeEditable = editable;
		updateToolbarState(range);
	} else {
		// Selection is outside an editable area
		updateToolbarState(null);
		currentSelectionRange = null;
		activeEditable = null;
	}
}

/**
 * Updates the enabled/disabled state of toolbar buttons and the word count.
 * @param {Range|null} range The current selection range, or null if none.
 */
function updateToolbarState(range) {
	const formatBtns = toolbar.querySelectorAll('[data-command]');
	const aiBtns = toolbar.querySelectorAll('.js-ai-action-btn');
	const highlightBtn = toolbar.querySelector('button[title="Highlight"]');
	
	if (range && activeEditable) {
		// Enable all buttons
		formatBtns.forEach(btn => { btn.disabled = false; });
		aiBtns.forEach(btn => { btn.disabled = false; });
		if (highlightBtn) highlightBtn.disabled = false;
		
		// Update word count
		const text = range.toString().trim();
		const words = text.split(/\s+/).filter(Boolean);
		wordCountEl.textContent = `${words.length} word${words.length !== 1 ? 's' : ''} selected`;
		
	} else {
		// Disable all buttons and reset word count
		formatBtns.forEach(btn => { btn.disabled = true; });
		aiBtns.forEach(btn => { btn.disabled = true; });
		if (highlightBtn) highlightBtn.disabled = true;
		wordCountEl.textContent = 'No text selected';
	}
}

/**
 * Main click handler for all buttons on the toolbar.
 * @param {MouseEvent} event
 */
async function handleToolbarClick(event) {
	const button = event.target.closest('button');
	if (!button || !activeEditable) return;
	
	// Restore selection before executing a command, as clicks can change it.
	const selection = window.getSelection();
	if (currentSelectionRange) {
		selection.removeAllRanges();
		selection.addRange(currentSelectionRange);
	}
	
	if (button.dataset.command) {
		document.execCommand(button.dataset.command, false, null);
	} else if (button.classList.contains('js-highlight-option')) {
		handleHighlight(button.dataset.bg);
		closeAllDropdowns();
	} else if (button.classList.contains('js-ai-apply-btn')) {
		await handleAiAction(button);
		closeAllDropdowns();
	}
	
	// After command, re-focus the editable area and update toolbar state
	activeEditable.focus();
	handleSelectionChange();
}

/**
 * Applies or removes a highlight class to the selected text.
 * This uses a non-destructive method to preserve other formatting like bold/italic.
 * @param {string} bgColorClass The CSS class for the highlight, or 'transparent' to remove.
 */
function handleHighlight(bgColorClass) {
	// Use a neutral, unique color with execCommand. This preserves other formatting.
	const uniqueColor = '#000001'; // An unlikely color (black with 1 blue).
	document.execCommand('backColor', false, uniqueColor);
	
	if (!activeEditable) return;
	
	// Find all spans created by execCommand (they will have the unique color)
	// and replace their inline style with the desired class.
	const spans = activeEditable.querySelectorAll(`span[style*="background-color: rgb(0, 0, 1)"]`);
	spans.forEach(span => {
		span.removeAttribute('style');
		// Remove any old highlight classes to prevent conflicts
		span.className = span.className.replace(/highlight-\w+/g, '').trim();
		if (bgColorClass !== 'transparent') {
			span.classList.add(bgColorClass);
		}
	});
	
	// Clean up by unwrapping any spans that are now empty or classless.
	activeEditable.querySelectorAll('span:not([class])').forEach(emptySpan => {
		unwrap(emptySpan);
	});
}

/**
 * Helper function to remove a wrapper element (like a <span>) and keep its children.
 * @param {HTMLElement} el The element to unwrap.
 */
function unwrap(el) {
	const parent = el.parentNode;
	if (!parent) return;
	while (el.firstChild) {
		parent.insertBefore(el.firstChild, el);
	}
	parent.removeChild(el);
}

/**
 * Handles the click on an AI action "Apply" button.
 * @param {HTMLButtonElement} button The clicked button.
 */
async function handleAiAction(button) {
	const action = button.dataset.action;
	const dropdown = button.closest('.js-dropdown');
	const modelSelect = dropdown.querySelector('.js-llm-model-select');
	const model = modelSelect.value;
	const text = currentSelectionRange.toString();
	const entryId = activeEditable.closest('.codex-entry-window-content')?.dataset.entryId;
	
	if (!action || !model || !text || !entryId) {
		alert('Could not perform AI action. Missing required information.');
		return;
	}
	
	button.disabled = true;
	button.textContent = 'Processing...';
	
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
		
		// Replace selected text with the new text from the AI
		document.execCommand('insertText', false, data.text);
		
	} catch (error) {
		console.error('AI Action Error:', error);
		alert(`Error: ${error.message}`);
	} finally {
		button.disabled = false;
		button.textContent = 'Apply';
	}
}

/**
 * Manages the opening and closing of dropdown menus in the toolbar.
 * @param {MouseEvent} event
 */
function handleDropdowns(event) {
	const dropdownContainer = event.target.closest('.js-dropdown-container');
	if (dropdownContainer) {
		const dropdown = dropdownContainer.querySelector('.js-dropdown');
		const isOpening = dropdown.classList.contains('hidden');
		closeAllDropdowns();
		if (isOpening) {
			dropdown.classList.remove('hidden');
		}
	} else {
		closeAllDropdowns();
	}
}

/**
 * Helper to close all open dropdowns.
 */
function closeAllDropdowns() {
	toolbar.querySelectorAll('.js-dropdown').forEach(d => d.classList.add('hidden'));
}
