import WindowManager from './WindowManager.js';
import { setupCodexEntryHandler, setupChapterHandler, setupThemeToggle, setupOpenWindowsMenu, setupCanvasControls } from './eventHandlers.js';
import { setupChapterEditor } from './chapter-editor.js';
import { setupCodexContentEditor } from './codex-content-editor.js';
import { setupTopToolbar } from './toolbar.js'; // NEW: Import the new toolbar setup function.

/**
 * Initializes the novel editor's multi-window desktop environment.
 * This script acts as the main entry point, wiring together the WindowManager
 * and all related UI event handlers.
 */
document.addEventListener('DOMContentLoaded', () => {
	const viewport = document.getElementById('viewport');
	const desktop = document.getElementById('desktop');
	const taskbar = document.getElementById('taskbar');
	const novelId = document.body.dataset.novelId;
	
	if (!viewport || !desktop || !taskbar || !novelId) {
		console.error('Essential novel editor elements are missing from the DOM.');
		return;
	}
	
	const windowManager = new WindowManager(desktop, taskbar, novelId, viewport);
	
	windowManager.initCanvas();
	
	windowManager.loadState();
	
	// Initialize event handlers for various UI interactions.
	setupTopToolbar(); // NEW: Call the top toolbar setup.
	setupCodexEntryHandler(desktop, windowManager);
	setupChapterHandler(desktop, windowManager);
	setupChapterEditor(desktop);
	setupCodexContentEditor(desktop);
	setupThemeToggle();
	setupOpenWindowsMenu(windowManager);
	setupCanvasControls(windowManager);
});
