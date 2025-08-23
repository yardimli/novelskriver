/**
 * Manages ProseMirror editor instances for codex entry windows.
 * This file handles editor creation, schema definition, and debounced saving.
 */

import { EditorState, Plugin } from 'prosemirror-state';
import { EditorView } from 'prosemirror-view';
import { Schema, DOMParser, DOMSerializer } from 'prosemirror-model';
import { schema as basicSchema } from 'prosemirror-schema-basic';
import { addListNodes } from 'prosemirror-schema-list';
import { history, undo, redo } from 'prosemirror-history';
import { keymap } from 'prosemirror-keymap';
// MODIFIED: Import toggleMark to rebuild keybindings, and baseKeymap for the rest.
import { baseKeymap, toggleMark } from 'prosemirror-commands';
import { updateToolbarState } from './toolbar.js';

// --- STATE MANAGEMENT ---
const debounceTimers = new Map();
const editorInstances = new Map(); // Maps entryId to { descriptionView, contentView }
let activeEditorView = null;

// --- SCHEMA DEFINITION ---
// Helper to define custom highlight marks.
const highlightMarkSpec = (colorClass) => ({
	attrs: {},
	parseDOM: [{ tag: `span.${colorClass}` }],
	toDOM: () => ['span', { class: colorClass }, 0],
});

// The main schema for the 'content' field, with added marks for underline and highlights.
export const schema = new Schema({
	nodes: addListNodes(basicSchema.spec.nodes, 'paragraph block*', 'block'),
	marks: {
		// MODIFIED: Replaced `...basicSchema.spec.marks` with explicit definitions.
		// This resolves an issue where `strong` and `em` marks were not being
		// correctly added to the schema, causing errors in the toolbar.
		
		// From prosemirror-schema-basic
		link: {
			attrs: {
				href: {},
				title: { default: null },
			},
			inclusive: false,
			parseDOM: [{
				tag: 'a[href]', getAttrs(dom) {
					return { href: dom.getAttribute('href'), title: dom.getAttribute('title') };
				},
			}],
			toDOM(node) { return ['a', node.attrs, 0]; },
		},
		
		// From prosemirror-schema-basic
		em: {
			parseDOM: [{ tag: 'i' }, { tag: 'em' }, { style: 'font-style=italic' }],
			toDOM() { return ['em', 0]; },
		},
		
		// From prosemirror-schema-basic
		strong: {
			parseDOM: [
				{ tag: 'strong' },
				// This works around a Google Docs misbehavior where
				// pasted content will be inexplicably wrapped in `<b>`
				// tags with a font-weight normal.
				{ tag: 'b', getAttrs: node => node.style.fontWeight != 'normal' && null },
				{ style: 'font-weight', getAttrs: value => /^(bold(er)?|[5-9]\d{2,})$/.test(value) && null },
			],
			toDOM() { return ['strong', 0]; },
		},
		
		// From prosemirror-schema-basic
		code: {
			parseDOM: [{ tag: 'code' }],
			toDOM() { return ['code', 0]; },
		},
		
		// Custom marks
		underline: {
			parseDOM: [{ tag: 'u' }, { style: 'text-decoration=underline' }],
			toDOM: () => ['u', 0],
		},
		strike: {
			parseDOM: [{ tag: 's' }, { tag: 'del' }, { style: 'text-decoration=line-through' }],
			toDOM: () => ['s', 0],
		},
		highlight_yellow: highlightMarkSpec('highlight-yellow'),
		highlight_green: highlightMarkSpec('highlight-green'),
		highlight_blue: highlightMarkSpec('highlight-blue'),
		highlight_red: highlightMarkSpec('highlight-red'),
	},
});

// A simpler, single-line schema for the 'description' field.
const descriptionSchema = new Schema({
	nodes: {
		doc: { content: 'paragraph' },
		paragraph: { content: 'text*', toDOM: () => ['p', 0], parseDOM: [{ tag: 'p' }] },
		text: {},
	},
	marks: {},
});

// --- HELPERS ---
/**
 * Returns the currently focused ProseMirror editor view.
 * @returns {EditorView|null}
 */
export function getActiveEditor() {
	return activeEditorView;
}

// --- DEBOUNCED SAVING ---
/**
 * Triggers a debounced save operation for a given codex entry window.
 * @param {HTMLElement} windowContent The content element of the codex window.
 */
function triggerDebouncedSave(windowContent) {
	const entryId = windowContent.dataset.entryId;
	if (!entryId) return;
	
	if (debounceTimers.has(entryId)) {
		clearTimeout(debounceTimers.get(entryId));
	}
	
	const timer = setTimeout(() => {
		saveCodexEntry(windowContent);
		debounceTimers.delete(entryId);
	}, 2000); // 2-second delay
	
	debounceTimers.set(entryId, timer);
}

/**
 * Saves the content of a codex entry window via an API call.
 * @param {HTMLElement} windowContent The main content element of the codex window.
 */
async function saveCodexEntry(windowContent) {
	const entryId = windowContent.dataset.entryId;
	const instances = editorInstances.get(entryId);
	if (!instances) return;
	
	const titleInput = windowContent.querySelector('.js-codex-title-input');
	
	// Serialize description as plain text, as it was before.
	const description = instances.descriptionView.state.doc.textContent;
	
	// Serialize content as HTML, preserving formatting.
	const serializer = DOMSerializer.fromSchema(schema);
	const fragment = serializer.serializeFragment(instances.contentView.state.doc.content);
	const tempDiv = document.createElement('div');
	tempDiv.appendChild(fragment);
	const content = tempDiv.innerHTML;
	
	const data = {
		title: titleInput.value,
		description: description,
		content: content,
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
	} catch (error) {
		console.error('Error saving codex entry:', error);
		alert('Error: Could not save changes to codex entry.');
	}
}

// --- EDITOR INITIALIZATION ---
/**
 * Creates and initializes ProseMirror editors for a newly opened codex window.
 * @param {HTMLElement} windowContent The content element of the codex window.
 */
function initEditorsForWindow(windowContent) {
	const entryId = windowContent.dataset.entryId;
	if (!entryId || editorInstances.has(entryId)) return;
	
	// Also trigger save on title input change.
	const titleInput = windowContent.querySelector('.js-codex-title-input');
	titleInput.addEventListener('input', () => triggerDebouncedSave(windowContent));
	
	const descriptionMount = windowContent.querySelector('.js-codex-editable[data-name="description"]');
	const contentMount = windowContent.querySelector('.js-codex-editable[data-name="content"]');
	const initialContentContainer = windowContent.querySelector('.js-pm-content');
	
	if (!descriptionMount || !contentMount || !initialContentContainer) return;
	
	// Factory function to create an editor instance.
	const createEditor = (mount, isDescription) => {
		const name = mount.dataset.name;
		const placeholder = mount.dataset.placeholder || '';
		const initialContentEl = initialContentContainer.querySelector(`[data-name="${name}"]`);
		const currentSchema = isDescription ? descriptionSchema : schema;
		
		// Parse the initial content from the hidden div.
		const doc = DOMParser.fromSchema(currentSchema).parse(initialContentEl);
		
		// MODIFIED: Rebuild the base keymap to use marks from our custom schema.
		// This is crucial because the default baseKeymap is bound to the original
		// basicSchema instance, and our new schema has different mark instances.
		const customKeymap = {
			...baseKeymap,
			'Mod-b': toggleMark(schema.marks.strong),
			'Mod-B': toggleMark(schema.marks.strong),
			'Mod-i': toggleMark(schema.marks.em),
			'Mod-I': toggleMark(schema.marks.em),
		};
		
		const view = new EditorView(mount, {
			state: EditorState.create({
				doc,
				plugins: [
					history(),
					keymap({ 'Mod-z': undo, 'Mod-y': redo, 'Shift-Mod-z': redo }),
					keymap(customKeymap), // Use our rebuilt keymap
					isDescription ? keymap({ 'Enter': () => true }) : keymap({}), // Prevent newlines in description
					new Plugin({
						props: {
							handleDOMEvents: {
								focus(view) {
									activeEditorView = view;
									updateToolbarState(view);
								},
								blur(view, event) {
									const relatedTarget = event.relatedTarget;
									if (!relatedTarget || !relatedTarget.closest('#top-toolbar')) {
										activeEditorView = null;
										updateToolbarState(null);
									}
								},
							},
							// Add classes for styling and placeholder text.
							attributes: (state) => ({
								class: `ProseMirror ${state.doc.childCount === 1 && state.doc.firstChild.content.size === 0 ? 'is-editor-empty' : ''}`,
								'data-placeholder': placeholder,
							}),
						},
					}),
				],
			}),
			dispatchTransaction(transaction) {
				const newState = view.state.apply(transaction);
				view.updateState(newState);
				// If the document changed, trigger a save.
				if (transaction.docChanged) {
					triggerDebouncedSave(windowContent);
				}
				// If selection or content changed, update the toolbar state.
				if ((transaction.selectionSet || transaction.docChanged)) {
					// MODIFIED: No need to check hasFocus() here, as the blur event handles deactivation.
					// This ensures the toolbar updates correctly even after a command is dispatched.
					updateToolbarState(view);
				}
			},
		});
		return view;
	};
	
	const descriptionView = createEditor(descriptionMount, true);
	const contentView = createEditor(contentMount, false);
	
	editorInstances.set(entryId, { descriptionView, contentView });
}

/**
 * Sets up a MutationObserver to automatically initialize editors in new codex windows.
 * @param {HTMLElement} desktop - The main desktop element to observe for changes.
 */
export function setupCodexContentEditor(desktop) {
	const observer = new MutationObserver((mutationsList) => {
		for (const mutation of mutationsList) {
			if (mutation.type === 'childList') {
				// Handle newly added windows.
				mutation.addedNodes.forEach(node => {
					if (node.nodeType !== Node.ELEMENT_NODE) return;
					const windowContent = node.querySelector('.codex-entry-window-content') || (node.matches('.codex-entry-window-content') ? node : null);
					if (windowContent) {
						initEditorsForWindow(windowContent);
					}
				});
				// Handle closed windows to clean up editor instances.
				mutation.removedNodes.forEach(node => {
					if (node.nodeType !== Node.ELEMENT_NODE) return;
					const windowContent = node.querySelector('.codex-entry-window-content') || (node.matches('.codex-entry-window-content') ? node : null);
					if (windowContent) {
						const entryId = windowContent.dataset.entryId;
						if (editorInstances.has(entryId)) {
							const { descriptionView, contentView } = editorInstances.get(entryId);
							descriptionView.destroy();
							contentView.destroy();
							editorInstances.delete(entryId);
							debounceTimers.delete(entryId);
						}
					}
				});
			}
		}
	});
	
	observer.observe(desktop, { childList: true, subtree: true });
	
	// Initialize editors for any windows that are already present on page load.
	desktop.querySelectorAll('.codex-entry-window-content').forEach(initEditorsForWindow);
}
