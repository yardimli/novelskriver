/**
 * This module contains functions to set up various event listeners for the novel editor UI.
 * It helps to keep the main initialization script clean and separates concerns.
 */

/**
 * Sets up the event listener for opening codex entry windows.
 * Uses event delegation on the desktop to handle clicks on dynamically loaded content.
 * @param {HTMLElement} desktop - The main desktop element to attach the listener to.
 * @param {WindowManager} windowManager - The window manager instance.
 */
export function setupCodexEntryHandler(desktop, windowManager) {
	const entryIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-full h-full"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>`;
	
	desktop.addEventListener('click', async (event) => {
		const entryButton = event.target.closest('.js-open-codex-entry');
		if (!entryButton) return;
		
		const entryId = entryButton.dataset.entryId;
		const entryTitle = entryButton.dataset.entryTitle;
		const windowId = `codex-entry-${entryId}`;
		
		if (windowManager.windows.has(windowId)) {
			const win = windowManager.windows.get(windowId);
			if (win.isMinimized) {
				windowManager.restore(windowId);
			} else {
				windowManager.focus(windowId);
			}
			return;
		}
		
		try {
			const response = await fetch(`/novels/codex-entries/${entryId}`);
			if (!response.ok) {
				throw new Error('Failed to load codex entry details.');
			}
			const content = await response.text();
			
			const openWindows = document.querySelectorAll('[id^="codex-entry-"]').length;
			const offsetX = 850 + (openWindows * 30);
			const offsetY = 120 + (openWindows * 30);
			
			windowManager.createWindow({
				id: windowId,
				title: entryTitle,
				content: content,
				x: offsetX,
				y: offsetY,
				width: 600,
				height: 450,
				icon: entryIcon,
				closable: true
			});
		} catch (error) {
			console.error('Error opening codex entry window:', error);
			alert(error.message);
		}
	});
}

/**
 * NEW: Sets up the event listener for opening chapter windows.
 * Uses event delegation on the desktop to handle clicks on dynamically loaded content.
 * @param {HTMLElement} desktop - The main desktop element to attach the listener to.
 * @param {WindowManager} windowManager - The window manager instance.
 */
export function setupChapterHandler(desktop, windowManager) {
	const chapterIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-full h-full"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" /></svg>`;
	
	desktop.addEventListener('click', async (event) => {
		const chapterButton = event.target.closest('.js-open-chapter');
		if (!chapterButton) return;
		
		const chapterId = chapterButton.dataset.chapterId;
		const chapterTitle = chapterButton.dataset.chapterTitle;
		const windowId = `chapter-${chapterId}`;
		
		if (windowManager.windows.has(windowId)) {
			const win = windowManager.windows.get(windowId);
			if (win.isMinimized) {
				windowManager.restore(windowId);
			} else {
				windowManager.focus(windowId);
			}
			return;
		}
		
		try {
			const response = await fetch(`/chapters/${chapterId}`);
			if (!response.ok) {
				throw new Error('Failed to load chapter details.');
			}
			const content = await response.text();
			
			const openWindows = document.querySelectorAll('[id^="chapter-"]').length;
			const offsetX = 100 + (openWindows * 30);
			const offsetY = 300 + (openWindows * 30);
			
			windowManager.createWindow({
				id: windowId,
				title: chapterTitle,
				content: content,
				x: offsetX,
				y: offsetY,
				width: 700,
				height: 500,
				icon: chapterIcon,
				closable: true
			});
		} catch (error) {
			console.error('Error opening chapter window:', error);
			alert(error.message);
		}
	});
}

/**
 * Sets up the theme toggling functionality.
 */
export function setupThemeToggle() {
	const themeToggleBtn = document.getElementById('theme-toggle');
	const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
	const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
	
	if (document.documentElement.classList.contains('dark')) {
		themeToggleLightIcon.classList.remove('hidden');
	} else {
		themeToggleDarkIcon.classList.remove('hidden');
	}
	
	themeToggleBtn.addEventListener('click', function() {
		themeToggleDarkIcon.classList.toggle('hidden');
		themeToggleLightIcon.classList.toggle('hidden');
		
		if (document.documentElement.classList.contains('dark')) {
			document.documentElement.classList.remove('dark');
			localStorage.setItem('theme', 'light');
		} else {
			document.documentElement.classList.add('dark');
			localStorage.setItem('theme', 'dark');
		}
	});
}

/**
 * Sets up the "Open Windows" menu functionality in the taskbar.
 * @param {WindowManager} windowManager - The window manager instance.
 */
export function setupOpenWindowsMenu(windowManager) {
	const openWindowsBtn = document.getElementById('open-windows-btn');
	const openWindowsMenu = document.getElementById('open-windows-menu');
	const openWindowsList = document.getElementById('open-windows-list');
	
	function populateOpenWindowsMenu() {
		openWindowsList.innerHTML = '';
		
		if (windowManager.windows.size === 0) {
			openWindowsList.innerHTML = `<li class="px-4 py-2 text-sm text-gray-500">No open windows.</li>`;
			return;
		}
		
		windowManager.windows.forEach((win, windowId) => {
			const li = document.createElement('li');
			const button = document.createElement('button');
			button.className = 'w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3';
			button.innerHTML = `<div class="w-5 h-5 flex-shrink-0">${win.icon || ''}</div><span class="truncate">${win.title}</span>`;
			
			button.addEventListener('click', () => {
				if (win.isMinimized) {
					windowManager.restore(windowId);
				} else {
					windowManager.focus(windowId);
				}
				openWindowsMenu.classList.add('hidden');
			});
			
			li.appendChild(button);
			openWindowsList.appendChild(li);
		});
	}
	
	openWindowsBtn.addEventListener('click', (e) => {
		e.stopPropagation();
		if (openWindowsMenu.classList.contains('hidden')) {
			populateOpenWindowsMenu();
		}
		openWindowsMenu.classList.toggle('hidden');
	});
	
	document.addEventListener('click', (e) => {
		if (!openWindowsMenu.classList.contains('hidden') && !openWindowsMenu.contains(e.target) && !openWindowsBtn.contains(e.target)) {
			openWindowsMenu.classList.add('hidden');
		}
	});
}
