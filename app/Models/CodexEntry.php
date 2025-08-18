<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\HasOne; // NEW: Import HasOne relationship.
	use Illuminate\Support\Facades\Storage; // NEW: Import Storage facade.

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
		 * NEW: Get the image associated with the codex entry.
		 */
		public function image(): HasOne
		{
			return $this->hasOne(Image::class);
		}

		/**
		 * NEW: Accessor to get the public URL for the entry's image or a placeholder.
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
	}
