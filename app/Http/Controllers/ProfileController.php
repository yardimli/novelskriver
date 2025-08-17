<?php

	namespace App\Http\Controllers;

	use App\Http\Requests\ProfileUpdateRequest;
	use Illuminate\Http\RedirectResponse;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\Redirect;
	use Illuminate\View\View;

	class ProfileController extends Controller
	{
		/**
		 * Display the user's profile form.
		 */
		public function edit(Request $request): View
		{
			return view('profile.edit', [
				'user' => $request->user(),
			]);
		}

		/**
		 * Update the user's profile information.
		 *
		 * Note: This assumes you have created a migration to add the new columns
		 * (first_name, last_name, phone, birthday, address, country, state, city, zip)
		 * to your 'users' table and added them to the $fillable array in the User model.
		 */
		public function update(ProfileUpdateRequest $request): RedirectResponse
		{
			// The request is already validated by ProfileUpdateRequest
			$user = $request->user();

			// The 'name' field is a combination of first and last name
			$user->name = $request->input('first_name') . ' ' . $request->input('last_name');

			// Fill other model attributes from the request
			$user->fill($request->except(['name', 'first_name', 'last_name']));

			// If the user changes their email, we must reset the verification status.
			if ($user->isDirty('email')) {
				$user->email_verified_at = null;
			}

			$user->save();

			return Redirect::route('profile.edit')->with('status', 'profile-updated');
		}

		/**
		 * Delete the user's account.
		 */
		public function destroy(Request $request): RedirectResponse
		{
			$request->validateWithBag('userDeletion', [
				'password' => ['required', 'current_password'],
			]);

			$user = $request->user();

			Auth::logout();

			$user->delete();

			$request->session()->invalidate();
			$request->session()->regenerateToken();

			return Redirect::to('/');
		}
	}
