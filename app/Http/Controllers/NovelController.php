<?php

	namespace App\Http\Controllers;

	use App\Http\Controllers\LlmController;
	use App\Models\Image;
	use App\Models\Novel;
	use App\Services\FalAiService;
	use App\Services\ImageUploadService;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Validator;
	use Illuminate\View\View;
	use Throwable;

	class NovelController extends Controller
	{
		/**
		 * Show the form for creating a new novel.
		 *
		 * @return View
		 */
		public function create(): View
		{
			$user = Auth::user();
			$series = $user->series()->orderBy('title')->get();
			$authors = $user->novels()->select('author')->distinct()->whereNotNull('author')->pluck('author');

			return view('novels.create', [
				'user' => $user,
				'seriesList' => $series,
				'authorList' => $authors,
			]);
		}

		/**
		 * Store a newly created novel in storage.
		 *
		 * @param  \Illuminate\Http\Request  $request
		 * @param  LlmController $llm
		 * @param  FalAiService $fal
		 * @param  ImageUploadService $imageUploader
		 * @return \Illuminate\Http\RedirectResponse
		 */
		// MODIFIED: Injected services for AI image generation.
		public function store(Request $request, LlmController $llm, FalAiService $fal, ImageUploadService $imageUploader)
		{
			$user = Auth::user();

			$validator = Validator::make($request->all(), [
				'title' => 'required|string|max:255',
				'author' => 'required|string|max:255',
				'series_id' => 'nullable|exists:series,id,user_id,' . $user->id,
				'series_index' => 'nullable|integer|min:1',
			]);

			if ($validator->fails()) {
				return redirect()->route('novels.create')
					->withErrors($validator)
					->withInput();
			}

			$novel = new Novel();
			$novel->user_id = $user->id;
			$novel->title = $request->input('title');
			$novel->author = $request->input('author');
			$novel->status = 'draft'; // Default status

			if ($request->filled('series_id')) {
				$novel->series_id = $request->input('series_id');
				$novel->order_in_series = $request->input('series_index');
			}

			$novel->save();

			// NEW: Generate a cover image for the novel using AI.
			try {
				// Step 1: Generate a prompt for the cover image using the novel's title.
				$promptGenModel = env('OPEN_ROUTER_MODEL', 'openai/gpt-4o-mini');
				$promptGenPrompt = "Based on the book title \"{$novel->title}\", create a dramatic and visually striking art prompt for an AI image generator. The prompt should describe a scene, mood, and key elements for a compelling book cover. Provide the response as a JSON object with a single key \"prompt\". Example: {\"prompt\": \"A lone astronaut standing on a desolate red planet, looking at a giant, swirling cosmic anomaly in the sky, digital art, dramatic lighting.\"}";

				$promptResponse = $llm->callLlmSync(
					prompt: $promptGenPrompt,
					modelId: $promptGenModel,
					callReason: 'Generate Novel Cover Prompt',
					temperature: 0.7,
					responseFormat: 'json_object'
				);

				if (isset($promptResponse['prompt'])) {
					$imagePrompt = $promptResponse['prompt'];

					// Step 2: Generate the image using Fal.ai.
					$imageUrl = $fal->generateImage($imagePrompt);

					if ($imageUrl) {
						// Step 3: Download and store the image.
						$paths = $imageUploader->storeImageFromUrl(
							url: $imageUrl,
							uploadConfigKey: 'novel_covers',
							customSubdirectory: (string) $novel->id,
							customFilenameBase: 'cover'
						);

						// Step 4: Save image record to the database.
						Image::create([
							'user_id' => $user->id,
							'novel_id' => $novel->id,
							'image_local_path' => $paths['original_path'],
							'thumbnail_local_path' => $paths['thumbnail_path'],
							'remote_url' => $imageUrl,
							'prompt' => $imagePrompt,
							'image_type' => 'generated',
						]);
					}
				}
			} catch (Throwable $e) {
				Log::error('Failed to generate novel cover image for novel ID ' . $novel->id . ': ' . $e->getMessage());
				// Do not fail the whole request, just log the error.
			}

			// Redirect to the novel's dashboard or editor page
			// For now, redirecting to the main dashboard with a success message.
			return redirect()->route('dashboard')->with('success', 'Novel created successfully!');
		}

		/**
		 * Generate a novel title using AI.
		 *
		 * @param  LlmController $llm
		 * @return \Illuminate\Http\JsonResponse
		 */
		public function generateTitle(LlmController $llm)
		{
			// Use the model specified in the .env file.
			$modelId = env('OPEN_ROUTER_MODEL', 'openai/gpt-4o-mini');
			$prompt = "Generate a single, funny, compelling, and unique novel title of 3 words. Do not provide any explanation or surrounding text. Think about the random number " . rand(1, 1000) . " for inspiration but dont mention the number in the title. Provide the response as a JSON object with a single key \"title\". Example: {\"title\": \"The Last Donut\"}";

			try {
				$response = $llm->callLlmSync(
					prompt: $prompt,
					modelId: $modelId,
					callReason: 'Generate Novel Title',
					temperature: 0.9,
					responseFormat: 'json_object'
				);

				if (isset($response['title'])) {
					$title = trim($response['title'], " \n\r\t\v\0.\"");
					return response()->json(['title' => $title]);
				}

				return response()->json(['error' => 'Failed to generate title due to invalid AI response format.'], 500);

			} catch (Throwable $e) {
				Log::error('Failed to generate novel title via LlmController: ' . $e->getMessage());
				return response()->json(['error' => 'Failed to generate title.'], 500);
			}
		}
	}
