<?php

	namespace App\Http\Controllers;

	use App\Http\Controllers\LlmController; // MODIFIED: Added LlmController import.
	use App\Models\Series;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\File; // MODIFIED: Added File facade import.
	use Illuminate\Support\Facades\Log; // MODIFIED: Added Log facade import.
	use Illuminate\View\View;
	// No need to import Collection as we are using the collect() helper or existing collections.

	class DashboardController extends Controller
	{
		/**
		 * Display the user's dashboard.
		 *
		 * @param Request $request
		 * @param LlmController $llm
		 * @return View
		 */
		// MODIFIED: Injected LlmController to fetch available models.
		public function index(Request $request, LlmController $llm): View
		{
			$user = Auth::user();

			$novels = $user->novels()
				->with([
					'series',
					'images'
				])
				->withCount('chapters') // NEW: Get the count of chapters for each novel.
				->latest('created_at')
				->get();

			$groupedNovels = $novels->groupBy('series_id');

			// MODIFIED: Corrected the key for pulling novels without a series.
			// Laravel's groupBy uses an empty string ('') as the key for null values.
			$novelsWithoutSeries = $groupedNovels->pull('') ?? collect();

			// The rest are novels with series. We need to get the Series models for the titles.
			$seriesWithNovels = collect();
			if ($groupedNovels->isNotEmpty()) {
				// The keys will be actual series IDs now, as the '' group has been removed.
				$seriesIds = $groupedNovels->keys();

				if ($seriesIds->isNotEmpty()) {
					$seriesModels = Series::findMany($seriesIds)->keyBy('id');

					$seriesWithNovels = $groupedNovels->map(function ($novelsInSeries, $seriesId) use ($seriesModels) {
						if ($seriesModel = $seriesModels->get($seriesId)) {
							return [
								'series' => $seriesModel,
								'novels' => $novelsInSeries
							];
						}
						return null;
					})->filter()->values(); // Use values() to reset keys for consistent array-like behavior in the view.
				}
			}

			// NEW: Get structure files from resources/structures directory.
			$structures = [];
			try {
				$structureFiles = File::files(resource_path('structures'));
				foreach ($structureFiles as $file) {
					if ($file->getExtension() === 'txt') {
						$structures[basename($file)] = pathinfo($file->getFilename(), PATHINFO_FILENAME);
					}
				}
			} catch (\Exception $e) {
				Log::error('Could not read structure files: ' . $e->getMessage());
			}

			// NEW: Get LLM models for the dropdown.
			$llmModels = [];
			try {
				$apiModels = $llm->getModels();
				if (isset($apiModels['data'])) {
					$llmModels = collect($apiModels['data'])->pluck('id')->sort()->values()->all();
				}
			} catch (\Exception $e) {
				Log::error('Could not fetch LLM models for dashboard: ' . $e->getMessage());
				// Fallback to the default model if the API call fails.
				$llmModels = [env('OPEN_ROUTER_MODEL', 'openai/gpt-4o-mini')];
			}

			// NEW: A list of top languages for the dropdown.
			$languages = [
				'English', 'Mandarin Chinese', 'Hindi', 'Spanish', 'French',
				'Standard Arabic', 'Bengali', 'Russian', 'Portuguese', 'Urdu',
				'Indonesian', 'German', 'Japanese', 'Nigerian Pidgin', 'Marathi',
				'Telugu', 'Turkish', 'Tamil', 'Yue Chinese', 'Vietnamese'
			];

			return view('dashboard.index', [
				'user' => $user,
				'novelsWithoutSeries' => $novelsWithoutSeries,
				'seriesWithNovels' => $seriesWithNovels,
				'structures' => $structures,
				'llmModels' => $llmModels,
				'languages' => $languages,
			]);
		}
	}
