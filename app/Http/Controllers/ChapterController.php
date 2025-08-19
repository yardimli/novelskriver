<?php

	namespace App\Http\Controllers;

	use App\Models\Chapter;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\View\View;

	/**
	 * Controller to manage individual chapters, primarily for the novel editor.
	 */
	class ChapterController extends Controller
	{
		/**
		 * Display a partial view for a single chapter.
		 *
		 * @param Chapter $chapter
		 * @return View|JsonResponse
		 */
		public function show(Chapter $chapter): View|JsonResponse
		{
			// Authorization check
			if (Auth::id() !== $chapter->novel->user_id) {
				return response()->json(['message' => 'Unauthorized'], 403);
			}

			// MODIFIED: Eager load codex entries (and their images) and the parent section.
			$chapter->load('section', 'codexEntries.image');

			return view('novel-editor.partials.chapter-window', compact('chapter'));
		}
	}
