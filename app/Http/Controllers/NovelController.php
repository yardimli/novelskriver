<?php

	namespace App\Http\Controllers;

	use App\Http\Controllers\LlmController;
	use App\Models\Chapter;
	use App\Models\CodexCategory;
	use App\Models\CodexEntry;
	use App\Models\Image;
	use App\Models\Novel;
	use App\Models\Section;
	use App\Services\FalAiService;
	use App\Services\ImageUploadService;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\File;
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
			$novel->status = 'draft';

			if ($request->filled('series_id')) {
				$novel->series_id = $request->input('series_id');
				$novel->order_in_series = $request->input('series_index');
			}

			$novel->save();

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

		/**
		 * Generate novel structure, chapters, and codex entries using an LLM.
		 *
		 * @param Request $request
		 * @param Novel $novel
		 * @param LlmController $llm
		 * @return \Illuminate\Http\JsonResponse
		 */
		public function generateStructure(Request $request, Novel $novel, LlmController $llm)
		{
			ini_set('max_execution_time', 300); // 300 seconds = 5 minutes

			// Authorization check
			if ($request->user()->id !== $novel->user_id) {
				return response()->json(['message' => 'Unauthorized'], 403);
			}

			// Ensure novel is empty
			if ($novel->chapters()->count() > 0) {
				return response()->json(['message' => 'This novel already has chapters.'], 400);
			}

			$validator = Validator::make($request->all(), [
				'book_about' => 'required|string|min:20|max:2000',
				'book_structure' => 'required|string',
				'language' => 'required|string',
				'llm_model' => 'required|string',
			]);

			if ($validator->fails()) {
				return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
			}

			try {
				// Read structure file content
				$structurePath = resource_path('structures/' . $request->input('book_structure'));
				if (!File::exists($structurePath)) {
					return response()->json(['message' => 'Selected structure file not found.'], 400);
				}
				$structureContent = File::get($structurePath);

				// --- LLM Call 1: Generate Book Outline ---
				$outlinePrompt = $this->buildOutlinePrompt(
					$novel->title,
					$request->input('book_about'),
					$structureContent,
					$request->input('language')
				);

				$outlineResponse = $llm->callLlmSync(
					prompt: $outlinePrompt,
					modelId: $request->input('llm_model'),
					callReason: 'Generate Novel Structure',
					temperature: 0.7,
					responseFormat: 'json_object'
				);

				if (empty($outlineResponse) || !isset($outlineResponse['sections'])) {
					throw new \Exception('Failed to generate a valid novel structure from the LLM.');
				}

				// --- LLM Call 2: Generate Codex Entries ---
				$codexPrompt = $this->buildCodexPrompt(json_encode($outlineResponse), $request->input('language'));

				$codexResponse = $llm->callLlmSync(
					prompt: $codexPrompt,
					modelId: $request->input('llm_model'),
					callReason: 'Generate Novel Codex',
					temperature: 0.6,
					responseFormat: 'json_object'
				);

				// --- Database Transaction ---
				DB::transaction(function () use ($novel, $outlineResponse, $codexResponse) {
					// Update novel details
					$novel->update([
						'genre' => $outlineResponse['genre'] ?? $novel->genre,
						'logline' => $outlineResponse['logline'] ?? $novel->logline,
						'synopsis' => $outlineResponse['synopsis'] ?? $novel->synopsis,
					]);

					// Create Sections and Chapters
					$sectionOrder = 1;
					foreach ($outlineResponse['sections'] as $sectionData) {
						$section = $novel->sections()->create([
							'title' => $sectionData['title'],
							'description' => $sectionData['description'] ?? null,
							'order' => $sectionOrder++,
						]);

						$chapterOrder = 1;
						foreach ($sectionData['chapters'] as $chapterData) {
							$section->chapters()->create([
								'novel_id' => $novel->id,
								'title' => $chapterData['title'],
								'summary' => $chapterData['summary'] ?? null,
								'status' => 'in_progress',
								'order' => $chapterOrder++,
							]);
						}
					}

					// Create Codex Categories and Entries
					if (!empty($codexResponse)) {
						if (!empty($codexResponse['characters'])) {
							$charCategory = $novel->codexCategories()->firstOrCreate(
								['name' => 'Characters'],
								['description' => 'All major and minor characters in the story.']
							);
							foreach ($codexResponse['characters'] as $charData) {
								$charCategory->entries()->create([
									'novel_id' => $novel->id,
									'title' => $charData['name'],
									'description' => $charData['description'] ?? null,
									'content' => $charData['content'] ?? null,
								]);
							}
						}

						if (!empty($codexResponse['locations'])) {
							$locCategory = $novel->codexCategories()->firstOrCreate(
								['name' => 'Locations'],
								['description' => 'Key settings and places in the story.']
							);
							foreach ($codexResponse['locations'] as $locData) {
								$locCategory->entries()->create([
									'novel_id' => $novel->id,
									'title' => $locData['name'],
									'description' => $locData['description'] ?? null,
									'content' => $locData['content'] ?? null,
								]);
							}
						}
					}
				});

				return response()->json(['success' => true, 'message' => 'Novel structure generated successfully!']);

			} catch (Throwable $e) {
				Log::error('Failed to generate novel structure for novel ID ' . $novel->id . ': ' . $e->getMessage());
				return response()->json(['message' => 'An error occurred while generating the novel structure: ' . $e->getMessage()], 500);
			}
		}

		/**
		 * Helper to build the prompt for generating the novel outline.
		 * @param string $title
		 * @param string $about
		 * @param string $structure
		 * @param string $language
		 * @return string
		 */
		private function buildOutlinePrompt(string $title, string $about, string $structure, string $language): string
		{
			return <<<PROMPT
You are a master storyteller and book outliner. Your task is to create a detailed outline for a new novel.

**Novel Title:** "{$title}"
**Core Idea:** "{$about}"
**Narrative Structure to Follow:** "{$structure}"
**Language for Output:** "{$language}"

Based on the information above, generate a JSON object with the following structure:
- `genre`: A single, appropriate genre for this story (e.g., "Science Fiction", "Fantasy", "Thriller").
- `logline`: A compelling one-sentence summary of the novel.
- `synopsis`: A 3-4 paragraph summary of the entire plot.
- `sections`: An array of objects representing the main parts or acts of the book. Each section object must have:
  - `title`: The title of the section (e.g., "Act I: The Setup").
  - `description`: A brief one-sentence summary of this section's purpose.
  - `chapters`: An array of objects, with 3-5 chapters per section. Each chapter object must have:
    - `title`: A creative and fitting title for the chapter.
    - `summary`: A 2-3 sentence summary of the key events, character actions, and plot developments in this chapter.

Ensure the entire output is a single, valid JSON object. Do not include any text or markdown formatting before or after the JSON.
PROMPT;
		}

		/**
		 * Helper to build the prompt for generating codex entries.
		 * @param string $outlineJson
		 * @param string $language
		 * @return string
		 */
		private function buildCodexPrompt(string $outlineJson, string $language): string
		{
			return <<<PROMPT
You are a world-building assistant. Based on the provided novel outline, your task is to identify and create encyclopedia-style entries (a codex) for the key characters and locations.

**Novel Outline (JSON):**
{$outlineJson}

**Language for Output:** "{$language}"

From the outline, extract the most important characters and locations. Generate a JSON object with the following structure:
- `characters`: An array of objects for the main characters. Each object must have:
  - `name`: The full name of the character.
  - `description`: A one-sentence summary of their role in the story.
  - `content`: A detailed paragraph describing their personality, motivations, and background.
- `locations`: An array of objects for the key settings. Each object must have:
  - `name`: The name of the location.
  - `description`: A one-sentence summary of its significance.
  - `content`: A detailed paragraph describing the location's atmosphere, appearance, and history.

Focus on the most prominent elements mentioned in the synopsis and chapter summaries. Provide at least 3 characters and 2 locations if possible. Ensure the entire output is a single, valid JSON object. Do not include any text or markdown formatting before or after the JSON.
PROMPT;
		}
	}
