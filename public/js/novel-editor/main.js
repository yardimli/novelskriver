import WindowManager from './WindowManager.js';
// MODIFIED: Import the new chapter handler.
import { setupCodexEntryHandler, setupChapterHandler, setupThemeToggle, setupOpenWindowsMenu } from './eventHandlers.js';

/**
 * Initializes the novel editor's multi-window desktop environment.
 * This script acts as the main entry point, wiring together the WindowManager
 * and all related UI event handlers.
 */
document.addEventListener('DOMContentLoaded', () => {
	const desktop = document.getElementById('desktop');
	const taskbar = document.getElementById('taskbar');
	// The novel ID is crucial for namespacing the window state in localStorage.
	const novelId = document.body.dataset.novelId;
	
	if (!desktop || !taskbar || !novelId) {
		console.error('Essential novel editor elements are missing from the DOM.');
		return;
	}
	
	// Instantiate the core window manager.
	const windowManager = new WindowManager(desktop, taskbar, novelId);
	
	// Load window state from localStorage or create the default set of windows.
	// This is an async operation as it may need to fetch content for windows.
	windowManager.loadState();
	
	// Initialize event handlers for various UI interactions.
	setupCodexEntryHandler(desktop, windowManager);
	setupChapterHandler(desktop, windowManager); // NEW: Call the chapter handler.
	setupThemeToggle();
	setupOpenWindowsMenu(windowManager);
});
