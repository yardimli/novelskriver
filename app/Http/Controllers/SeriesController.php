<?php

	namespace App\Http\Controllers;

	use App\Models\Series;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\Validator;

	class SeriesController extends Controller
	{
		/**
		 * Store a newly created series in storage via AJAX.
		 *
		 * @param  \Illuminate\Http\Request  $request
		 * @return \Illuminate\Http\JsonResponse
		 */
		public function store(Request $request)
		{
			$user = Auth::user();

			$validator = Validator::make($request->all(), [
				'title' => 'required|string|max:255|unique:series,title,NULL,id,user_id,' . $user->id,
			]);

			if ($validator->fails()) {
				return response()->json(['errors' => $validator->errors()], 422);
			}

			$series = Series::create([
				'user_id' => $user->id,
				'title' => $request->input('title'),
			]);

			return response()->json($series, 201);
		}
	}
