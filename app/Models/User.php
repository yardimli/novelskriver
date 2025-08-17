<?php

	namespace App\Models;

	use Illuminate\Contracts\Auth\MustVerifyEmail;
	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Relations\BelongsToMany;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Foundation\Auth\User as Authenticatable;
	use Illuminate\Notifications\Notifiable;
	use Laravel\Sanctum\HasApiTokens;
	use App\Mail\PasswordResetEmail;
	use Illuminate\Support\Facades\Mail;

	class User extends Authenticatable implements MustVerifyEmail
	{
		use HasApiTokens, HasFactory, Notifiable;

		public const TYPE_USER = 1;
		public const TYPE_ADMIN = 2;

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array<int, string>
		 */
		protected $fillable = [
			'name',
			'first_name',
			'last_name',
			'email',
			'password',
			'user_type',
			'provider_name',
			'provider_id',
			'avatar',
			'email_verified_at',
			'phone',
			'birthday',
			'address',
			'country',
			'state',
			'city',
			'zip',
		];

		/**
		 * The attributes that should be hidden for serialization.
		 *
		 * @var array<int, string>
		 */
		protected $hidden = [
			'password',
			'remember_token',
		];

		/**
		 * The attributes that should be cast.
		 *
		 * @var array<string, string>
		 */
		protected $casts = [
			'email_verified_at' => 'datetime',
			'password' => 'hashed',
			'user_type' => 'integer',
			'birthday' => 'date',
		];

		/**
		 * Check if the user is an admin.
		 *
		 * @return bool
		 */
		public function isAdmin(): bool
		{
			return $this->user_type === self::TYPE_ADMIN;
		}

		/**
		 * Send the password reset notification.
		 *
		 * @param string $token
		 * @return void
		 */
		public function sendPasswordResetNotification($token)
		{
			Mail::to($this->getEmailForPasswordReset())->send(new PasswordResetEmail($this, $token));
		}

		// START: ADDED RELATIONSHIPS

		/**
		 * Get the novels that the user owns.
		 */
		public function novels(): HasMany
		{
			return $this->hasMany(Novel::class);
		}

		/**
		 * Get the series that the user owns.
		 */
		public function series(): HasMany
		{
			return $this->hasMany(Series::class);
		}

		/**
		 * Get the novels that the user is a collaborator on.
		 */
		public function collaborations(): BelongsToMany
		{
			return $this->belongsToMany(Novel::class, 'novel_user')
				->withPivot('role')
				->withTimestamps();
		}

		/**
		 * Get the writing stats for the user.
		 */
		public function writingStats(): HasMany
		{
			return $this->hasMany(WritingStat::class);
		}

		/**
		 * Get the AI logs for the user.
		 */
		public function aiLogs(): HasMany
		{
			return $this->hasMany(AiLog::class);
		}

		/**
		 * Get the images for the user.
		 * @return HasMany
		 */
		// NEW: Added relationship to images.
		public function images(): HasMany
		{
			return $this->hasMany(Image::class);
		}
		// END: ADDED RELATIONSHIPS
	}
