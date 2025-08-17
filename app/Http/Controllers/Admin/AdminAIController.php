<?php

	namespace App\Http\Controllers\Admin;

	use App\Http\Controllers\Controller;
	use App\Models\Cover;
	use App\Models\Element;
	use App\Models\Overlay;
	use App\Models\Template;
	use App\Services\ImageUploadService;
	use App\Services\OpenAiService;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Facades\Validator;
	use Illuminate\Support\Str;
	use Illuminate\Validation\Rule;
	use Intervention\Image\Laravel\Facades\Image as InterventionImageFacade;

	class AdminAIController extends Controller
	{

		protected ImageUploadService $imageUploadService;
		protected OpenAiService $openAiService;

		public function __construct(ImageUploadService $imageUploadService, OpenAiService $openAiService)
		{
			$this->openAiService = $openAiService;
		}

		private function getModelInstance(string $itemType)
		{
			return match ($itemType) {
				'covers' => new Cover(),
				'templates' => new Template(),
				'elements' => new Element(), // Assuming Element and Overlay models also have getAllImagePaths() or similar if they have multiple images
				'overlays' => new Overlay(),
				default => null,
			};
		}

		public function generateAiMetadata(Request $request)
		{
			$validator = Validator::make($request->all(), [
				'item_type' => ['required', Rule::in(['covers', 'templates', 'elements', 'overlays'])],
				'id' => 'required|integer',
				'fields_to_generate' => 'nullable|string', // Comma-separated: name,caption,keywords,categories
			]);

			if ($validator->fails()) {
				return response()->json(['success' => false, 'message' => 'Invalid input.', 'errors' => $validator->errors()], 422);
			}

			$itemType = $request->input('item_type');
			$id = $request->input('id');
			$fieldsToGenerateInput = $request->input('fields_to_generate');
			$fieldsToGenerate = $fieldsToGenerateInput ? explode(',', $fieldsToGenerateInput) : [];
			$isBatchTargetedMode = !empty($fieldsToGenerate);

			$model = $this->getModelInstance($itemType);
			if (!$model) {
				return response()->json(['success' => false, 'message' => 'Invalid item type.'], 400);
			}
			$item = $model->find($id);
			if (!$item) {
				return response()->json(['success' => false, 'message' => ucfirst(Str::singular($itemType)) . ' not found.'], 404);
			}

			$imagePathForAi = null;
			if ($itemType === 'covers') {
				$imagePathForAi = $item->cover_path ?? $item->cover_thumbnail_path;
			} elseif ($itemType === 'templates') {
				// For templates, use cover_image_path or full_cover_image_path if more detailed
				$imagePathForAi = $item->cover_image_path ?? $item->full_cover_image_path;
			} elseif ($itemType === 'elements' || $itemType === 'overlays') {
				// Assuming old field names, adjust if these models are also refactored
				$imagePathForAi = $item->image_path ?? $item->thumbnail_path;
			}


			if (!$imagePathForAi || !Storage::disk('public')->exists($imagePathForAi)) {
				return response()->json(['success' => false, 'message' => 'Image file not found on server for AI processing.'], 404);
			}

			try {
				$imageContent = Storage::disk('public')->get($imagePathForAi);
				$base64Image = base64_encode($imageContent);
				$mimeType = Storage::disk('public')->mimeType($imagePathForAi) ?: 'image/jpeg';
				$aiGeneratedData = [];

				// Name Generation (only for covers)
				if ($itemType === 'covers' && (!$isBatchTargetedMode || in_array('name', $fieldsToGenerate))) {
					$namePrompt = "Generate a concise and descriptive 3-word name for this image based on its visual elements, style, and potential use case. The name should be suitable as a title. Output only the 3-word name. Example: 'Mystic Forest Path' or 'Cosmic Abstract Swirls'.";
					$nameResponse = $this->openAiService->generateMetadataFromImageBase64($namePrompt, $base64Image, $mimeType);
					if (isset($nameResponse['content'])) {
						$generatedName = trim($nameResponse['content']);
						if (str_word_count($generatedName) >= 2 && str_word_count($generatedName) <= 4) {
							$aiGeneratedData['name'] = Str::title($generatedName);
						} else {
							Log::warning("AI Name Generation for Cover ID {$id}: Did not return 2-4 words. Got: '{$generatedName}'");
						}
					} elseif (isset($nameResponse['error'])) {
						Log::warning("AI Name Error for cover ID {$id}: " . $nameResponse['error']);
					}
				}

				// Keywords Generation
				if (!$isBatchTargetedMode || in_array('keywords', $fieldsToGenerate)) {
					$keywordsPrompt = "Generate a list of 10-15 relevant keywords for this image, suitable for search or tagging. Include single words and relevant two-word phrases. Focus on visual elements, style, and potential use case. Output only a comma-separated list.";
					$keywordsResponse = $this->openAiService->generateMetadataFromImageBase64($keywordsPrompt, $base64Image, $mimeType);
					if (isset($keywordsResponse['content'])) {
						$parsedKeywords = $this->openAiService->parseAiListResponse($keywordsResponse['content']);
						if (!empty($parsedKeywords)) $aiGeneratedData['keywords'] = $parsedKeywords;
					} elseif (isset($keywordsResponse['error'])) {
						Log::warning("AI Keywords Error for {$itemType} ID {$id}: " . $keywordsResponse['error']);
					}
				}

				// Caption and Categories (only for covers)
				if ($itemType === 'covers') {
					if (!$isBatchTargetedMode || in_array('caption', $fieldsToGenerate)) {
						$captionPrompt = "Describe this book cover image concisely for use as an alt text or short caption. Focus on the main visual elements and mood. Do not include or describe any text visible on the image. Maximum 140 characters.";
						$captionResponse = $this->openAiService->generateMetadataFromImageBase64($captionPrompt, $base64Image, $mimeType);
						if (isset($captionResponse['content'])) {
							$aiGeneratedData['caption'] = Str::limit(trim($captionResponse['content']), 250);
						} elseif (isset($captionResponse['error'])) {
							Log::warning("AI Caption Error for cover ID {$id}: " . $captionResponse['error']);
						}
					}
					if (!$isBatchTargetedMode || in_array('categories', $fieldsToGenerate)) {
						$categoriesPrompt = "Categorize this book cover image into 1-3 relevant genres from the following list: Mystery, Thriller & Suspense, Fantasy, Science Fiction, Horror, Romance, Erotica, Children's, Action & Adventure, Chick Lit, Historical Fiction, Literary Fiction, Teen & Young Adult, Royal Romance, Western, Surreal, Paranormal & Urban, Apocalyptic, Nature, Poetry, Travel, Religion & Spirituality, Business, Self-Improvement, Education, Health & Wellness, Cookbooks & Food, Environment, Politics & Society, Family & Parenting, Abstract, Medical, Fitness, Sports, Science, Music. Output only a comma-separated list of the chosen categories.";
						$categoriesResponse = $this->openAiService->generateMetadataFromImageBase64($categoriesPrompt, $base64Image, $mimeType);
						if (isset($categoriesResponse['content'])) {
							$parsedCategories = $this->openAiService->parseAiListResponse($categoriesResponse['content']);
							if (!empty($parsedCategories)) $aiGeneratedData['categories'] = $parsedCategories;
						} elseif (isset($categoriesResponse['error'])) {
							Log::warning("AI Categories Error for cover ID {$id}: " . $categoriesResponse['error']);
						}
					}
				}

				if (empty($aiGeneratedData)) {
					$message = $isBatchTargetedMode ? 'AI did not return any usable metadata for the requested fields. Check logs.' : 'AI did not return any usable metadata or an error occurred. Check logs.';
					return response()->json(['success' => false, 'message' => $message], 500);
				}

				$item->update($aiGeneratedData);
				$updatedFieldsList = implode(', ', array_keys($aiGeneratedData));

				return response()->json([
					'success' => true,
					'message' => ucfirst(Str::singular($itemType)) . " AI metadata updated successfully for fields: {$updatedFieldsList}.",
					'data' => ['updated_fields' => array_keys($aiGeneratedData)]
				]);

			} catch (\Exception $e) {
				Log::error("AI Metadata generation error ({$itemType} ID {$id}): " . $e->getMessage() . "\n" . $e->getTraceAsString());
				return response()->json(['success' => false, 'message' => 'AI metadata generation failed: ' . $e->getMessage()], 500);
			}
		}
	}
