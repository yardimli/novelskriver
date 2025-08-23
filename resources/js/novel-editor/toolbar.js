/**
 * Manages the top toolbar for text editing within ProseMirror editors.
 * Handles formatting, highlighting, AI actions, and selection state.
 */

import { getActiveEditor, schema } from './codex-content-editor.js';
import { toggleMark } from 'prosemirror-commands';
import { history, undo, redo } from 'prosemirror-history';

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
	
	if (view && view.state) {
		const { state } = view;
		const { from, to, empty } = state.selection;
		
		const isTextSelected = !empty;
		
		// Enable/disable buttons based on selection and command availability.
		allBtns.forEach(btn => {
			const cmd = btn.dataset.command;
			if (cmd === 'undo') {
				btn.disabled = !undo(state);
			} else if (cmd === 'redo') {
				btn.disabled = !redo(state);
			} else if (btn.closest('.js-dropdown-container') || btn.classList.contains('js-ai-action-btn')) {
				btn.disabled = !isTextSelected;
			} else if (cmd) {
				btn.disabled = !isTextSelected;
			}
		});
		
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
		wordCountEl.textContent = 'No text selected';
	}
}

/**
 * Applies a formatting command to the active editor.
 * @param {string} command The command name (e.g., 'bold', 'italic').
 */
function applyFormatCommand(command) {
	if (!activeEditorView) return;
	
	let cmd;
	switch (command) {
		case 'bold':
			cmd = toggleMark(schema.marks.strong);
			break;
		case 'italic':
			cmd = toggleMark(schema.marks.em);
			break;
		case 'underline':
			cmd = toggleMark(schema.marks.underline);
			break;
		default:
			return;
	}
	
	cmd(activeEditorView.state, activeEditorView.dispatch);
}

/**
 * Applies or removes a highlight to the selected text.
 * This first removes any existing highlights in the selection before applying the new one.
 * @param {string} color The color name (e.g., 'yellow', 'transparent').
 */
function applyHighlight(color) {
	if (!activeEditorView) return;
	
	const { from, to } = activeEditorView.state.selection;
	let tr = activeEditorView.state.tr;
	
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
 * Main click handler for all buttons on the toolbar.
 * @param {MouseEvent} event
 */
async function handleToolbarClick(event) {
	const button = event.target.closest('button');
	if (!button) return;
	
	// Prevent running commands if no editor is active.
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
			applyFormatCommand(command);
		}
	} else if (button.classList.contains('js-highlight-option')) {
		applyHighlight(button.dataset.bg.replace('highlight-', ''));
		closeAllDropdowns();
	} else if (button.classList.contains('js-ai-apply-btn')) {
		await handleAiAction(button);
		closeAllDropdowns();
	}
	
	// After command, re-focus the editor.
	if (activeEditorView) {
		activeEditorView.focus();
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
	} else if (!event.target.closest('.js-dropdown')) {
		// Close if clicking outside any dropdown.
		closeAllDropdowns();
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
	
	// Add click handlers for toolbar buttons.
	toolbar.addEventListener('click', handleToolbarClick);
	
	// Dropdown handling for highlight and AI menus.
	document.body.addEventListener('click', handleDropdowns);
	
	// Set initial disabled state.
	updateToolbarState(null);
}
