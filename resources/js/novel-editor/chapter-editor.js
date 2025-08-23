/**
 * This module sets up interactions for chapter windows, including drag-and-drop
 * for codex entries and the logic for linking/unlinking them via API calls.
 * @param {HTMLElement} desktop - The main desktop element to attach listeners to.
 */
export function setupChapterEditor(desktop) {
	// --- Drag and Drop for linking Codex Entries ---
	
	// 1. Handle dragging from the codex window
	desktop.addEventListener('dragstart', (event) => {
		const draggable = event.target.closest('.js-draggable-codex');
		if (draggable) {
			event.dataTransfer.setData('application/x-codex-entry-id', draggable.dataset.entryId);
			event.dataTransfer.setData('text/plain', draggable.dataset.entryId);
			event.dataTransfer.effectAllowed = 'link';
		}
	});
	
	// 2. Handle dragging over the chapter window
	desktop.addEventListener('dragover', (event) => {
		const dropZone = event.target.closest('.js-chapter-drop-zone');
		if (dropZone) {
			event.preventDefault(); // Necessary to allow drop
			event.dataTransfer.dropEffect = 'link';
		}
	});
	
	// 3. Add visual feedback on drag enter/leave
	desktop.addEventListener('dragenter', (event) => {
		const dropZone = event.target.closest('.js-chapter-drop-zone');
		if (dropZone) {
			dropZone.classList.add('bg-blue-100', 'dark:bg-blue-900/50');
		}
	});
	
	desktop.addEventListener('dragleave', (event) => {
		const dropZone = event.target.closest('.js-chapter-drop-zone');
		// Check if the leave event is not just moving to a child element
		if (dropZone && !dropZone.contains(event.relatedTarget)) {
			dropZone.classList.remove('bg-blue-100', 'dark:bg-blue-900/50');
		}
	});
	
	// 4. Handle the drop event to create the link
	desktop.addEventListener('drop', async (event) => {
		const dropZone = event.target.closest('.js-chapter-drop-zone');
		if (!dropZone) return;
		
		event.preventDefault();
		dropZone.classList.remove('bg-blue-100', 'dark:bg-blue-900/50');
		
		const chapterId = dropZone.dataset.chapterId;
		const codexEntryId = event.dataTransfer.getData('application/x-codex-entry-id');
		
		if (!chapterId || !codexEntryId) return;
		
		// Prevent dropping the same entry if it's already there
		if (dropZone.querySelector(`.js-codex-tag[data-entry-id="${codexEntryId}"]`)) {
			return;
		}
		
		try {
			const response = await fetch(`/chapters/${chapterId}/codex-entries/${codexEntryId}`, {
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
				const newTag = createCodexTagElement(chapterId, data.codexEntry);
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
		const removeBtn = event.target.closest('.js-remove-codex-link');
		if (!removeBtn) return;
		
		const tag = removeBtn.closest('.js-codex-tag');
		const chapterId = removeBtn.dataset.chapterId;
		const codexEntryId = removeBtn.dataset.entryId;
		const entryTitle = tag.querySelector('.js-codex-tag-title').textContent;
		
		if (!confirm(`Are you sure you want to unlink "${entryTitle}" from this chapter?`)) {
			return;
		}
		
		try {
			const response = await fetch(`/chapters/${chapterId}/codex-entries/${codexEntryId}`, {
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
}

/**
 * Helper function to create the HTML for a new codex tag.
 * @param {string} chapterId
 * @param {object} codexEntry
 * @returns {HTMLElement}
 */
function createCodexTagElement(chapterId, codexEntry) {
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
                class="js-remove-codex-link absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover/tag:opacity-100 transition-opacity"
                data-chapter-id="${chapterId}"
                data-entry-id="${codexEntry.id}"
                title="Unlink this entry">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
            </svg>
        </button>
    `;
	return div;
}
