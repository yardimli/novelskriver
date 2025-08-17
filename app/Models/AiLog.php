<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class AiLog extends Model
	{
		use HasFactory;

		protected $table = 'ai_logs';

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array<int, string>
		 */
		protected $fillable = [
			'user_id',
			'novel_id',
			'prompt',
			'response',
			'model_used',
			'token_count',
		];

		/**
		 * Get the user who made the AI request.
		 */
		public function user(): BelongsTo
		{
			return $this->belongsTo(User::class);
		}

		/**
		 * Get the novel associated with the AI request.
		 */
		public function novel(): BelongsTo
		{
			return $this->belongsTo(Novel::class);
		}
	}
