<?php

	namespace App\Http\Controllers;

	use App\Models\Novel;
	use App\Models\Series;
	use App\Services\OpenAiService;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\Validator;
	use Illuminate\View\View;

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
		 * @return \Illuminate\Http\RedirectResponse
		 */
		public function store(Request $request)
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

			// Redirect to the novel's dashboard or editor page
			// For now, redirecting to the main dashboard with a success message.
			return redirect()->route('dashboard')->with('success', 'Novel created successfully!');
		}

		/**
		 * Generate a novel title using AI.
		 *
		 * @param  OpenAiService $openAiService
		 * @return \Illuminate\Http\JsonResponse
		 */
		public function generateTitle(OpenAiService $openAiService)
		{
			$prompt = "Generate a single, funny, compelling, and unique novel title of 3 words. Do not provide any explanation or surrounding text, just the title itself. Think about the random number ".rand(1, 1000) . " for inspiration but dont mention the number in the title.";
			$messages = [
				['role' => 'system', 'content' => 'You are a creative assistant that generates book titles.'],
				['role' => 'user', 'content' => $prompt]
			];

			$response = $openAiService->generateText($messages, 0.8, 50);

			if (isset($response['content'])) {
				$title = trim($response['content'], " \n\r\t\v\0.\"");
				return response()->json(['title' => $title]);
			}

			return response()->json(['error' => 'Failed to generate title.'], 500);
		}
	}
