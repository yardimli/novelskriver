<?php

	namespace App\Http\Controllers;

	use App\Models\Novel;
	use Illuminate\Http\JsonResponse; // NEW: Import JsonResponse.
	use Illuminate\Http\Request;
	use Illuminate\View\View;

	/**
	 * NEW: Controller to manage the novel editing interface.
	 */
	class NovelEditorController extends Controller
	{
		/**
		 * Display the novel editor interface.
		 *
		 * @param Request $request
		 * @param Novel $novel
		 * @return View
		 */
		public function index(Request $request, Novel $novel): View
		{
			// Authorization check to ensure the user owns the novel.
			if ($request->user()->id !== $novel->user_id) {
				abort(403, 'Unauthorized');
			}

			// Eager load all the necessary data for the editor to prevent N+1 query issues.
			$novel->load([
				'sections' => fn ($query) => $query->orderBy('order'),
				'sections.chapters' => fn ($query) => $query->orderBy('order'),
				'codexCategories' => fn ($query) => $query->withCount('entries')->orderBy('name'),
				'codexCategories.entries' => fn ($query) => $query->with('image')->orderBy('title'),
			]);

			return view('novel-editor.index', compact('novel'));
		}

		/**
		 * NEW: Save the editor state (window positions, canvas zoom, etc.) to the database.
		 *
		 * @param Request $request
		 * @param Novel $novel
		 * @return JsonResponse
		 */
		public function saveState(Request $request, Novel $novel): JsonResponse
		{
			// Authorization check
			if ($request->user()->id !== $novel->user_id) {
				return response()->json(['message' => 'Unauthorized'], 403);
			}

			// Basic validation to ensure we're receiving an object.
			// More specific validation could be added for windows and canvas properties.
			$validated = $request->validate([
				'state' => 'required|array',
				'state.windows' => 'sometimes|array',
				'state.canvas' => 'sometimes|array',
			]);

			$novel->editor_state = $validated['state'];
			$novel->save();

			return response()->json(['success' => true, 'message' => 'Editor state saved.']);
		}
	}
