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
		this.viewport = viewport; // The visible area for the canvas.
		this.minimizedContainer = document.getElementById('minimized-windows-container');
		this.windows = new Map();
		this.activeWindow = null;
		this.highestZIndex = 10;
		this.windowCounter = 0;
		this.minimizedOrder = ['outline-window', 'codex-window'];
		this.selectedWindows = new Set(); // NEW: To track multiple selected windows.
		
		// Timeout for debouncing save state calls.
		this.saveStateTimeout = null;
		
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
		
		titleBar.addEventListener('dblclick', () => this.maximize(windowId));
		
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
		
		// MODIFIED: Add "+ New Entry" button to the codex window title bar and handle spacer width.
		if (id === 'codex-window') {
			rightSpacer.className = 'flex items-center justify-end min-w-[64px]';
			const newEntryBtn = document.createElement('button');
			newEntryBtn.type = 'button';
			newEntryBtn.className = 'js-open-new-codex-modal text-xs px-2 py-1 bg-teal-500 hover:bg-teal-600 text-white rounded-md transition-colors flex items-center gap-1 mr-2';
			newEntryBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                New Entry
            `;
			rightSpacer.appendChild(newEntryBtn);
		} else {
			// If it's not the codex window, keep the original spacer for balance.
			rightSpacer.style.width = '64px';
		}
		
		titleBar.append(controls, titleWrapper, rightSpacer);
		
		const contentArea = document.createElement('div');
		contentArea.className = 'flex-grow overflow-auto p-1';
		contentArea.innerHTML = content;
		
		// NEW: Find modals within the content, move them to the body so they are not affected by canvas transform.
		const modals = contentArea.querySelectorAll('.js-ai-modal, .js-upload-modal');
		modals.forEach(modal => {
			document.body.appendChild(modal);
		});
		
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
		
		// MODIFIED: Pass the event object to focus() to handle shift-clicks for multi-selection.
		win.addEventListener('mousedown', (e) => this.focus(windowId, e), true);
		
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
	 * MODIFIED: Pans the canvas to ensure the specified window is fully visible in the viewport.
	 * This version checks all four edges of the window against the viewport boundaries.
	 * @param {string} windowId The ID of the window to bring into view.
	 */
	scrollIntoView(windowId) {
		const win = this.windows.get(windowId);
		if (!win || win.isMinimized) return;
		
		const el = win.element;
		const padding = 50; // 50px padding from the viewport edges
		
		// Get window's position and size in the desktop's coordinate system
		const winLeft = el.offsetLeft;
		const winTop = el.offsetTop;
		const winWidth = el.offsetWidth;
		const winHeight = el.offsetHeight;
		
		// Calculate the window's bounding box in the viewport's coordinate system
		const viewLeft = (winLeft * this.scale) + this.panX;
		const viewTop = (winTop * this.scale) + this.panY;
		const viewRight = viewLeft + (winWidth * this.scale);
		const viewBottom = viewTop + (winHeight * this.scale);
		
		const viewportWidth = this.viewport.clientWidth;
		const viewportHeight = this.viewport.clientHeight;
		
		let deltaX = 0;
		let deltaY = 0;
		
		// Check horizontal position.
		// The `else if` is important for windows wider than the viewport, preventing jitter.
		// It prioritizes aligning the left edge.
		if (viewLeft < padding) {
			// Window's left edge is off-screen to the left. Pan right.
			deltaX = padding - viewLeft;
		} else if (viewRight > viewportWidth - padding) {
			// Window's right edge is off-screen to the right. Pan left.
			deltaX = (viewportWidth - padding) - viewRight;
		}
		
		// Check vertical position.
		// Prioritizes aligning the top edge for windows taller than the viewport.
		if (viewTop < padding) {
			// Window's top edge is off-screen to the top. Pan down.
			deltaY = padding - viewTop;
		} else if (viewBottom > viewportHeight - padding) {
			// Window's bottom edge is off-screen to the bottom. Pan up.
			deltaY = (viewportHeight - padding) - viewBottom;
		}
		
		// Apply the calculated adjustments if any are needed.
		if (deltaX !== 0 || deltaY !== 0) {
			this.panX += deltaX;
			this.panY += deltaY;
			this.updateCanvasTransform(true); // Animate the pan
			this.saveState();
		}
	}
	
	/**
	 * MODIFIED: Brings a window to the front and handles multi-selection logic.
	 * @param {string} windowId The ID of the window being interacted with.
	 * @param {MouseEvent|null} event The mousedown event, used to check for the Shift key.
	 */
	focus(windowId, event = null) {
		const win = this.windows.get(windowId);
		if (!win) return;
		
		const isShiftPressed = event && event.shiftKey;
		
		// Always bring the clicked window to the front and make it active.
		if (this.activeWindow && this.windows.has(this.activeWindow)) {
			this.windows.get(this.activeWindow).element.classList.remove('active');
		}
		win.element.style.zIndex = this.highestZIndex++;
		win.element.classList.add('active');
		this.activeWindow = windowId;
		
		if (isShiftPressed) {
			// Toggle selection with the Shift key.
			if (this.selectedWindows.has(windowId)) {
				this.selectedWindows.delete(windowId);
				win.element.classList.remove('selected');
			} else {
				this.selectedWindows.add(windowId);
				win.element.classList.add('selected');
			}
		} else {
			// Standard click.
			// If clicking on a window that is NOT part of the current selection,
			// clear the old selection and select only this one.
			if (!this.selectedWindows.has(windowId)) {
				this._clearSelection();
				this.selectedWindows.add(windowId);
				win.element.classList.add('selected');
			}
			// If clicking on a window that IS part of a multi-selection, we don't clear it.
			// This allows the user to click-and-drag the group.
		}
		
		this.scrollIntoView(windowId);
		this.saveState();
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
			this.selectedWindows.delete(windowId); // NEW: Remove from selection on close.
			
			// NEW: If this was a codex entry window, remove its associated modals from the body.
			if (windowId.startsWith('codex-entry-')) {
				const entryId = windowId.replace('codex-entry-', '');
				const aiModal = document.getElementById(`ai-modal-${entryId}`);
				const uploadModal = document.getElementById(`upload-modal-${entryId}`);
				if (aiModal) aiModal.remove();
				if (uploadModal) uploadModal.remove();
			}
			
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
		
		// NEW: Deselect the window when it's minimized.
		win.element.classList.remove('selected');
		this.selectedWindows.delete(windowId);
		
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
		this.focus(windowId); // focus() now handles bringing it into view.
		
		this.updateTaskbar();
		this.saveState();
	}
	
	/**
	 * MODIFIED: Toggles a window between maximized and its original size.
	 * When maximizing, it now also sets the canvas zoom to 100% and centers the view on the window.
	 */
	maximize(windowId) {
		const win = this.windows.get(windowId);
		if (!win) return;
		
		if (win.isMaximized) {
			// Restore to original state
			win.element.style.width = `${win.originalRect.width}px`;
			win.element.style.height = `${win.originalRect.height}px`;
			win.element.style.left = `${win.originalRect.x}px`;
			win.element.style.top = `${win.originalRect.y}px`;
			win.isMaximized = false;
		} else {
			// Maximize: set zoom to 100% and center the window.
			win.originalRect = {
				x: win.element.offsetLeft,
				y: win.element.offsetTop,
				width: win.element.offsetWidth,
				height: win.element.offsetHeight
			};
			
			// Set zoom to 100%.
			this.scale = 1;
			
			// Calculate max dimensions, with some padding from the viewport edges.
			const maxW = Math.min(this.viewport.clientWidth * 0.9, 1600);
			const maxH = Math.min((this.viewport.clientHeight - this.taskbar.offsetHeight) * 0.9, 1200);
			
			win.element.style.width = `${maxW}px`;
			win.element.style.height = `${maxH}px`;
			
			win.isMaximized = true;
			setTimeout(() => {
				this.scrollIntoView(windowId);
			}, 250);
			
		}
		this.saveState();
	}
	
	/**
	 * MODIFIED: Adds drag functionality to a window, now supporting group dragging.
	 */
	makeDraggable(win, handle) {
		const onMouseMove = (e) => {
			// Iterate over all selected windows and move them in unison.
			this.selectedWindows.forEach(id => {
				const currentWinEl = this.windows.get(id).element;
				
				// Calculate new position based on this specific window's starting point.
				let newLeft = currentWinEl.startLeft + (e.clientX - currentWinEl.startX) / this.scale;
				let newTop = currentWinEl.startTop + (e.clientY - currentWinEl.startY) / this.scale;
				
				const desktopWidth = this.desktop.offsetWidth;
				const desktopHeight = this.desktop.offsetHeight;
				const winWidth = currentWinEl.offsetWidth;
				const winHeight = currentWinEl.offsetHeight;
				
				// Clamp the position for each window within the desktop boundaries.
				newLeft = Math.max(0, Math.min(newLeft, desktopWidth - winWidth));
				newTop = Math.max(0, Math.min(newTop, desktopHeight - winHeight));
				
				currentWinEl.style.left = `${newLeft}px`;
				currentWinEl.style.top = `${newTop}px`;
			});
		};
		
		const onMouseUp = () => {
			win.classList.remove('dragging');
			document.removeEventListener('mousemove', onMouseMove);
			document.removeEventListener('mouseup', onMouseUp);
			
			// Update the stored position for all moved windows.
			this.selectedWindows.forEach(id => {
				const winState = this.windows.get(id);
				if (winState && !winState.isMaximized) {
					winState.originalRect.x = winState.element.offsetLeft;
					winState.originalRect.y = winState.element.offsetTop;
				}
			});
			
			this.saveState();
		};
		
		handle.addEventListener('mousedown', (e) => {
			const winState = this.windows.get(win.id);
			if (winState && winState.isMaximized) return;
			
			// The focus() event, which fires on mousedown, handles the selection logic.
			// This ensures the clicked window is part of the selection before dragging starts.
			
			win.classList.add('dragging');
			
			// Store start positions for all selected windows relative to the initial mouse click.
			this.selectedWindows.forEach(id => {
				const currentWinEl = this.windows.get(id).element;
				currentWinEl.startX = e.clientX;
				currentWinEl.startY = e.clientY;
				currentWinEl.startLeft = currentWinEl.offsetLeft;
				currentWinEl.startTop = currentWinEl.offsetTop;
			});
			
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
			let newWidth = startWidth + (e.clientX - startX) / this.scale;
			let newHeight = startHeight + (e.clientY - startY) / this.scale;
			
			// MODIFIED: Constrain window size to max limits and desktop boundaries.
			const maxW = Math.min(this.viewport.clientWidth / this.scale, 1600);
			const maxH = Math.min(this.viewport.clientHeight / this.scale, 1200);
			
			// Apply max size constraints.
			newWidth = Math.min(newWidth, maxW);
			newHeight = Math.min(newHeight, maxH);
			
			// Ensure the window does not resize beyond the desktop's right and bottom edges.
			if (win.offsetLeft + newWidth > this.desktop.offsetWidth) {
				newWidth = this.desktop.offsetWidth - win.offsetLeft;
			}
			if (win.offsetTop + newHeight > this.desktop.offsetHeight) {
				newHeight = this.desktop.offsetHeight - win.offsetTop;
			}
			
			win.style.width = `${newWidth}px`;
			win.style.height = `${newHeight}px`;
		};
		
		const onMouseUp = () => {
			win.classList.remove('dragging');
			document.removeEventListener('mousemove', onMouseMove);
			document.removeEventListener('mouseup', onMouseUp);
			
			// Update the window's stored dimensions before saving state.
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
	 * Saves the state of all windows and the canvas to the database via fetch.
	 * This is now debounced to prevent excessive API calls.
	 */
	saveState() {
		// Clear any existing timer to reset the debounce period.
		if (this.saveStateTimeout) {
			clearTimeout(this.saveStateTimeout);
		}
		
		// Set a new timer to perform the save after a short delay.
		this.saveStateTimeout = setTimeout(() => {
			this._performSaveState();
		}, 1000);
	}
	
	/**
	 * The actual implementation of the state saving logic.
	 * This is called by the debounced saveState method.
	 */
	async _performSaveState() {
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
	 * Loads window state from the data attribute provided by the server.
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
		
		// Place windows near the center of the 5000x5000 canvas.
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
	
	// --- CANVAS PAN AND ZOOM METHODS ---
	
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
		// Check if the scroll event is happening inside a window's content area.
		const scrollContainer = event.target.closest('.overflow-auto, .overflow-y-auto');
		
		if (scrollContainer) {
			// This check ensures that we only block zooming if there's actually something to scroll.
			const hasVerticalScroll = scrollContainer.scrollHeight > scrollContainer.clientHeight;
			const hasHorizontalScroll = scrollContainer.scrollWidth > scrollContainer.clientWidth;
			
			if (hasVerticalScroll || hasHorizontalScroll) {
				return; // Let the browser handle scrolling inside the window.
			}
		}
		
		event.preventDefault();
		const zoomIntensity = 0.01;
		const delta = event.deltaY > 0 ? -zoomIntensity : zoomIntensity;
		// MODIFIED: Limit max zoom to 100% (1.0).
		const newScale = Math.max(0.1, Math.min(1, this.scale + delta * this.scale));
		
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
		this.saveState();
	}
	
	/**
	 * Handles the mousedown event to initiate panning.
	 */
	handlePanStart(event) {
		// Only pan when clicking directly on the desktop, not on a window or its contents.
		if (event.target === this.desktop) {
			this._clearSelection(); // NEW: Deselect all windows when clicking the desktop.
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
			this.saveState();
		}
	}
	
	/**
	 * Zooms in on the center of the viewport.
	 */
	zoomIn() {
		// MODIFIED: Limit max zoom to 100% (1.0).
		this.scale = Math.min(1, this.scale * 1.2);
		this.updateCanvasTransform(true);
		this.saveState();
	}
	
	/**
	 * Zooms out from the center of the viewport.
	 */
	zoomOut() {
		this.scale = Math.max(0.1, this.scale / 1.2);
		this.updateCanvasTransform(true);
		this.saveState();
	}
	
	/**
	 * NEW: Zooms to a specific scale, keeping the viewport center stationary.
	 * @param {number} targetScale The target scale factor.
	 * @param {boolean} [animated=true] - Whether to animate the transition.
	 */
	zoomTo(targetScale, animated = true) {
		const viewportCenterX = this.viewport.clientWidth / 2;
		const viewportCenterY = this.viewport.clientHeight / 2;
		
		// Find what point on the canvas is at the center of the viewport
		const canvasPointX = (viewportCenterX - this.panX) / this.scale;
		const canvasPointY = (viewportCenterY - this.panY) / this.scale;
		
		this.scale = targetScale;
		
		// Calculate the new pan values to keep that point at the center
		this.panX = viewportCenterX - (canvasPointX * this.scale);
		this.panY = viewportCenterY - (canvasPointY * this.scale);
		
		this.updateCanvasTransform(animated);
		this.saveState();
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
		this.saveState();
	}
	
	// --- END: CANVAS METHODS ---
	
	/**
	 * NEW: Helper method to deselect all windows.
	 */
	_clearSelection() {
		this.selectedWindows.forEach(id => {
			const win = this.windows.get(id);
			if (win) {
				win.element.classList.remove('selected');
			}
		});
		this.selectedWindows.clear();
	}
	
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
