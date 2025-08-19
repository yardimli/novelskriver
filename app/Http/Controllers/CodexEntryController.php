<?php

	namespace App\Http\Controllers;

	use App\Http\Controllers\LlmController; // MODIFIED: Import LlmController.
	use App\Models\CodexEntry;
	use App\Models\Image;
	use App\Models\Novel;
	use App\Services\FalAiService;
	use App\Services\ImageUploadService;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\Request;
	use Illuminate\Http\UploadedFile;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Facades\Validator;
	use Illuminate\Validation\Rule;
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

			// Eager load the image and any linked entries (and their images).
			$codexEntry->load('image', 'linkedEntries.image');

			return view('novel-editor.partials.codex-entry-window', compact('codexEntry'));
		}

		/**
		 * Store a newly created codex entry in storage.
		 *
		 * @param Request $request
		 * @param Novel $novel
		 * @param ImageUploadService $imageUploader
		 * @return JsonResponse
		 */
		public function store(Request $request, Novel $novel, ImageUploadService $imageUploader): JsonResponse
		{
			// Authorization check
			if (Auth::id() !== $novel->user_id) {
				return response()->json(['message' => 'Unauthorized'], 403);
			}

			$validator = Validator::make($request->all(), [
				'title' => 'required|string|max:255',
				'description' => 'nullable|string|max:1000',
				'content' => 'nullable|string',
				'codex_category_id' => [
					'nullable',
					Rule::exists('codex_categories', 'id')->where(function ($query) use ($novel) {
						$query->where('novel_id', $novel->id);
					}),
				],
				'new_category_name' => 'nullable|string|max:255',
				'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
			]);

			// Custom validation logic to ensure a category is selected or created.
			$validator->after(function ($validator) use ($request) {
				if (empty($request->input('codex_category_id')) && empty($request->input('new_category_name'))) {
					$validator->errors()->add('codex_category_id', 'A category is required. Please select an existing one or create a new one.');
				}
			});

			if ($validator->fails()) {
				return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
			}

			try {
				$categoryId = $request->input('codex_category_id');
				$newCategoryData = null;

				DB::beginTransaction();

				// Create a new category if a name is provided
				if ($request->filled('new_category_name')) {
					$newCategory = $novel->codexCategories()->create([
						'name' => $request->input('new_category_name'),
					]);
					$categoryId = $newCategory->id;
					$newCategoryData = ['id' => $newCategory->id, 'name' => $newCategory->name];
				}

				// Create the codex entry
				$codexEntry = $novel->codexEntries()->create([
					'codex_category_id' => $categoryId,
					'title' => $request->input('title'),
					'description' => $request->input('description'),
					'content' => $request->input('content'),
				]);

				// Handle image upload if present
				if ($request->hasFile('image')) {
					/** @var UploadedFile $imageFile */
					$imageFile = $request->file('image');
					$paths = $imageUploader->uploadImageWithThumbnail(
						file: $imageFile,
						uploadConfigKey: 'novel_codex_entries',
						customSubdirectory: (string) $novel->id . '/' . $codexEntry->id,
						customFilenameBase: 'codex-image-upload'
					);

					Image::create([
						'user_id' => Auth::id(),
						'novel_id' => $novel->id,
						'codex_entry_id' => $codexEntry->id,
						'image_local_path' => $paths['original_path'],
						'thumbnail_local_path' => $paths['thumbnail_path'],
						'image_type' => 'upload',
					]);
				}

				DB::commit();

				// Eager load the image for the response payload
				$codexEntry->load('image');

				return response()->json([
					'success' => true,
					'message' => 'Codex entry created successfully.',
					'codexEntry' => [
						'id' => $codexEntry->id,
						'title' => $codexEntry->title,
						'description' => $codexEntry->description,
						'thumbnail_url' => $codexEntry->thumbnail_url,
						'category_id' => $codexEntry->codex_category_id,
					],
					'newCategory' => $newCategoryData,
				], 201);
			} catch (Throwable $e) {
				DB::rollBack();
				Log::error('Failed to create codex entry for novel ID ' . $novel->id . ': ' . $e->getMessage());
				return response()->json(['message' => 'Failed to create codex entry: ' . $e->getMessage()], 500);
			}
		}

		/**
		 * NEW: Update the specified codex entry in storage.
		 *
		 * @param Request $request
		 * @param CodexEntry $codexEntry
		 * @return JsonResponse
		 */
		public function update(Request $request, CodexEntry $codexEntry): JsonResponse
		{
			// Authorization check
			if (Auth::id() !== $codexEntry->novel->user_id) {
				return response()->json(['message' => 'Unauthorized'], 403);
			}

			$validator = Validator::make($request->all(), [
				'title' => 'sometimes|required|string|max:255',
				'description' => 'nullable|string|max:1000',
				'content' => 'nullable|string',
			]);

			if ($validator->fails()) {
				return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
			}

			try {
				$codexEntry->update($validator->validated());

				return response()->json([
					'success' => true,
					'message' => 'Codex entry updated successfully.'
				]);
			} catch (Throwable $e) {
				Log::error('Failed to update codex entry ID ' . $codexEntry->id . ': ' . $e->getMessage());
				return response()->json(['message' => 'Failed to update codex entry: ' . $e->getMessage()], 500);
			}
		}

		/**
		 * NEW: Process a text selection using an LLM for actions like rephrasing.
		 *
		 * @param Request $request
		 * @param CodexEntry $codexEntry
		 * @param LlmController $llm
		 * @return JsonResponse
		 */
		public function processText(Request $request, CodexEntry $codexEntry, LlmController $llm): JsonResponse
		{
			// Authorization check
			if (Auth::id() !== $codexEntry->novel->user_id) {
				return response()->json(['message' => 'Unauthorized'], 403);
			}

			$validator = Validator::make($request->all(), [
				'text' => 'required|string',
				'action' => ['required', Rule::in(['expand', 'rephrase', 'shorten'])],
				'model' => 'required|string',
			]);

			if ($validator->fails()) {
				return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
			}

			try {
				$validated = $validator->validated();
				$prompt = $this->buildProcessTextPrompt($validated['text'], $validated['action']);

				$response = $llm->callLlmSync(
					prompt: $prompt,
					modelId: $validated['model'],
					callReason: 'Process Codex Text',
					temperature: 0.7,
					responseFormat: 'json_object'
				);

				if (isset($response['processed_text'])) {
					return response()->json(['success' => true, 'text' => $response['processed_text']]);
				}

				throw new \Exception('Invalid response format from LLM.');
			} catch (Throwable $e) {
				Log::error('Failed to process text for codex entry ID ' . $codexEntry->id . ': ' . $e->getMessage());
				return response()->json(['message' => 'Failed to process text: ' . $e->getMessage()], 500);
			}
		}

		/**
		 * NEW: Helper to build the prompt for text processing.
		 * @param string $text
		 * @param string $action
		 * @return string
		 */
		private function buildProcessTextPrompt(string $text, string $action): string
		{
			$actionInstruction = match ($action) {
				'expand' => 'Expand on the following text, adding more detail, description, and context. Make it about twice as long.',
				'rephrase' => 'Rephrase the following text to make it clearer, more engaging, or to have a different tone, while preserving the core meaning.',
				'shorten' => 'Shorten the following text, condensing it to its most essential points. Make it about half as long.',
				default => 'Process the following text.',
			};

			return <<<PROMPT
You are a writing assistant. Your task is to process a piece of text based on a specific instruction.

**Instruction:** {$actionInstruction}

**Original Text:**
"{$text}"

Please provide only the modified text as your response. The output must be a single, valid JSON object with one key: "processed_text". Do not include any explanations or surrounding text.
PROMPT;
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
		 * Upload an image for a codex entry.
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

		/**
		 * Attach a codex entry to another codex entry.
		 *
		 * @param CodexEntry $codexEntry
		 * @param CodexEntry $linkedCodexEntry
		 * @return JsonResponse
		 */
		public function attachLink(CodexEntry $codexEntry, CodexEntry $linkedCodexEntry): JsonResponse
		{
			// Authorization check
			if (Auth::id() !== $codexEntry->novel->user_id || Auth::id() !== $linkedCodexEntry->novel->user_id) {
				return response()->json(['message' => 'Unauthorized'], 403);
			}

			// Prevent linking to self
			if ($codexEntry->id === $linkedCodexEntry->id) {
				return response()->json(['message' => 'Cannot link an entry to itself.'], 422);
			}

			// Use syncWithoutDetaching to add the link without affecting existing ones and prevent duplicates.
			$codexEntry->linkedEntries()->syncWithoutDetaching($linkedCodexEntry->id);

			// Eager load the image for the response payload.
			$linkedCodexEntry->load('image');

			return response()->json([
				'success' => true,
				'message' => 'Codex entry linked successfully.',
				'codexEntry' => [
					'id' => $linkedCodexEntry->id,
					'title' => $linkedCodexEntry->title,
					'thumbnail_url' => $linkedCodexEntry->thumbnail_url,
				]
			]);
		}

		/**
		 * Detach a codex entry from another codex entry.
		 *
		 * @param CodexEntry $codexEntry
		 * @param CodexEntry $linkedCodexEntry
		 * @return JsonResponse
		 */
		public function detachLink(CodexEntry $codexEntry, CodexEntry $linkedCodexEntry): JsonResponse
		{
			// Authorization check
			if (Auth::id() !== $codexEntry->novel->user_id || Auth::id() !== $linkedCodexEntry->novel->user_id) {
				return response()->json(['message' => 'Unauthorized'], 403);
			}

			// Detach the specific link.
			$codexEntry->linkedEntries()->detach($linkedCodexEntry->id);

			return response()->json(['success' => true, 'message' => 'Codex entry unlinked.']);
		}
	}
