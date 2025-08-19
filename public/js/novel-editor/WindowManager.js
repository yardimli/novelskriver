/**
 * Manages the creation, state, and interaction of windows in the novel editor desktop environment.
 * This class is responsible for the entire lifecycle of a window, from creation to closing,
 * including state persistence in the database via API calls.
 * This class now also manages the state of the pannable/zoomable canvas.
 */
export default class WindowManager {
	// Constructor now accepts a viewport element for canvas controls.
	constructor(desktop, taskbar, novelId, viewport) {
		this.desktop = desktop;
		this.taskbar = taskbar;
		this.novelId = novelId;
		this.viewport = viewport; // NEW: The visible area for the canvas.
		this.minimizedContainer = document.getElementById('minimized-windows-container');
		this.windows = new Map();
		this.activeWindow = null;
		this.highestZIndex = 10;
		this.windowCounter = 0;
		this.minimizedOrder = ['outline-window', 'codex-window'];
		
		// Properties for canvas pan and zoom state.
		this.scale = 1;
		this.panX = 0;
		this.panY = 0;
		this.isPanning = false;
		this.panStartX = 0;
		this.panStartY = 0;
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
		
		const titleBar = document.createElement('div');
		titleBar.className = 'window-title-bar flex items-center justify-between h-10 bg-gray-100 dark:bg-gray-900/70 px-3 cursor-move border-b border-gray-200 dark:border-gray-700 flex-shrink-0';
		
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
		
		this.makeDraggable(win, titleBar);
		this.makeResizable(win, resizeHandle);
		
		win.addEventListener('mousedown', () => this.focus(windowId), true);
		
		this.focus(windowId);
		
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
		
		if (this.activeWindow && this.windows.has(this.activeWindow)) {
			this.windows.get(this.activeWindow).element.classList.remove('active');
		}
		
		const win = this.windows.get(windowId);
		if (win) {
			win.element.style.zIndex = this.highestZIndex++;
			win.element.classList.add('active');
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
			const wasMinimized = win.isMinimized;
			win.element.remove();
			this.windows.delete(windowId);
			if (wasMinimized) {
				this.updateTaskbar();
			}
			this.saveState();
		}
	}
	
	/**
	 * Minimizes a window to the taskbar.
	 */
	minimize(windowId) {
		const win = this.windows.get(windowId);
		if (!win || win.isMinimized) return;
		
		if (!win.isMaximized) {
			win.originalRect = {
				x: win.element.offsetLeft,
				y: win.element.offsetTop,
				width: win.element.offsetWidth,
				height: win.element.offsetHeight
			};
		}
		
		win.isMinimized = true;
		win.element.classList.add('hidden');
		
		this.updateTaskbar();
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
		
		this.updateTaskbar();
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
			// MODIFIED: Maximize now considers the canvas scale.
			const viewportRect = this.viewport.getBoundingClientRect();
			win.element.style.width = `${viewportRect.width / this.scale}px`;
			win.element.style.height = `${(viewportRect.height - this.taskbar.offsetHeight) / this.scale}px`;
			win.element.style.left = `${-this.panX / this.scale}px`;
			win.element.style.top = `${-this.panY / this.scale}px`;
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
			// MODIFIED: Dragging must account for the canvas scale.
			win.style.left = `${win.startLeft + (e.clientX - win.startX) / this.scale}px`;
			win.style.top = `${win.startTop + (e.clientY - win.startY) / this.scale}px`;
		};
		
		const onMouseUp = () => {
			win.classList.remove('dragging');
			document.removeEventListener('mousemove', onMouseMove);
			document.removeEventListener('mouseup', onMouseUp);
			
			// MODIFIED: Update the window's stored position before saving state.
			// This ensures that if the page is reloaded, the window appears in its new position.
			const winState = this.windows.get(win.id);
			if (winState && !winState.isMaximized) {
				winState.originalRect.x = win.offsetLeft;
				winState.originalRect.y = win.offsetTop;
			}
			
			this.saveState();
		};
		
		handle.addEventListener('mousedown', (e) => {
			const winState = this.windows.get(win.id);
			if (winState && winState.isMaximized) return;
			
			win.classList.add('dragging');
			
			// MODIFIED: Store start positions for scaled movement calculation.
			win.startX = e.clientX;
			win.startY = e.clientY;
			win.startLeft = win.offsetLeft;
			win.startTop = win.offsetTop;
			
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
			const newWidth = startWidth + (e.clientX - startX) / this.scale;
			const newHeight = startHeight + (e.clientY - startY) / this.scale;
			win.style.width = `${newWidth}px`;
			win.style.height = `${newHeight}px`;
		};
		
		const onMouseUp = () => {
			win.classList.remove('dragging');
			document.removeEventListener('mousemove', onMouseMove);
			document.removeEventListener('mouseup', onMouseUp);
			
			// MODIFIED: Update the window's stored dimensions before saving state.
			// This ensures that if the page is reloaded, the window appears with its new size.
			const winState = this.windows.get(win.id);
			if (winState && !winState.isMaximized) {
				winState.originalRect.width = win.offsetWidth;
				winState.originalRect.height = win.offsetHeight;
			}
			
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
	 * MODIFIED: Saves the state of all windows and the canvas to the database via fetch.
	 * This replaces the previous localStorage implementation.
	 */
	async saveState() {
		// Collect window states
		const windowsState = [];
		this.windows.forEach((win, id) => {
			const state = {
				id: id,
				title: win.title,
				icon: win.icon,
				x: win.originalRect.x,
				y: win.originalRect.y,
				width: win.originalRect.width,
				height: win.originalRect.height,
				zIndex: parseInt(win.element.style.zIndex, 10),
				isMinimized: win.isMinimized,
				isMaximized: win.isMaximized
			};
			windowsState.push(state);
		});
		
		// Collect canvas state
		const canvasState = {
			scale: this.scale,
			panX: this.panX,
			panY: this.panY
		};
		
		// Combine into a single state object
		const fullState = {
			windows: windowsState,
			canvas: canvasState
		};
		
		try {
			const response = await fetch(`/novels/${this.novelId}/editor-state`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
					'Accept': 'application/json'
				},
				body: JSON.stringify({ state: fullState })
			});
			
			if (!response.ok) {
				const errorData = await response.json();
				throw new Error(errorData.message || 'Failed to save editor state.');
			}
			// console.log('Editor state saved successfully.'); // Optional: for debugging
		} catch (error) {
			console.error('Error saving editor state:', error);
			// Optionally, notify the user that saving failed.
		}
	}
	
	/**
	 * MODIFIED: Loads window state from the data attribute provided by the server.
	 * This replaces the previous localStorage implementation.
	 */
	async loadState() {
		const stateJSON = document.body.dataset.editorState;
		let savedState = null;
		
		if (stateJSON) {
			try {
				savedState = JSON.parse(stateJSON);
			} catch (e) {
				console.error('Failed to parse editor state from server:', e);
				savedState = null;
			}
		}
		
		let windowsCreated = false;
		
		if (savedState && savedState.windows && savedState.windows.length > 0) {
			const windows = savedState.windows;
			windows.sort((a, b) => a.zIndex - b.zIndex);
			
			for (const state of windows) {
				// ... (content fetching logic remains the same)
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
			const maxZ = Math.max(...windows.map(w => w.zIndex || 0), 10);
			this.highestZIndex = maxZ + 1;
			windowsCreated = true;
		}
		
		if (!windowsCreated) {
			this.createDefaultWindows();
		}
		
		// Load canvas state from the same object
		if (savedState && savedState.canvas) {
			this.scale = savedState.canvas.scale || 1;
			this.panX = savedState.canvas.panX || 0;
			this.panY = savedState.canvas.panY || 0;
			this.updateCanvasTransform();
		} else {
			// If no state exists, fit the default windows into view.
			this.fitToView(false); // Don't animate on initial load
		}
	}
	
	/**
	 * Redraws the entire taskbar area for minimized windows.
	 */
	updateTaskbar() {
		const minimized = [];
		this.windows.forEach((win, id) => {
			if (win.isMinimized) {
				minimized.push({ id, title: win.title, icon: win.icon });
			}
		});
		
		minimized.sort((a, b) => {
			const order = this.minimizedOrder;
			const indexA = order.indexOf(a.id);
			const indexB = order.indexOf(b.id);
			
			if (indexA !== -1 && indexB !== -1) return indexA - indexB;
			if (indexA !== -1) return -1;
			if (indexB !== -1) return 1;
			return a.title.localeCompare(b.title);
		});
		
		this.minimizedContainer.innerHTML = '';
		
		minimized.forEach(item => {
			const taskbarItem = document.createElement('button');
			taskbarItem.className = 'window-minimized bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded px-3 py-2 text-sm font-semibold text-gray-800 dark:text-gray-200 transition-colors flex items-center gap-2 flex-shrink min-w-[120px] max-w-[256px] flex-grow basis-0';
			taskbarItem.innerHTML = `<div class="w-5 h-5 flex-shrink-0">${item.icon || ''}</div><span class="truncate">${item.title}</span>`;
			taskbarItem.dataset.windowId = item.id;
			taskbarItem.addEventListener('click', () => this.restore(item.id));
			this.minimizedContainer.appendChild(taskbarItem);
		});
	}
	
	/**
	 * Creates the default set of windows if no state is saved.
	 */
	createDefaultWindows() {
		const outlineIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-full h-full"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>`;
		const codexIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-full h-full"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>`;
		
		// MODIFIED: Place windows near the center of the 5000x5000 canvas.
		const canvasCenterX = 2500;
		const canvasCenterY = 2500;
		
		const outlineTemplate = document.getElementById('outline-window-template');
		if (outlineTemplate) {
			this.createWindow({
				id: 'outline-window',
				title: 'Novel Outline',
				content: outlineTemplate.innerHTML,
				x: canvasCenterX - 520,
				y: canvasCenterY - 300,
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
				x: canvasCenterX + 20,
				y: canvasCenterY - 270,
				width: 450,
				height: 550,
				icon: codexIcon,
				closable: false
			});
		}
	}
	
	// --- NEW: CANVAS PAN AND ZOOM METHODS ---
	
	/**
	 * Initializes event listeners for canvas interactions.
	 */
	initCanvas() {
		this.viewport.addEventListener('wheel', this.handleZoom.bind(this), { passive: false });
		this.viewport.addEventListener('mousedown', this.handlePanStart.bind(this));
		this.viewport.addEventListener('mousemove', this.handlePanMove.bind(this));
		this.viewport.addEventListener('mouseup', this.handlePanEnd.bind(this));
		this.viewport.addEventListener('mouseleave', this.handlePanEnd.bind(this)); // Stop panning if mouse leaves viewport
	}
	
	/**
	 * Applies the current pan and zoom state to the desktop element.
	 * @param {boolean} [animated=false] - Whether to animate the transition.
	 */
	updateCanvasTransform(animated = false) {
		this.desktop.style.transition = animated ? 'transform 0.3s ease, top 0.3s ease, left 0.3s ease' : 'none';
		this.desktop.style.transform = `scale(${this.scale})`;
		this.desktop.style.left = `${this.panX}px`;
		this.desktop.style.top = `${this.panY}px`;
	}
	
	/**
	 * Handles the mouse wheel event for zooming.
	 */
	handleZoom(event) {
		event.preventDefault();
		const zoomIntensity = 0.1;
		const delta = event.deltaY > 0 ? -zoomIntensity : zoomIntensity;
		const newScale = Math.max(0.1, Math.min(2, this.scale + delta * this.scale));
		
		const viewportRect = this.viewport.getBoundingClientRect();
		const mouseX = event.clientX - viewportRect.left;
		const mouseY = event.clientY - viewportRect.top;
		
		// Calculate where the mouse is pointing on the un-scaled canvas
		const mousePointX = (mouseX - this.panX) / this.scale;
		const mousePointY = (mouseY - this.panY) / this.scale;
		
		// Update pan to keep the mouse point stationary relative to the viewport
		this.panX = mouseX - mousePointX * newScale;
		this.panY = mouseY - mousePointY * newScale;
		this.scale = newScale;
		
		this.updateCanvasTransform();
		this.saveState(); // MODIFIED: Replaced saveCanvasState
	}
	
	/**
	 * Handles the mousedown event to initiate panning.
	 */
	handlePanStart(event) {
		// Only pan when clicking directly on the desktop, not on a window or its contents.
		if (event.target === this.desktop) {
			this.isPanning = true;
			this.panStartX = event.clientX - this.panX;
			this.panStartY = event.clientY - this.panY;
			this.viewport.classList.add('panning');
		}
	}
	
	/**
	 * Handles the mousemove event to perform panning.
	 */
	handlePanMove(event) {
		if (this.isPanning) {
			this.panX = event.clientX - this.panStartX;
			this.panY = event.clientY - this.panStartY;
			this.updateCanvasTransform();
		}
	}
	
	/**
	 * Handles the mouseup event to end panning.
	 */
	handlePanEnd() {
		if (this.isPanning) {
			this.isPanning = false;
			this.viewport.classList.remove('panning');
			this.saveState(); // MODIFIED: Replaced saveCanvasState
		}
	}
	
	/**
	 * Zooms in on the center of the viewport.
	 */
	zoomIn() {
		this.scale = Math.min(2, this.scale * 1.2);
		this.updateCanvasTransform(true);
		this.saveState(); // MODIFIED: Replaced saveCanvasState
	}
	
	/**
	 * Zooms out from the center of the viewport.
	 */
	zoomOut() {
		this.scale = Math.max(0.1, this.scale / 1.2);
		this.updateCanvasTransform(true);
		this.saveState(); // MODIFIED: Replaced saveCanvasState
	}
	
	/**
	 * Adjusts pan and zoom to fit all open windows within the viewport.
	 * @param {boolean} [animated=true] - Whether to animate the transition.
	 */
	fitToView(animated = true) {
		if (this.windows.size === 0) return;
		
		let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
		
		this.windows.forEach(win => {
			if (win.isMinimized) return;
			const el = win.element;
			minX = Math.min(minX, el.offsetLeft);
			minY = Math.min(minY, el.offsetTop);
			maxX = Math.max(maxX, el.offsetLeft + el.offsetWidth);
			maxY = Math.max(maxY, el.offsetTop + el.offsetHeight);
		});
		
		if (!isFinite(minX)) return; // No non-minimized windows found
		
		const contentWidth = maxX - minX;
		const contentHeight = maxY - minY;
		const padding = 100; // Pixels of padding around the content
		
		const viewportWidth = this.viewport.clientWidth;
		const viewportHeight = this.viewport.clientHeight;
		
		const scaleX = viewportWidth / (contentWidth + padding * 2);
		const scaleY = viewportHeight / (contentHeight + padding * 2);
		this.scale = Math.min(1, scaleX, scaleY); // Don't zoom in past 100% on fit
		
		const contentCenterX = minX + contentWidth / 2;
		const contentCenterY = minY + contentHeight / 2;
		
		this.panX = (viewportWidth / 2) - (contentCenterX * this.scale);
		this.panY = (viewportHeight / 2) - (contentCenterY * this.scale);
		
		this.updateCanvasTransform(animated);
		this.saveState(); // MODIFIED: Replaced saveCanvasState
	}
	
	// --- END: CANVAS METHODS ---
	
	/**
	 * Helper to reposition and resize a window, updating its state.
	 */
	reposition(windowId, x, y, width, height) {
		const win = this.windows.get(windowId);
		if (!win) return;
		
		if (win.isMinimized) {
			this.restore(windowId);
		}
		
		win.element.style.left = `${x}px`;
		win.element.style.top = `${y}px`;
		win.element.style.width = `${width}px`;
		win.element.style.height = `${height}px`;
		
		win.isMaximized = false;
		win.originalRect = { x, y, width, height };
		
		this.focus(windowId);
	}
}
