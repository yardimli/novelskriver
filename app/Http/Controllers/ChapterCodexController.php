<?php

	namespace App\Http\Controllers;

	use App\Models\Chapter;
	use App\Models\CodexEntry;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Support\Facades\Auth;

	/**
	 * Controller to manage the relationship between chapters and codex entries.
	 */
	class ChapterCodexController extends Controller
	{
		/**
		 * Attach a codex entry to a chapter.
		 *
		 * @param Chapter $chapter
		 * @param CodexEntry $codexEntry
		 * @return JsonResponse
		 */
		public function attach(Chapter $chapter, CodexEntry $codexEntry): JsonResponse
		{
			// Authorization check to ensure both items belong to the user
			if (Auth::id() !== $chapter->novel->user_id || Auth::id() !== $codexEntry->novel->user_id) {
				return response()->json(['message' => 'Unauthorized'], 403);
			}

			// Use syncWithoutDetaching to add the link without affecting existing ones and prevent duplicates.
			$chapter->codexEntries()->syncWithoutDetaching($codexEntry->id);

			// Eager load the image for the response payload so the UI can render the thumbnail.
			$codexEntry->load('image');

			return response()->json([
				'success' => true,
				'message' => 'Codex entry linked successfully.',
				'codexEntry' => [
					'id' => $codexEntry->id,
					'title' => $codexEntry->title,
					'thumbnail_url' => $codexEntry->thumbnail_url,
				]
			]);
		}

		/**
		 * Detach a codex entry from a chapter.
		 *
		 * @param Chapter $chapter
		 * @param CodexEntry $codexEntry
		 * @return JsonResponse
		 */
		public function detach(Chapter $chapter, CodexEntry $codexEntry): JsonResponse
		{
			// Authorization check
			if (Auth::id() !== $chapter->novel->user_id || Auth::id() !== $codexEntry->novel->user_id) {
				return response()->json(['message' => 'Unauthorized'], 403);
			}

			// Detach the specific link.
			$chapter->codexEntries()->detach($codexEntry->id);

			return response()->json(['success' => true, 'message' => 'Codex entry unlinked.']);
		}
	}
