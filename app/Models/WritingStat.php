<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class WritingStat extends Model
	{
		use HasFactory;

		protected $table = 'writing_stats';

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array<int, string>
		 */
		protected $fillable = [
			'user_id',
			'novel_id',
			'date',
			'words_written',
			'time_spent_seconds',
		];

		/**
		 * The attributes that should be cast.
		 *
		 * @var array<string, string>
		 */
		protected $casts = [
			'date' => 'date',
		];

		/**
		 * Get the user associated with the writing stat.
		 */
		public function user(): BelongsTo
		{
			return $this->belongsTo(User::class);
		}

		/**
		 * Get the novel associated with the writing stat.
		 */
		public function novel(): BelongsTo
		{
			return $this->belongsTo(Novel::class);
		}
	}
