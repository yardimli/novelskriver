<?php

	namespace App\Http\Controllers\Auth;

	use App\Http\Controllers\Controller;
	use App\Models\User;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\Hash;
	use Illuminate\Support\Str;
	use Laravel\Socialite\Facades\Socialite;
	use App\Mail\WelcomeEmail;
	use Illuminate\Support\Facades\Mail;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Http\RedirectResponse;
	use Illuminate\Http\Request; // Not strictly needed for these methods but good practice

// --- ADD THESE ---
	use Illuminate\Support\Facades\Http;
	use Illuminate\Support\Facades\Storage;
// --- END ADD ---

	class SocialLoginController extends Controller
	{
		/**
		 * Redirect the user to the provider authentication page.
		 *
		 * @param string $provider
		 * @return \Illuminate\Http\RedirectResponse
		 */
		public function redirectToProvider(string $provider): RedirectResponse
		{
			if (!in_array($provider, ['google'])) { // Add other providers if needed
				return redirect()->route('login')->with('error', 'Unsupported login provider.');
			}
			return Socialite::driver($provider)->redirect();
		}

		/**
		 * Obtain the user information from the provider.
		 *
		 * @param string $provider
		 * @return \Illuminate\Http\RedirectResponse
		 */
		public function handleProviderCallback(string $provider): RedirectResponse
		{
			if (!in_array($provider, ['google'])) {
				return redirect()->route('login')->with('error', 'Unsupported login provider.');
			}

			try {
				$socialUser = Socialite::driver($provider)->user();
			} catch (\Exception $e) {
				Log::error("Socialite Error ({$provider}): " . $e->getMessage());
				return redirect()->route('login')->with('error', 'Failed to authenticate with ' . ucfirst($provider) . '. Please try again.');
			}

			// --- NEW: Download avatar and prepare user data ---
			$avatarPath = null;
			if ($socialUser->getAvatar()) {
				try {
					$response = Http::get($socialUser->getAvatar());
					if ($response->successful()) {
						$filename = 'avatars/' . Str::random(40) . '.jpg';
						Storage::disk('public')->put($filename, $response->body());
						$avatarPath = $filename;
					}
				} catch (\Exception $e) {
					Log::error('Failed to download avatar for ' . $socialUser->getEmail() . ': ' . $e->getMessage());
				}
			}

			// Split name into first and last for the new database columns
			$nameParts = explode(' ', $socialUser->getName(), 2);
			$firstName = $nameParts[0];
			$lastName = $nameParts[1] ?? null;
			// --- END NEW ---


			// Find user by provider ID
			$user = User::where('provider_name', $provider)
				->where('provider_id', $socialUser->getId())
				->first();

			if ($user) {
				Auth::login($user, true);
				return redirect()->intended(route('dashboard'));
			}

			// If user not found by provider ID, check by email
			$user = User::where('email', $socialUser->getEmail())->first();

			if ($user) {
				// User exists with this email, but not linked to this provider yet.
				$user->provider_name = $provider;
				$user->provider_id = $socialUser->getId();

				// MODIFIED: Update avatar only if it's not already set and we downloaded a new one.
				if (!$user->avatar && $avatarPath) {
					$user->avatar = $avatarPath;
				}

				// If user registered normally and hasn't verified email, mark as verified now.
				if (!$user->hasVerifiedEmail()) {
					$user->email_verified_at = now();
				}
				$user->save();

				Auth::login($user, true);
				return redirect()->intended(route('dashboard'));
			}

			// If no user exists with this email, create a new user
			// MODIFIED: Use the new local avatar path and split names.
			$newUser = User::create([
				'name' => $socialUser->getName(),
				'first_name' => $firstName,
				'last_name' => $lastName,
				'email' => $socialUser->getEmail(),
				'password' => Hash::make(Str::random(24)), // Generate a random password
				'provider_name' => $provider,
				'provider_id' => $socialUser->getId(),
				'avatar' => $avatarPath, // Use the local path here
				'email_verified_at' => now(), // Email is verified by the provider
				'user_type' => User::TYPE_USER,
			]);

			Auth::login($newUser, true);

			// Send welcome email for new social user
			try {
				Mail::to($newUser->email)->send(new WelcomeEmail($newUser));
			} catch (\Exception $e) {
				Log::error("Failed to send welcome email to new social user {$newUser->email}: " . $e->getMessage());
			}

			return redirect()->intended(route('dashboard'));
		}
	}
