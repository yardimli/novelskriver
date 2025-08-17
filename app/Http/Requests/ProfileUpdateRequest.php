<?php

	namespace App\Http\Requests;

	use App\Models\User;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Validation\Rule;

	class ProfileUpdateRequest extends FormRequest
	{
		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
		 */
		public function rules(): array
		{
			return [
				'first_name' => ['required', 'string', 'max:255'],
				'last_name' => ['required', 'string', 'max:255'],
				'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
				'phone' => ['nullable', 'string', 'max:25'],
				'birthday' => ['nullable', 'date'],
				'address' => ['nullable', 'string', 'max:255'],
				'country' => ['nullable', 'string', 'max:255'],
				'state' => ['nullable', 'string', 'max:255'],
				'city' => ['nullable', 'string', 'max:255'],
				'zip' => ['nullable', 'string', 'max:20'],
			];
		}
	}
