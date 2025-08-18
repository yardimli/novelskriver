<?php

	namespace App\Http\Controllers;

	use App\Models\Novel;
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
				'codexCategories.entries' => fn ($query) => $query->orderBy('title'),
			]);

			return view('novel-editor.index', compact('novel'));
		}
	}
