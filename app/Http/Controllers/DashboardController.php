<?php

	namespace App\Http\Controllers;

	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\View\View;

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

			// In a real application, you would fetch the user's projects, stats, etc.
			// For example:
			// $bookProjects = $user->projects()->latest()->take(5)->get();
			// $wordCountToday = $user->stats()->today()->sum('words');

			return view('dashboard.index', [
				'user' => $user,
			]);
		}
	}
