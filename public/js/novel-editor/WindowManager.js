/**
 * Manages the creation, state, and interaction of windows in the novel editor desktop environment.
 * This class is responsible for the entire lifecycle of a window, from creation to closing,
 * including state persistence in localStorage.
 */
export default class WindowManager {
	constructor(desktop, taskbar, novelId) {
		this.desktop = desktop;
		this.taskbar = taskbar;
		this.novelId = novelId;
		this.storageKey = `novel-editor-windows-${this.novelId}`;
		this.minimizedContainer = document.getElementById('minimized-windows-container');
		this.windows = new Map();
		this.activeWindow = null;
		this.highestZIndex = 10;
		this.windowCounter = 0;
	}
	
	/**
	 * Creates a new window on the desktop.
	 */
	createWindow({ id, title, content, x, y, width, height, icon, closable = true }) {
		this.windowCounter++;
		const windowId = id || `window-${this.windowCounter}`;
		
		if (this.windows.has(windowId)) {
			this.focus(windowId);
			return;
		}
		
		const win = document.createElement('div');
		win.id = windowId;
		win.className = 'absolute flex flex-col bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-300 dark:border-gray-700 overflow-hidden transition-all duration-100 ease-in-out';
		win.style.width = `${width}px`;
		win.style.height = `${height}px`;
		win.style.minWidth = '300px';
		win.style.minHeight = '200px';
		win.style.left = `${x}px`;
		win.style.top = `${y}px`;
		win.style.zIndex = this.highestZIndex++;
		
		const titleBar = document.createElement('div');
		titleBar.className = 'flex items-center justify-between h-10 bg-gray-100 dark:bg-gray-900/70 px-3 cursor-move border-b border-gray-200 dark:border-gray-700 flex-shrink-0';
		
		const controls = document.createElement('div');
		controls.className = 'flex items-center gap-2';
		
		const controlButtons = [];
		if (closable) {
			controlButtons.push(this.createControlButton('bg-red-500', () => this.close(windowId), 'close'));
		}
		controlButtons.push(this.createControlButton('bg-yellow-500', () => this.minimize(windowId), 'minimize'));
		controlButtons.push(this.createControlButton('bg-green-500', () => this.maximize(windowId), 'maximize'));
		controls.append(...controlButtons);
		
		const titleWrapper = document.createElement('div');
		titleWrapper.className = 'flex items-center overflow-hidden';
		
		const iconEl = document.createElement('div');
		iconEl.className = 'w-5 h-5 mr-2 text-gray-500 dark:text-gray-400 flex-shrink-0';
		iconEl.innerHTML = icon || '';
		
		const titleText = document.createElement('span');
		titleText.className = 'font-bold text-sm truncate text-gray-700 dark:text-gray-300';
		titleText.textContent = title;
		
		titleWrapper.append(iconEl, titleText);
		
		const rightSpacer = document.createElement('div');
		rightSpacer.style.width = '64px';
		
		titleBar.append(controls, titleWrapper, rightSpacer);
		
		const contentArea = document.createElement('div');
		contentArea.className = 'flex-grow overflow-auto p-1';
		contentArea.innerHTML = content;
		
		const resizeHandle = document.createElement('div');
		resizeHandle.className = 'resize-handle';
		
		win.append(titleBar, contentArea, resizeHandle);
		this.desktop.appendChild(win);
		
		const windowState = {
			element: win,
			title,
			icon,
			isMinimized: false,
			isMaximized: false,
			originalRect: { x, y, width, height }
		};
		this.windows.set(windowId, windowState);
		this.activeWindow = windowId;
		
		this.makeDraggable(win, titleBar);
		this.makeResizable(win, resizeHandle);
		
		win.addEventListener('mousedown', () => this.focus(windowId), true);
		
		this.saveState();
		return windowId;
	}
	
	/**
	 * Helper to create window control buttons (close, minimize, maximize).
	 */
	createControlButton(colorClass, onClick, type) {
		const btn = document.createElement('button');
		btn.className = `w-3.5 h-3.5 rounded-full ${colorClass} focus:outline-none flex items-center justify-center group`;
		btn.addEventListener('click', (e) => {
			e.stopPropagation();
			onClick();
		});
		
		const icon = document.createElement('span');
		icon.className = 'text-black/50 opacity-0 group-hover:opacity-100 transition-opacity';
		let iconSvg = '';
		switch (type) {
			case 'close':
				iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>';
				break;
			case 'minimize':
				iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"/></svg>';
				break;
			
			case 'maximize':
				iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" fill="currentColor" class="bi bi-square" viewBox="0 0 16 16"><path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/></svg>';
				break;
		}
		icon.innerHTML = iconSvg;
		btn.appendChild(icon);
		
		return btn;
	}
	
	/**
	 * Brings a window to the front by increasing its z-index.
	 */
	focus(windowId) {
		if (this.activeWindow === windowId) return;
		const win = this.windows.get(windowId);
		if (win) {
			win.element.style.zIndex = this.highestZIndex++;
			this.activeWindow = windowId;
			this.saveState();
		}
	}
	
	/**
	 * Closes and removes a window from the desktop.
	 */
	close(windowId) {
		const win = this.windows.get(windowId);
		if (win) {
			win.element.remove();
			this.windows.delete(windowId);
			const taskbarItem = this.minimizedContainer.querySelector(`[data-window-id="${windowId}"]`);
			if (taskbarItem) taskbarItem.remove();
			this.saveState();
		}
	}
	
	/**
	 * Minimizes a window to the taskbar.
	 */
	minimize(windowId) {
		const win = this.windows.get(windowId);
		if (!win || win.isMinimized) return;
		
		win.isMinimized = true;
		win.element.classList.add('hidden');
		
		const taskbarItem = document.createElement('button');
		taskbarItem.className = 'window-minimized bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded px-3 py-2 text-sm font-semibold text-gray-800 dark:text-gray-200 transition-colors flex items-center gap-2';
		taskbarItem.innerHTML = `<div class="w-5 h-5 flex-shrink-0">${win.icon || ''}</div><span class="truncate">${win.title}</span>`;
		taskbarItem.dataset.windowId = windowId;
		taskbarItem.addEventListener('click', () => this.restore(windowId));
		this.minimizedContainer.appendChild(taskbarItem);
		this.saveState();
	}
	
	/**
	 * Restores a minimized window from the taskbar.
	 */
	restore(windowId) {
		const win = this.windows.get(windowId);
		if (!win || !win.isMinimized) return;
		
		win.isMinimized = false;
		win.element.classList.remove('hidden');
		this.focus(windowId);
		
		const taskbarItem = this.minimizedContainer.querySelector(`[data-window-id="${windowId}"]`);
		if (taskbarItem) taskbarItem.remove();
		this.saveState();
	}
	
	/**
	 * Toggles a window between maximized and its original size.
	 */
	maximize(windowId) {
		const win = this.windows.get(windowId);
		if (!win) return;
		
		if (win.isMaximized) {
			// Restore
			win.element.style.width = `${win.originalRect.width}px`;
			win.element.style.height = `${win.originalRect.height}px`;
			win.element.style.left = `${win.originalRect.x}px`;
			win.element.style.top = `${win.originalRect.y}px`;
			win.isMaximized = false;
		} else {
			// Maximize
			win.originalRect = {
				x: win.element.offsetLeft,
				y: win.element.offsetTop,
				width: win.element.offsetWidth,
				height: win.element.offsetHeight
			};
			win.element.style.width = '100%';
			win.element.style.height = `calc(100% - ${this.taskbar.offsetHeight}px)`;
			win.element.style.left = '0';
			win.element.style.top = '0';
			win.isMaximized = true;
		}
		this.focus(windowId);
		this.saveState();
	}
	
	/**
	 * Adds drag functionality to a window.
	 */
	makeDraggable(win, handle) {
		let offsetX; let offsetY;
		
		const onMouseMove = (e) => {
			win.style.left = `${e.clientX - offsetX}px`;
			win.style.top = `${e.clientY - offsetY}px`;
		};
		
		const onMouseUp = () => {
			win.classList.remove('dragging');
			document.removeEventListener('mousemove', onMouseMove);
			document.removeEventListener('mouseup', onMouseUp);
			this.saveState();
		};
		
		handle.addEventListener('mousedown', (e) => {
			const winState = this.windows.get(win.id);
			if (winState && winState.isMaximized) return;
			
			win.classList.add('dragging');
			
			offsetX = e.clientX - win.offsetLeft;
			offsetY = e.clientY - win.offsetTop;
			document.addEventListener('mousemove', onMouseMove);
			document.addEventListener('mouseup', onMouseUp);
		});
	}
	
	/**
	 * Adds resize functionality to a window.
	 */
	makeResizable(win, handle) {
		let startX; let startY; let startWidth; let startHeight;
		
		const onMouseMove = (e) => {
			const newWidth = startWidth + e.clientX - startX;
			const newHeight = startHeight + e.clientY - startY;
			win.style.width = `${newWidth}px`;
			win.style.height = `${newHeight}px`;
		};
		
		const onMouseUp = () => {
			win.classList.remove('dragging');
			document.removeEventListener('mousemove', onMouseMove);
			document.removeEventListener('mouseup', onMouseUp);
			this.saveState();
		};
		
		handle.addEventListener('mousedown', (e) => {
			e.preventDefault();
			win.classList.add('dragging');
			
			startX = e.clientX;
			startY = e.clientY;
			startWidth = parseInt(document.defaultView.getComputedStyle(win).width, 10);
			startHeight = parseInt(document.defaultView.getComputedStyle(win).height, 10);
			document.addEventListener('mousemove', onMouseMove);
			document.addEventListener('mouseup', onMouseUp);
		});
	}
	
	/**
	 * Saves the state of all windows to localStorage.
	 */
	saveState() {
		const windowsState = [];
		this.windows.forEach((win, id) => {
			const state = {
				id: id,
				title: win.title,
				icon: win.icon,
				x: win.isMaximized ? win.originalRect.x : win.element.offsetLeft,
				y: win.isMaximized ? win.originalRect.y : win.element.offsetTop,
				width: win.isMaximized ? win.originalRect.width : win.element.offsetWidth,
				height: win.isMaximized ? win.originalRect.height : win.element.offsetHeight,
				zIndex: parseInt(win.element.style.zIndex, 10),
				isMinimized: win.isMinimized,
				isMaximized: win.isMaximized
			};
			windowsState.push(state);
		});
		localStorage.setItem(this.storageKey, JSON.stringify(windowsState));
	}
	
	/**
	 * Loads window states from localStorage.
	 */
	async loadState() {
		const savedState = localStorage.getItem(this.storageKey);
		if (!savedState) {
			this.createDefaultWindows();
			return;
		}
		
		const windows = JSON.parse(savedState);
		if (!windows || windows.length === 0) {
			this.createDefaultWindows();
			return;
		}
		
		// Sort by z-index to create them in the correct stacking order.
		windows.sort((a, b) => a.zIndex - b.zIndex);
		
		for (const state of windows) {
			let content = '';
			let closable = true;
			const icon = state.icon;
			const title = state.title;
			
			if (state.id === 'outline-window') {
				const template = document.getElementById('outline-window-template');
				if (template) content = template.innerHTML;
				closable = false;
			} else if (state.id === 'codex-window') {
				const template = document.getElementById('codex-window-template');
				if (template) content = template.innerHTML;
				closable = false;
			} else if (state.id.startsWith('codex-entry-')) {
				const entryId = state.id.replace('codex-entry-', '');
				try {
					const response = await fetch(`/novels/codex-entries/${entryId}`);
					if (response.ok) {
						content = await response.text();
					} else {
						content = `<p class="p-4 text-red-500">Error loading content.</p>`;
					}
				} catch (e) {
					content = `<p class="p-4 text-red-500">Error loading content.</p>`;
				}
				// NEW: Handle restoring chapter windows.
			} else if (state.id.startsWith('chapter-')) {
				const chapterId = state.id.replace('chapter-', '');
				try {
					const response = await fetch(`/chapters/${chapterId}`);
					if (response.ok) {
						content = await response.text();
					} else {
						content = `<p class="p-4 text-red-500">Error loading chapter content.</p>`;
					}
				} catch (e) {
					content = `<p class="p-4 text-red-500">Error loading chapter content.</p>`;
				}
			}
			
			if (content) {
				this.createWindow({
					id: state.id,
					title: title,
					content: content,
					x: state.x,
					y: state.y,
					width: state.width,
					height: state.height,
					icon: icon,
					closable: closable
				});
				
				const win = this.windows.get(state.id);
				if (win) {
					win.element.style.zIndex = state.zIndex;
					// Set originalRect from saved state before potentially maximizing.
					win.originalRect = { x: state.x, y: state.y, width: state.width, height: state.height };
					if (state.isMaximized) {
						this.maximize(state.id);
					}
					if (state.isMinimized) {
						this.minimize(state.id);
					}
				}
			}
		}
		// After creating all, find the highest z-index to continue from there.
		const maxZ = Math.max(...windows.map(w => w.zIndex || 0), 10);
		this.highestZIndex = maxZ + 1;
	}
	
	/**
	 * Creates the default set of windows if no state is saved.
	 */
	createDefaultWindows() {
		const outlineIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-full h-full"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>`;
		const codexIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-full h-full"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>`;
		
		const outlineTemplate = document.getElementById('outline-window-template');
		if (outlineTemplate) {
			this.createWindow({
				id: 'outline-window',
				title: 'Novel Outline',
				content: outlineTemplate.innerHTML,
				x: 50,
				y: 50,
				width: 500,
				height: 600,
				icon: outlineIcon,
				closable: false
			});
		}
		
		const codexTemplate = document.getElementById('codex-window-template');
		if (codexTemplate) {
			this.createWindow({
				id: 'codex-window',
				title: 'Codex',
				content: codexTemplate.innerHTML,
				x: 600,
				y: 80,
				width: 450,
				height: 550,
				icon: codexIcon,
				closable: false
			});
		}
	}
}
