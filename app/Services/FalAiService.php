<?php

	namespace App\Services;

	use Illuminate\Support\Facades\Http;
	use Illuminate\Support\Facades\Log;
	use Throwable;

	/**
	 * Handles communication with the Fal.ai image generation API.
	 */
	class FalAiService
	{
		protected ?string $apiKey;
		protected string $apiBaseUrl = 'https://fal.run';

		/**
		 * Set up the service, fetching the API key from config.
		 *
		 * IMPORTANT: Add your Fal.ai API key to your .env file:
		 * FAL_KEY=your_key_here
		 *
		 * And reference it in config/services.php:
		 * 'fal' => [
		 *     'key' => env('FAL_KEY'),
		 * ],
		 */
		public function __construct()
		{
			$this->apiKey = config('services.fal.key');
		}

		/**
		 * Generates an image using the Fal.ai API.
		 *
		 * @param string $prompt The text prompt for the image.
		 * @param array $options Additional options for the API call (e.g., 'image_size').
		 * @return string|null The URL of the generated image, or null on failure.
		 */
		// MODIFIED: Method now accepts an $options array for flexibility.
		public function generateImage(string $prompt, array $options = []): ?string
		{
			if (!$this->apiKey) {
				Log::error('Fal.ai API key is not configured.');
				return null;
			}

			try {
				$payload = [
					'prompt' => $prompt,
					'image_size' => $options['image_size'] ?? 'portrait_16_9', // Default size
				];

				$response = Http::withToken($this->apiKey, 'Key')
					->withHeaders(['Content-Type' => 'application/json'])
					->timeout(180) // 3-minute timeout for image generation
					->post($this->apiBaseUrl . '/fal-ai/qwen-image', $payload);

				Log::info('Fal.ai API request made.', [
					'status' => $response->status(),
					'payload' => $payload,
				]);
				Log::debug('Fal.ai API response body: ' . $response->body());

				if ($response->failed()) {
					Log::error('Fal.ai API request failed.', [
						'status' => $response->status(),
						'body' => $response->body(),
					]);
					$response->throw();
				}

				$data = $response->json();

				if (isset($data['images'][0]['url'])) {
					return $data['images'][0]['url'];
				}

				Log::warning('Fal.ai response did not contain an image URL.', ['response' => $data]);
				return null;

			} catch (Throwable $e) {
				Log::error('Exception when calling Fal.ai API: ' . $e->getMessage());
				return null;
			}
		}
	}
