<?php

	namespace App\Http\Controllers;

	use App\Models\CodexEntry;
	use App\Models\Image;
	use App\Services\FalAiService;
	use App\Services\ImageUploadService;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\Request;
	use Illuminate\Http\UploadedFile;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Facades\Validator;
	use Illuminate\View\View;
	use Throwable;

	/**
	 * Controller to manage individual codex entries.
	 */
	class CodexEntryController extends Controller
	{
		/**
		 * Display a partial view for a single codex entry.
		 *
		 * @param CodexEntry $codexEntry
		 * @return View|JsonResponse
		 */
		public function show(CodexEntry $codexEntry): View|JsonResponse
		{
			// Authorization check
			if (Auth::id() !== $codexEntry->novel->user_id) {
				return response()->json(['message' => 'Unauthorized'], 403);
			}

			$codexEntry->load('image');

			return view('novel-editor.partials.codex-entry-window', compact('codexEntry'));
		}

		/**
		 * Generate an image for a codex entry using AI.
		 *
		 * @param Request $request
		 * @param CodexEntry $codexEntry
		 * @param FalAiService $fal
		 * @param ImageUploadService $imageUploader
		 * @return JsonResponse
		 */
		public function generateImage(Request $request, CodexEntry $codexEntry, FalAiService $fal, ImageUploadService $imageUploader): JsonResponse
		{
			// Authorization check
			if (Auth::id() !== $codexEntry->novel->user_id) {
				return response()->json(['message' => 'Unauthorized'], 403);
			}

			$validator = Validator::make($request->all(), [
				'prompt' => 'required|string|max:1000',
			]);

			if ($validator->fails()) {
				return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
			}

			try {
				$imagePrompt = $request->input('prompt');

				// Generate the image using Fal.ai with the specified size.
				$imageUrl = $fal->generateImage($imagePrompt, ['image_size' => 'square_hd']);

				if (!$imageUrl) {
					throw new \Exception('Failed to get image URL from AI service.');
				}

				// Download and store the image.
				$paths = $imageUploader->storeImageFromUrl(
					url: $imageUrl,
					uploadConfigKey: 'novel_codex_entries',
					customSubdirectory: (string) $codexEntry->novel_id . '/' . $codexEntry->id,
					customFilenameBase: 'codex-image'
				);

				// Delete old image if it exists
				if ($codexEntry->image) {
					Storage::disk('public')->delete([$codexEntry->image->image_local_path, $codexEntry->image->thumbnail_local_path]);
					$codexEntry->image->delete();
				}

				// Save new image record to the database.
				$image = Image::create([
					'user_id' => Auth::id(),
					'novel_id' => $codexEntry->novel_id,
					'codex_entry_id' => $codexEntry->id,
					'image_local_path' => $paths['original_path'],
					'thumbnail_local_path' => $paths['thumbnail_path'],
					'remote_url' => $imageUrl,
					'prompt' => $imagePrompt,
					'image_type' => 'generated',
				]);

				// Update the codex entry's direct path for simplicity, though relationship is primary.
				$codexEntry->update(['image_path' => $paths['original_path']]);

				return response()->json([
					'success' => true,
					'message' => 'Image generated successfully!',
					'image_url' => Storage::disk('public')->url($image->image_local_path)
				]);
			} catch (Throwable $e) {
				Log::error('Failed to generate codex entry image for entry ID ' . $codexEntry->id . ': ' . $e->getMessage());
				return response()->json(['message' => 'Failed to generate image: ' . $e->getMessage()], 500);
			}
		}

		/**
		 * NEW: Upload an image for a codex entry.
		 *
		 * @param Request $request
		 * @param CodexEntry $codexEntry
		 * @param ImageUploadService $imageUploader
		 * @return JsonResponse
		 */
		public function uploadImage(Request $request, CodexEntry $codexEntry, ImageUploadService $imageUploader): JsonResponse
		{
			// Authorization check
			if (Auth::id() !== $codexEntry->novel->user_id) {
				return response()->json(['message' => 'Unauthorized'], 403);
			}

			$validator = Validator::make($request->all(), [
				'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
			]);

			if ($validator->fails()) {
				return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
			}

			try {
				/** @var UploadedFile $imageFile */
				$imageFile = $request->file('image');

				// Store the new image.
				$paths = $imageUploader->uploadImageWithThumbnail(
					file: $imageFile,
					uploadConfigKey: 'novel_codex_entries',
					customSubdirectory: (string) $codexEntry->novel_id . '/' . $codexEntry->id,
					customFilenameBase: 'codex-image-upload'
				);

				// Delete old image if it exists
				if ($codexEntry->image) {
					Storage::disk('public')->delete([$codexEntry->image->image_local_path, $codexEntry->image->thumbnail_local_path]);
					$codexEntry->image->delete();
				}

				// Save new image record to the database.
				$image = Image::create([
					'user_id' => Auth::id(),
					'novel_id' => $codexEntry->novel_id,
					'codex_entry_id' => $codexEntry->id,
					'image_local_path' => $paths['original_path'],
					'thumbnail_local_path' => $paths['thumbnail_path'],
					'remote_url' => null,
					'prompt' => null,
					'image_type' => 'upload',
				]);

				// Update the codex entry's direct path for simplicity.
				$codexEntry->update(['image_path' => $paths['original_path']]);

				return response()->json([
					'success' => true,
					'message' => 'Image uploaded successfully!',
					'image_url' => Storage::disk('public')->url($image->image_local_path)
				]);
			} catch (Throwable $e) {
				Log::error('Failed to upload codex entry image for entry ID ' . $codexEntry->id . ': ' . $e->getMessage());
				return response()->json(['message' => 'Failed to upload image: ' . $e->getMessage()], 500);
			}
		}
	}
