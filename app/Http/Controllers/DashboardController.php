<?php

	namespace App\Http\Controllers;

	use App\Http\Controllers\LlmController;
	use App\Models\Series;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\File;
	use Illuminate\Support\Facades\Log;
	use Illuminate\View\View;

	class DashboardController extends Controller
	{
		/**
		 * Display the user's dashboard.
		 *
		 * @param Request $request
		 * @param LlmController $llm
		 * @return View
		 */
		public function index(Request $request, LlmController $llm): View
		{
			$user = Auth::user();

			$novels = $user->novels()
				->with([
					'series',
					'images'
				])
				->withCount('chapters')
				->latest('created_at')
				->get();

			$groupedNovels = $novels->groupBy('series_id');
			$novelsWithoutSeries = $groupedNovels->pull('') ?? collect();

			$seriesWithNovels = collect();
			if ($groupedNovels->isNotEmpty()) {
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
					})->filter()->values();
				}
			}

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

			$llmModels = [];
			try {
				$apiModels = $llm->getModels();
				if (isset($apiModels['data'])) {
					$llmModels = collect($apiModels['data'])->pluck('id')->sort()->values()->all();
				}
			} catch (\Exception $e) {
				Log::error('Could not fetch LLM models for dashboard: ' . $e->getMessage());
				$llmModels = [env('OPEN_ROUTER_MODEL', 'openai/gpt-4o-mini')];
			}

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
