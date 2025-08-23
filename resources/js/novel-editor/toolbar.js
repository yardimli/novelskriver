/**
 * Manages the top toolbar for text editing within ProseMirror editors.
 * Handles formatting, highlighting, AI actions, and selection state.
 */

// MODIFIED: Removed schema import to break circular dependency.
import { toggleMark, setBlockType, wrapIn } from 'prosemirror-commands';
import { history, undo, redo } from 'prosemirror-history';
import { wrapInList } from 'prosemirror-schema-list';

let activeEditorView = null;
const toolbar = document.getElementById('top-toolbar');
const wordCountEl = document.getElementById('js-word-count');

/**
 * Updates the toolbar's state based on the active editor's state.
 * This function is exported and called by the editor manager when focus or selection changes.
 * @param {import('prosemirror-view').EditorView | null} view The active ProseMirror EditorView or null.
 */
export function updateToolbarState(view) {
	activeEditorView = view;
	const allBtns = toolbar.querySelectorAll('.js-toolbar-btn');
	
	const isMarkActive = (state, type) => {
		if (!type) return false;
		const { from, $from, to, empty } = state.selection;
		if (empty) {
			// Check stored marks for the cursor position.
			return !!(state.storedMarks || $from.marks()).some(mark => mark.type === type);
		}
		// Check if the mark exists anywhere in the selected range.
		return state.doc.rangeHasMark(from, to, type);
	};
	
	if (view && view.state) {
		const { state } = view;
		const { schema } = state; // MODIFIED: Get schema from the active editor's state.
		const { from, to, empty, $from } = state.selection;
		
		const isTextSelected = !empty;
		
		// Enable/disable buttons and set active state
		allBtns.forEach(btn => {
			const cmd = btn.dataset.command;
			let commandFn, markType;
			
			// Determine command function for enabling/disabling and mark type for active state
			switch (cmd) {
				case 'undo': btn.disabled = !undo(state); return;
				case 'redo': btn.disabled = !redo(state); return;
				case 'bold': markType = schema.marks.strong; commandFn = toggleMark(markType); break;
				case 'italic': markType = schema.marks.em; commandFn = toggleMark(markType); break;
				case 'underline': markType = schema.marks.underline; commandFn = toggleMark(markType); break;
				case 'strike': markType = schema.marks.strike; commandFn = toggleMark(markType); break;
				case 'blockquote': commandFn = wrapIn(schema.nodes.blockquote); break;
				case 'bullet_list': commandFn = wrapInList(schema.nodes.bullet_list); break;
				case 'ordered_list': commandFn = wrapInList(schema.nodes.ordered_list); break;
				case 'horizontal_rule':
					// Special case, enabled if selection exists to replace it.
					// This command can run even with an empty selection.
					btn.disabled = !((state, dispatch) => {
						if (dispatch) dispatch(state.tr.replaceSelectionWith(schema.nodes.horizontal_rule.create()));
						return true;
					})(state);
					return;
			}
			
			// General disable logic for selection-based dropdowns
			if (btn.closest('.js-dropdown-container') || btn.classList.contains('js-ai-action-btn')) {
				btn.disabled = !isTextSelected;
			}
			
			// Disable based on command executability
			if (commandFn) {
				btn.disabled = !commandFn(state);
			}
			
			// Set active state for marks
			if (markType) {
				btn.classList.toggle('active', isMarkActive(state, markType));
			}
		});
		
		// Update heading dropdown text and disabled state
		const headingBtn = toolbar.querySelector('.js-heading-btn');
		if (headingBtn) {
			const parent = $from.parent;
			if (parent.type.name === 'heading') {
				headingBtn.textContent = `Heading ${parent.attrs.level}`;
			} else {
				headingBtn.textContent = 'Paragraph';
			}
			headingBtn.disabled = !setBlockType(schema.nodes.paragraph)(state) && !setBlockType(schema.nodes.heading, { level: 1 })(state);
		}
		
		// Update word count for the selection.
		if (isTextSelected) {
			const text = state.doc.textBetween(from, to, ' ');
			const words = text.trim().split(/\s+/).filter(Boolean);
			wordCountEl.textContent = `${words.length} word${words.length !== 1 ? 's' : ''} selected`;
		} else {
			wordCountEl.textContent = 'No text selected';
		}
		
	} else {
		// No active editor, so disable all buttons and reset word count.
		allBtns.forEach(btn => { btn.disabled = true; });
		const headingBtn = toolbar.querySelector('.js-heading-btn');
		if (headingBtn) headingBtn.textContent = 'Paragraph';
		wordCountEl.textContent = 'No text selected';
	}
}

/**
 * Applies a formatting command to the active editor.
 * @param {string} command The command name (e.g., 'bold', 'italic').
 * @param {object} [attrs={}] Optional attributes for the command (e.g., heading level).
 */
function applyCommand(command, attrs = {}) {
	if (!activeEditorView) return;
	
	const { state, dispatch } = activeEditorView;
	const { schema } = state; // MODIFIED: Get schema from the active editor's state.
	console.log(schema);
	let cmd;
	
	switch (command) {
		case 'bold': cmd = toggleMark(schema.marks.strong); break;
		case 'italic': cmd = toggleMark(schema.marks.em); break;
		case 'underline': cmd = toggleMark(schema.marks.underline); break;
		case 'strike': cmd = toggleMark(schema.marks.strike); break;
		case 'blockquote': cmd = wrapIn(schema.nodes.blockquote); break;
		case 'bullet_list': cmd = wrapInList(schema.nodes.bullet_list); break;
		case 'ordered_list': cmd = wrapInList(schema.nodes.ordered_list); break;
		case 'horizontal_rule':
			dispatch(state.tr.replaceSelectionWith(schema.nodes.horizontal_rule.create()));
			break;
		case 'heading':
			const { level } = attrs;
			cmd = (level === 0)
				? setBlockType(schema.nodes.paragraph)
				: setBlockType(schema.nodes.heading, { level });
			break;
	}
	
	if (cmd) {
		cmd(state, dispatch);
	}
}

/**
 * Applies or removes a highlight to the selected text.
 * @param {string} color The color name (e.g., 'yellow', 'transparent').
 */
function applyHighlight(color) {
	if (!activeEditorView) return;
	
	const { state } = activeEditorView; // MODIFIED: Get state once.
	const { schema } = state; // MODIFIED: Get schema from the active editor's state.
	const { from, to } = state.selection;
	let tr = state.tr;
	
	// First, remove any existing highlight marks in the selection.
	Object.keys(schema.marks).forEach(markName => {
		if (markName.startsWith('highlight_')) {
			tr = tr.removeMark(from, to, schema.marks[markName]);
		}
	});
	
	// Then, apply the new one if a color is specified.
	if (color !== 'transparent') {
		const markType = schema.marks[`highlight_${color}`];
		if (markType) {
			tr = tr.addMark(from, to, markType.create());
		}
	}
	
	activeEditorView.dispatch(tr);
}

/**
 * Handles the click on an AI action "Apply" button.
 * @param {HTMLButtonElement} button The clicked button.
 */
async function handleAiAction(button) {
	if (!activeEditorView) return;
	
	const action = button.dataset.action;
	const dropdown = button.closest('.js-dropdown');
	const modelSelect = dropdown.querySelector('.js-llm-model-select');
	const model = modelSelect.value;
	
	const { state } = activeEditorView;
	const { schema } = state; // MODIFIED: Get schema from the active editor's state.
	const { from, to } = state.selection;
	const text = state.doc.textBetween(from, to, ' ');
	
	const entryId = activeEditorView.dom.closest('.codex-entry-window-content')?.dataset.entryId;
	
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
		
		// Replace selected text with the new text from the AI.
		const tr = activeEditorView.state.tr.replaceWith(from, to, schema.text(data.text));
		activeEditorView.dispatch(tr);
		
	} catch (error) {
		console.error('AI Action Error:', error);
		alert(`Error: ${error.message}`);
	} finally {
		button.disabled = false;
		button.textContent = 'Apply';
	}
}

/**
 * Main action handler for all buttons on the toolbar.
 * @param {HTMLButtonElement} button The button element that was clicked.
 */
async function handleToolbarAction(button) {
	if (!activeEditorView && !button.closest('.js-dropdown-container')) {
		return;
	}
	
	const command = button.dataset.command;
	
	if (command) {
		if (command === 'undo') {
			undo(activeEditorView.state, activeEditorView.dispatch);
		} else if (command === 'redo') {
			redo(activeEditorView.state, activeEditorView.dispatch);
		} else {
			applyCommand(command);
		}
	} else if (button.classList.contains('js-highlight-option')) {
		applyHighlight(button.dataset.bg.replace('highlight-', ''));
		closeAllDropdowns();
	} else if (button.classList.contains('js-ai-apply-btn')) {
		await handleAiAction(button);
		closeAllDropdowns();
	} else if (button.classList.contains('js-heading-option')) {
		const level = parseInt(button.dataset.level, 10);
		applyCommand('heading', { level });
		closeAllDropdowns();
	}
	
	// After any action, re-focus the editor.
	if (activeEditorView) {
		activeEditorView.focus();
	}
}

/**
 * Helper to close all open dropdowns.
 */
function closeAllDropdowns() {
	toolbar.querySelectorAll('.js-dropdown').forEach(d => d.classList.add('hidden'));
}

/**
 * Initializes the top toolbar functionality.
 */
export function setupTopToolbar() {
	if (!toolbar) return;
	
	// Use a single mousedown listener on the toolbar to prevent the editor from losing focus.
	toolbar.addEventListener('mousedown', event => {
		event.preventDefault(); // This is the key to fixing the focus issue.
		
		const button = event.target.closest('button');
		if (!button || button.disabled) return;
		
		// Handle dropdown toggling
		const dropdownContainer = button.closest('.js-dropdown-container');
		if (dropdownContainer) {
			const dropdown = dropdownContainer.querySelector('.js-dropdown');
			if (dropdown) {
				const isOpening = dropdown.classList.contains('hidden');
				closeAllDropdowns();
				if (isOpening) {
					dropdown.classList.remove('hidden');
				}
				// Don't process the dropdown button itself as an action
				if (button.classList.contains('js-toolbar-btn')) return;
			}
		}
		
		// Handle all other button actions
		handleToolbarAction(button);
	});
	
	// Add a listener to the body to close dropdowns when clicking elsewhere.
	document.body.addEventListener('mousedown', event => {
		if (!event.target.closest('#top-toolbar')) {
			closeAllDropdowns();
		}
	});
	
	// Set initial disabled state.
	updateToolbarState(null);
}
