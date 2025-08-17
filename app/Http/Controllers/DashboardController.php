<?php

	namespace App\Http\Controllers;

	use App\Models\Series;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\View\View;
	// No need to import Collection as we are using the collect() helper or existing collections.

	class DashboardController extends Controller
	{
		/**
		 * Display the user's dashboard.
		 *
		 * @param Request $request
		 * @return View
		 */
		public function index(Request $request): View
		{
			$user = Auth::user();

			$novels = $user->novels()
				->with([
					'series',
					'images'
				])
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

			return view('dashboard.index', [
				'user' => $user,
				'novelsWithoutSeries' => $novelsWithoutSeries,
				'seriesWithNovels' => $seriesWithNovels,
			]);
		}
	}
