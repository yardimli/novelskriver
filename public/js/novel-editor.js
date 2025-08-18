/**
 * Novel Editor Window Manager
 *
 * This script creates a multi-window desktop-like environment for editing novels.
 * It handles creating, dragging, resizing, minimizing, maximizing, and closing windows.
 */
document.addEventListener('DOMContentLoaded', () => {
	class WindowManager {
		constructor(desktop, taskbar) {
			this.desktop = desktop;
			this.taskbar = taskbar;
			this.windows = new Map();
			this.activeWindow = null;
			this.highestZIndex = 10;
			this.windowCounter = 0;
		}
		
		/**
		 * Creates a new window on the desktop.
		 */
		createWindow({ id, title, content, x, y, width, height }) {
			this.windowCounter++;
			const windowId = id || `window-${this.windowCounter}`;
			
			// If window already exists, focus it instead of creating a new one.
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
			
			// Title Bar
			const titleBar = document.createElement('div');
			titleBar.className = 'flex items-center justify-between h-10 bg-gray-100 dark:bg-gray-900/70 px-3 cursor-move border-b border-gray-200 dark:border-gray-700 flex-shrink-0';
			
			const controls = document.createElement('div');
			controls.className = 'flex items-center gap-2';
			const closeBtn = this.createControlButton('bg-red-500', () => this.close(windowId), 'close');
			const minimizeBtn = this.createControlButton('bg-yellow-500', () => this.minimize(windowId), 'minimize');
			const maximizeBtn = this.createControlButton('bg-green-500', () => this.maximize(windowId), 'maximize');
			controls.append(closeBtn, minimizeBtn, maximizeBtn);
			
			const titleText = document.createElement('span');
			titleText.className = 'font-bold text-sm truncate text-gray-700 dark:text-gray-300';
			titleText.textContent = title;
			
			titleBar.append(controls, titleText, document.createElement('div'));
			
			// Content Area
			const contentArea = document.createElement('div');
			contentArea.className = 'flex-grow overflow-auto p-1';
			contentArea.innerHTML = content;
			
			// Resize Handle
			const resizeHandle = document.createElement('div');
			resizeHandle.className = 'resize-handle';
			
			win.append(titleBar, contentArea, resizeHandle);
			this.desktop.appendChild(win);
			
			const windowState = {
				element: win,
				title,
				isMinimized: false,
				isMaximized: false,
				originalRect: { x, y, width, height },
			};
			this.windows.set(windowId, windowState);
			this.activeWindow = windowId;
			
			this.makeDraggable(win, titleBar);
			this.makeResizable(win, resizeHandle);
			
			win.addEventListener('mousedown', () => this.focus(windowId), true);
			
			return windowId;
		}
		
		/**
		 * Helper to create window control buttons (close, minimize, maximize).
		 * @param {string} colorClass - The background color class for the button.
		 * @param {Function} onClick - The function to call on click.
		 * @param {string} type - The type of button ('close', 'minimize', 'maximize') to determine the icon.
		 * @returns {HTMLButtonElement}
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
				const taskbarItem = this.taskbar.querySelector(`[data-window-id="${windowId}"]`);
				if (taskbarItem) taskbarItem.remove();
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
			taskbarItem.className = 'window-minimized bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded px-3 py-2 text-sm font-semibold truncate text-gray-800 dark:text-gray-200 transition-colors';
			taskbarItem.textContent = win.title;
			taskbarItem.dataset.windowId = windowId;
			taskbarItem.addEventListener('click', () => this.restore(windowId));
			this.taskbar.appendChild(taskbarItem);
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
			
			const taskbarItem = this.taskbar.querySelector(`[data-window-id="${windowId}"]`);
			if (taskbarItem) taskbarItem.remove();
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
				const rect = win.element.getBoundingClientRect();
				win.originalRect = { x: rect.left, y: rect.top, width: rect.width, height: rect.height };
				win.element.style.width = '100%';
				win.element.style.height = `calc(100% - ${this.taskbar.offsetHeight}px)`;
				win.element.style.left = '0';
				win.element.style.top = '0';
				win.isMaximized = true;
			}
			this.focus(windowId);
		}
		
		/**
		 * Adds drag functionality to a window.
		 */
		makeDraggable(win, handle) {
			let offsetX, offsetY;
			
			const onMouseMove = (e) => {
				win.style.left = `${e.clientX - offsetX}px`;
				win.style.top = `${e.clientY - offsetY}px`;
			};
			
			const onMouseUp = () => {
				// NEW: Remove the 'dragging' class to re-enable transitions.
				win.classList.remove('dragging');
				document.removeEventListener('mousemove', onMouseMove);
				document.removeEventListener('mouseup', onMouseUp);
			};
			
			handle.addEventListener('mousedown', (e) => {
				const winState = this.windows.get(win.id);
				if (winState && winState.isMaximized) return;
				
				// NEW: Add a 'dragging' class to disable transitions during drag.
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
			let startX, startY, startWidth, startHeight;
			
			const onMouseMove = (e) => {
				const newWidth = startWidth + e.clientX - startX;
				const newHeight = startHeight + e.clientY - startY;
				win.style.width = `${newWidth}px`;
				win.style.height = `${newHeight}px`;
			};
			
			const onMouseUp = () => {
				// NEW: Remove the 'dragging' class to re-enable transitions.
				win.classList.remove('dragging');
				document.removeEventListener('mousemove', onMouseMove);
				document.removeEventListener('mouseup', onMouseUp);
			};
			
			handle.addEventListener('mousedown', (e) => {
				e.preventDefault();
				
				// NEW: Add a 'dragging' class to disable transitions during resize.
				win.classList.add('dragging');
				
				startX = e.clientX;
				startY = e.clientY;
				startWidth = parseInt(document.defaultView.getComputedStyle(win).width, 10);
				startHeight = parseInt(document.defaultView.getComputedStyle(win).height, 10);
				document.addEventListener('mousemove', onMouseMove);
				document.addEventListener('mouseup', onMouseUp);
			});
		}
	}
	
	const desktop = document.getElementById('desktop');
	const taskbar = document.getElementById('taskbar');
	const windowManager = new WindowManager(desktop, taskbar);
	
	// Create initial windows from templates in the HTML.
	const outlineTemplate = document.getElementById('outline-window-template');
	if (outlineTemplate) {
		windowManager.createWindow({
			id: 'outline-window',
			title: 'Novel Outline',
			content: outlineTemplate.innerHTML,
			x: 50,
			y: 50,
			width: 500,
			height: 600,
		});
	}
	
	const codexTemplate = document.getElementById('codex-window-template');
	if (codexTemplate) {
		windowManager.createWindow({
			id: 'codex-window',
			title: 'Codex',
			content: codexTemplate.innerHTML,
			x: 600,
			y: 80,
			width: 450,
			height: 550,
		});
	}
});
