<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	/**
	 * Represents an image, either uploaded or AI-generated.
	 */
	class Image extends Model
	{
		use HasFactory;

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array<int, string>
		 */
		protected $fillable = [
			'user_id',
			'novel_id',
			'codex_entry_id',
			'image_local_path',
			'thumbnail_local_path',
			'remote_url',
			'prompt',
			'image_type',
		];

		/**
		 * Get the user who owns the image.
		 */
		public function user(): BelongsTo
		{
			return $this->belongsTo(User::class);
		}

		/**
		 * Get the novel this image is associated with.
		 */
		public function novel(): BelongsTo
		{
			return $this->belongsTo(Novel::class);
		}

		/**
		 * Get the codex entry this image is associated with.
		 */
		public function codexEntry(): BelongsTo
		{
			return $this->belongsTo(CodexEntry::class);
		}
	}
