/**
 * Codex Entry Window Interaction Manager
 *
 * Handles AI image generation, manual uploads, and codex-to-codex linking
 * for codex entry windows. Uses event delegation on the main #desktop element.
 */
document.addEventListener('DOMContentLoaded', () => {
	const desktop = document.getElementById('desktop');
	if (!desktop) return;
	
	// --- Modal Management ---
	const openModal = (modal) => {
		if (modal) modal.classList.remove('hidden');
	};
	
	const closeModal = (modal) => {
		if (modal) {
			modal.classList.add('hidden');
			// Reset form state to be clean for next opening
			const form = modal.querySelector('form');
			if (form) {
				form.reset();
				// Special handling for upload preview and button state
				const previewContainer = form.querySelector('.js-image-preview-container');
				if (previewContainer) previewContainer.classList.add('hidden');
				const fileNameSpan = form.querySelector('.js-file-name');
				if (fileNameSpan) fileNameSpan.textContent = 'Click to select a file';
				const submitBtn = form.querySelector('button[type="submit"]');
				if (submitBtn) submitBtn.disabled = true;
			}
		}
	};
	
	/**
	 * Toggles the loading state of a form submission button.
	 * @param {HTMLButtonElement} button The button element.
	 * @param {boolean} isLoading True to show spinner, false to show text.
	 */
	const setButtonLoadingState = (button, isLoading) => {
		const text = button.querySelector('.js-btn-text');
		const spinner = button.querySelector('.js-spinner');
		if (isLoading) {
			button.disabled = true;
			text.classList.add('hidden');
			spinner.classList.remove('hidden');
		} else {
			button.disabled = false;
			text.classList.remove('hidden');
			spinner.classList.add('hidden');
		}
	};
	
	// --- Event Delegation for Modal Triggers and Closers ---
	document.body.addEventListener('click', (event) => {
		const target = event.target;
		
		// Handle opening modals (triggers are inside windows on the desktop)
		const openTrigger = target.closest('.js-codex-generate-ai, .js-codex-upload-image');
		if (openTrigger) {
			const windowEl = openTrigger.closest('.codex-entry-window-content');
			if (!windowEl) return;
			
			const entryId = windowEl.dataset.entryId;
			
			// Open AI Modal
			if (openTrigger.matches('.js-codex-generate-ai')) {
				const modal = document.getElementById(`ai-modal-${entryId}`);
				// Pre-fill prompt with a suggestion
				const textarea = modal.querySelector('textarea');
				textarea.value = `A detailed portrait of ${windowEl.dataset.entryTitle}, fantasy art.`;
				openModal(modal);
			}
			
			// Open Upload Modal
			if (openTrigger.matches('.js-codex-upload-image')) {
				const modal = document.getElementById(`upload-modal-${entryId}`);
				openModal(modal);
			}
			return; // Event handled
		}
		
		// Handle closing modals (triggers are inside modals in the body)
		const closeTrigger = target.closest('.js-close-modal');
		if (closeTrigger) {
			const modal = closeTrigger.closest('.js-ai-modal, .js-upload-modal');
			closeModal(modal);
		}
	});
	
	// --- AI Generation Form Submission ---
	document.body.addEventListener('submit', async (event) => {
		if (!event.target.matches('.js-ai-form')) return;
		event.preventDefault();
		
		const form = event.target;
		const modal = form.closest('.js-ai-modal');
		const entryId = modal.id.replace('ai-modal-', '');
		const windowEl = document.querySelector(`.codex-entry-window-content[data-entry-id="${entryId}"]`);
		const submitBtn = form.querySelector('.js-ai-submit-btn');
		const prompt = new FormData(form).get('prompt');
		
		if (!prompt || prompt.trim() === '') {
			alert('Please enter a prompt.');
			return;
		}
		
		setButtonLoadingState(submitBtn, true);
		const imageContainer = windowEl.querySelector('.codex-image-container');
		const imgEl = imageContainer.querySelector('img');
		imageContainer.classList.add('opacity-50');
		
		try {
			const response = await fetch(`/codex-entries/${entryId}/generate-image`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
					'Accept': 'application/json',
				},
				body: JSON.stringify({ prompt }),
			});
			
			const data = await response.json();
			if (!response.ok) throw new Error(data.message || 'An unknown error occurred.');
			
			imgEl.src = data.image_url + '?t=' + new Date().getTime();
			closeModal(modal);
			
		} catch (error) {
			console.error('AI Image Generation Error:', error);
			alert('Failed to generate image: ' + error.message);
		} finally {
			setButtonLoadingState(submitBtn, false);
			imageContainer.classList.remove('opacity-50');
		}
	});
	
	// --- Manual Upload Form Submission ---
	document.body.addEventListener('submit', async (event) => {
		if (!event.target.matches('.js-upload-form')) return;
		event.preventDefault();
		
		const form = event.target;
		const modal = form.closest('.js-upload-modal');
		const entryId = modal.id.replace('upload-modal-', '');
		const windowEl = document.querySelector(`.codex-entry-window-content[data-entry-id="${entryId}"]`);
		const submitBtn = form.querySelector('.js-upload-submit-btn');
		const formData = new FormData(form);
		
		setButtonLoadingState(submitBtn, true);
		const imageContainer = windowEl.querySelector('.codex-image-container');
		const imgEl = imageContainer.querySelector('img');
		imageContainer.classList.add('opacity-50');
		
		try {
			const response = await fetch(`/codex-entries/${entryId}/upload-image`, {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
					'Accept': 'application/json',
				},
				body: formData,
			});
			
			const data = await response.json();
			if (!response.ok) {
				const errorMsg = data.errors?.image?.[0] || data.message || 'Upload failed.';
				throw new Error(errorMsg);
			}
			
			imgEl.src = data.image_url + '?t=' + new Date().getTime();
			closeModal(modal);
			
		} catch (error) {
			console.error('Image Upload Error:', error);
			alert('Failed to upload image: ' + error.message);
		} finally {
			setButtonLoadingState(submitBtn, false);
			imageContainer.classList.remove('opacity-50');
		}
	});
	
	// --- File Input Change for Preview in Upload Modal ---
	document.body.addEventListener('change', (event) => {
		if (!event.target.matches('input[type="file"][name="image"]')) return;
		
		const input = event.target;
		const form = input.closest('form');
		const file = input.files[0];
		const previewContainer = form.querySelector('.js-image-preview-container');
		const previewImg = form.querySelector('.js-image-preview');
		const fileNameSpan = form.querySelector('.js-file-name');
		const submitBtn = form.querySelector('button[type="submit"]');
		
		if (file) {
			const reader = new FileReader();
			reader.onload = (e) => {
				previewImg.src = e.target.result;
				previewContainer.classList.remove('hidden');
			};
			reader.readAsDataURL(file);
			fileNameSpan.textContent = file.name;
			submitBtn.disabled = false;
		} else {
			previewContainer.classList.add('hidden');
			fileNameSpan.textContent = 'Click to select a file';
			submitBtn.disabled = true;
		}
	});
	
	// --- Drag and Drop for linking Codex Entries to other Codex Entries ---
	
	// 1. Handle dragging over the codex entry window
	desktop.addEventListener('dragover', (event) => {
		// The dragstart listener in chapter-editor.js for .js-draggable-codex is sufficient.
		const dropZone = event.target.closest('.js-codex-drop-zone');
		if (dropZone) {
			event.preventDefault(); // Necessary to allow drop
			event.dataTransfer.dropEffect = 'link';
		}
	});
	
	// 2. Add visual feedback on drag enter/leave
	desktop.addEventListener('dragenter', (event) => {
		const dropZone = event.target.closest('.js-codex-drop-zone');
		if (dropZone) {
			dropZone.classList.add('bg-blue-100', 'dark:bg-blue-900/50');
		}
	});
	
	desktop.addEventListener('dragleave', (event) => {
		const dropZone = event.target.closest('.js-codex-drop-zone');
		if (dropZone && !dropZone.contains(event.relatedTarget)) {
			dropZone.classList.remove('bg-blue-100', 'dark:bg-blue-900/50');
		}
	});
	
	// 3. Handle the drop event to create the link
	desktop.addEventListener('drop', async (event) => {
		const dropZone = event.target.closest('.js-codex-drop-zone');
		if (!dropZone) return;
		
		event.preventDefault();
		dropZone.classList.remove('bg-blue-100', 'dark:bg-blue-900/50');
		
		const parentEntryId = dropZone.dataset.entryId;
		const linkedEntryId = event.dataTransfer.getData('application/x-codex-entry-id');
		
		if (!parentEntryId || !linkedEntryId || parentEntryId === linkedEntryId) {
			return; // Don't drop on self or if data is invalid
		}
		
		// Prevent dropping the same entry if it's already there
		if (dropZone.querySelector(`.js-codex-tag[data-entry-id="${linkedEntryId}"]`)) {
			return; // Silently fail if tag already exists
		}
		
		try {
			const response = await fetch(`/codex-entries/${parentEntryId}/link/${linkedEntryId}`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
					'Accept': 'application/json',
				},
			});
			
			const data = await response.json();
			if (!response.ok) throw new Error(data.message || 'Failed to link codex entry.');
			
			// Dynamically add the new tag to the UI
			const tagContainer = dropZone.querySelector('.js-codex-tags-container');
			if (tagContainer) {
				const newTag = createCodexLinkTagElement(parentEntryId, data.codexEntry);
				tagContainer.appendChild(newTag);
				// Show the container if it was hidden
				const tagsWrapper = dropZone.querySelector('.js-codex-tags-wrapper');
				if (tagsWrapper) tagsWrapper.classList.remove('hidden');
			}
		} catch (error) {
			console.error('Error linking codex entry:', error);
			alert(error.message);
		}
	});
	
	// --- Unlinking Codex Entries via click on 'x' button ---
	desktop.addEventListener('click', async (event) => {
		const removeBtn = event.target.closest('.js-remove-codex-codex-link');
		if (!removeBtn) return;
		
		const tag = removeBtn.closest('.js-codex-tag');
		const parentEntryId = removeBtn.dataset.parentEntryId;
		const linkedEntryId = removeBtn.dataset.entryId;
		const entryTitle = tag.querySelector('.js-codex-tag-title').textContent;
		
		if (!confirm(`Are you sure you want to unlink "${entryTitle}" from this entry?`)) {
			return;
		}
		
		try {
			const response = await fetch(`/codex-entries/${parentEntryId}/link/${linkedEntryId}`, {
				method: 'DELETE',
				headers: {
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
					'Accept': 'application/json',
				},
			});
			
			const data = await response.json();
			if (!response.ok) throw new Error(data.message || 'Failed to unlink codex entry.');
			
			// Remove the tag from the UI on success
			const tagContainer = tag.parentElement;
			tag.remove();
			
			// Hide the container if no tags are left
			if (tagContainer && tagContainer.children.length === 0) {
				const tagsWrapper = tagContainer.closest('.js-codex-tags-wrapper');
				if (tagsWrapper) tagsWrapper.classList.add('hidden');
			}
		} catch (error) {
			console.error('Error unlinking codex entry:', error);
			alert(error.message);
		}
	});
	
	/**
	 * Helper function to create the HTML for a new codex link tag.
	 * @param {string} parentEntryId
	 * @param {object} codexEntry
	 * @returns {HTMLElement}
	 */
	function createCodexLinkTagElement(parentEntryId, codexEntry) {
		const div = document.createElement('div');
		div.className = 'js-codex-tag group/tag relative inline-flex items-center gap-2 bg-gray-200 dark:bg-gray-700 rounded-full pr-2';
		div.dataset.entryId = codexEntry.id;
		
		div.innerHTML = `
			<button type="button"
					class="js-open-codex-entry flex items-center gap-2 pl-1 pr-2 py-1 rounded-full hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
					data-entry-id="${codexEntry.id}"
					data-entry-title="${codexEntry.title}">
				<img src="${codexEntry.thumbnail_url}" alt="Thumbnail for ${codexEntry.title}" class="w-5 h-5 object-cover rounded-full flex-shrink-0">
				<span class="js-codex-tag-title text-xs font-medium">${codexEntry.title}</span>
			</button>
			<button type="button"
					class="js-remove-codex-codex-link absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover/tag:opacity-100 transition-opacity"
					data-parent-entry-id="${parentEntryId}"
					data-entry-id="${codexEntry.id}"
					title="Unlink this entry">
				<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" viewBox="0 0 16 16">
					<path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
				</svg>
			</button>
		`;
		return div;
	}
	
	// --- Create New Codex Entry ---
	
	const newCodexModal = document.getElementById('new-codex-entry-modal');
	const newCodexForm = document.getElementById('new-codex-entry-form');
	const novelId = document.body.dataset.novelId;
	
	// 1. Open Modal
	document.body.addEventListener('click', (event) => {
		if (event.target.closest('.js-open-new-codex-modal')) {
			if (newCodexModal) {
				newCodexModal.classList.remove('hidden');
			}
		}
	});
	
	// 2. Close Modal
	if (newCodexModal) {
		newCodexModal.addEventListener('click', (event) => {
			if (event.target.closest('.js-close-new-codex-modal')) {
				resetAndCloseNewCodexModal();
			}
		});
	}
	
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && newCodexModal && !newCodexModal.classList.contains('hidden')) {
			resetAndCloseNewCodexModal();
		}
	});
	
	// 3. Handle "Create New Category" dropdown
	if (newCodexForm) {
		const categorySelect = newCodexForm.querySelector('#new-codex-category');
		const newCategoryWrapper = newCodexForm.querySelector('#new-category-wrapper');
		const newCategoryInput = newCodexForm.querySelector('#new-category-name');
		
		categorySelect.addEventListener('change', () => {
			if (categorySelect.value === 'new') {
				newCategoryWrapper.classList.remove('hidden');
			} else {
				newCategoryWrapper.classList.add('hidden');
				newCategoryInput.value = ''; // Clear input when hiding
			}
		});
	}
	
	// 4. Handle Form Submission
	if (newCodexForm) {
		newCodexForm.addEventListener('submit', async (event) => {
			event.preventDefault();
			
			const submitBtn = newCodexForm.querySelector('.js-new-codex-submit-btn');
			setButtonLoadingState(submitBtn, true);
			clearFormErrors(newCodexForm);
			
			const formData = new FormData(newCodexForm);
			
			try {
				const response = await fetch(`/novels/${novelId}/codex-entries`, {
					method: 'POST',
					headers: {
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
						'Accept': 'application/json',
					},
					body: formData,
				});
				
				const data = await response.json();
				
				if (!response.ok) {
					if (response.status === 422) {
						displayFormErrors(newCodexForm, data.errors);
					} else {
						displayGenericError(newCodexForm, data.message || 'An unknown error occurred.');
					}
					throw new Error(data.message || 'Form submission failed');
				}
				
				// --- Success ---
				resetAndCloseNewCodexModal();
				
				// Update UI
				if (data.newCategory) {
					addNewCategoryToCodexWindow(data.newCategory);
				}
				const newEntryButton = addNewEntryToCategoryList(data.codexEntry);
				
				// Open the new window by simulating a click
				if (newEntryButton) {
					newEntryButton.click();
				}
				
			} catch (error) {
				console.error('Error creating codex entry:', error);
			} finally {
				setButtonLoadingState(submitBtn, false);
			}
		});
	}
	
	/**
	 * Resets the new codex entry form and closes the modal.
	 */
	function resetAndCloseNewCodexModal() {
		if (newCodexModal) {
			newCodexModal.classList.add('hidden');
			if (newCodexForm) {
				newCodexForm.reset();
				clearFormErrors(newCodexForm);
				// Manually hide the new category input
				newCodexForm.querySelector('#new-category-wrapper').classList.add('hidden');
			}
		}
	}
	
	/**
	 * Clears all validation error messages from the form.
	 * @param {HTMLFormElement} form
	 */
	function clearFormErrors(form) {
		form.querySelectorAll('.js-error-message').forEach(el => {
			el.textContent = '';
			el.classList.add('hidden');
		});
		const genericErrorContainer = form.querySelector('#new-codex-error-container');
		if (genericErrorContainer) {
			genericErrorContainer.classList.add('hidden');
			genericErrorContainer.textContent = '';
		}
	}
	
	/**
	 * Displays validation errors on the form.
	 * @param {HTMLFormElement} form
	 * @param {object} errors
	 */
	function displayFormErrors(form, errors) {
		for (const field in errors) {
			// Handle mapping the category error to the select element
			const inputName = field === 'codex_category_id' ? 'codex_category_id' : field;
			const input = form.querySelector(`[name="${inputName}"]`);
			if (input) {
				const errorEl = input.parentElement.querySelector('.js-error-message');
				if (errorEl) {
					errorEl.textContent = errors[field][0];
					errorEl.classList.remove('hidden');
				}
			}
		}
	}
	
	/**
	 * Displays a generic error message at the top of the form.
	 * @param {HTMLFormElement} form
	 * @param {string} message
	 */
	function displayGenericError(form, message) {
		const genericErrorContainer = form.querySelector('#new-codex-error-container');
		if (genericErrorContainer) {
			genericErrorContainer.textContent = message;
			genericErrorContainer.classList.remove('hidden');
		}
	}
	
	/**
	 * Creates and appends the HTML for a new codex entry button to the correct category list.
	 * @param {object} entryData - The data for the new entry.
	 * @returns {HTMLButtonElement|null} The newly created button element.
	 */
	function addNewEntryToCategoryList(entryData) {
		const codexWindowContent = document.querySelector('#codex-window .p-4');
		if (!codexWindowContent) return null;
		
		const categoryContainer = codexWindowContent.querySelector(`#codex-category-${entryData.category_id}`);
		if (!categoryContainer) return null;
		
		const listContainer = categoryContainer.querySelector('.js-codex-entries-list');
		if (!listContainer) return null;
		
		// Remove "No entries" message if it exists
		const emptyMsg = listContainer.querySelector('p');
		if (emptyMsg) emptyMsg.remove();
		
		const button = document.createElement('button');
		button.type = 'button';
		button.className = 'js-open-codex-entry js-draggable-codex w-full text-left p-2 rounded bg-gray-100 dark:bg-gray-700/50 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500 flex items-start gap-3';
		button.dataset.entryId = entryData.id;
		button.dataset.entryTitle = entryData.title;
		button.draggable = true;
		
		button.innerHTML = `
            <img src="${entryData.thumbnail_url}" alt="Thumbnail for ${entryData.title}" class="w-12 h-12 object-cover rounded flex-shrink-0 bg-gray-300 dark:bg-gray-600 pointer-events-none">
            <div class="flex-grow min-w-0 pointer-events-none">
                <h4 class="font-semibold truncate">${entryData.title}</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">${entryData.description || ''}</p>
            </div>
        `;
		
		listContainer.appendChild(button);
		
		// Update category count
		const countSpan = categoryContainer.querySelector('.js-codex-category-count');
		if (countSpan) {
			const currentCount = listContainer.children.length;
			const itemText = currentCount === 1 ? 'item' : 'items';
			countSpan.textContent = `(${currentCount} ${itemText})`;
		}
		
		return button;
	}
	
	/**
	 * Creates and appends the HTML for a new category section in the codex window.
	 * @param {object} categoryData - The data for the new category.
	 */
	function addNewCategoryToCodexWindow(categoryData) {
		const codexWindowContent = document.querySelector('#codex-window .p-4');
		if (!codexWindowContent) return;
		
		// Check if it already exists to prevent race conditions
		if (document.getElementById(`codex-category-${categoryData.id}`)) return;
		
		const div = document.createElement('div');
		div.id = `codex-category-${categoryData.id}`;
		div.innerHTML = `
            <h3 class="text-lg font-bold text-teal-600 dark:text-teal-400 sticky top-0 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm py-2 -mx-1 px-1">
                ${categoryData.name}
                <span class="js-codex-category-count text-sm font-normal text-gray-500 dark:text-gray-400 ml-2">(0 items)</span>
            </h3>
            <div class="js-codex-entries-list mt-2 space-y-2">
                <p class="text-sm text-gray-500 px-2">No entries in this category yet.</p>
            </div>
        `;
		
		codexWindowContent.appendChild(div);
		
		// Also add the new category to the dropdown in the modal
		const categorySelect = document.getElementById('new-codex-category');
		if (categorySelect) {
			const newOption = new Option(categoryData.name, categoryData.id);
			// Insert before the "Create New" option
			categorySelect.insertBefore(newOption, categorySelect.options[categorySelect.options.length - 1]);
		}
	}
});
