<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\BelongsToMany; // NEW: Import BelongsToMany.
	use Illuminate\Database\Eloquent\Relations\HasOne;
	use Illuminate\Support\Facades\Storage;

	class CodexEntry extends Model
	{
		use HasFactory;

		protected $table = 'codex_entries';

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array<int, string>
		 */
		protected $fillable = [
			'novel_id',
			'codex_category_id',
			'title',
			'description',
			'content',
			'image_path',
		];

		/**
		 * Get the novel that this codex entry belongs to.
		 */
		public function novel(): BelongsTo
		{
			return $this->belongsTo(Novel::class);
		}

		/**
		 * Get the category that this codex entry belongs to.
		 */
		public function category(): BelongsTo
		{
			return $this->belongsTo(CodexCategory::class, 'codex_category_id');
		}

		/**
		 * Get the image associated with the codex entry.
		 */
		public function image(): HasOne
		{
			return $this->hasOne(Image::class);
		}

		/**
		 * NEW: Get the chapters that this codex entry is linked to.
		 */
		public function chapters(): BelongsToMany
		{
			return $this->belongsToMany(Chapter::class, 'chapter_codex_entry');
		}

		/**
		 * Accessor to get the public URL for the entry's image or a placeholder.
		 *
		 * @return string
		 */
		public function getImageUrlAttribute(): string
		{
			if ($this->image && $this->image->image_local_path && Storage::disk('public')->exists($this->image->image_local_path)) {
				return Storage::disk('public')->url($this->image->image_local_path);
			}

			return asset('images/codex-placeholder.png');
		}

		/**
		 * Accessor to get the public URL for the entry's thumbnail or a placeholder.
		 *
		 * @return string
		 */
		public function getThumbnailUrlAttribute(): string
		{
			if ($this->image && $this->image->thumbnail_local_path && Storage::disk('public')->exists($this->image->thumbnail_local_path)) {
				return Storage::disk('public')->url($this->image->thumbnail_local_path);
			}

			// You can use a different placeholder for thumbnails if you wish
			return asset('images/codex-placeholder.png');
		}
	}
