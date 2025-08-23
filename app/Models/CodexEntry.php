<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
		 * Get the chapters that this codex entry is linked to.
		 */
		public function chapters(): BelongsToMany
		{
			return $this->belongsToMany(Chapter::class, 'chapter_codex_entry');
		}

		/**
		 * Get the codex entries that this entry links to.
		 * This defines a self-referencing many-to-many relationship.
		 */
		public function linkedEntries(): BelongsToMany
		{
			return $this->belongsToMany(CodexEntry::class, 'codex_entry_links', 'codex_entry_id', 'linked_codex_entry_id');
		}

		/**
		 * Get the codex entries that link to this entry.
		 */
		public function linkedByEntries(): BelongsToMany
		{
			return $this->belongsToMany(CodexEntry::class, 'codex_entry_links', 'linked_codex_entry_id', 'codex_entry_id');
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

			return asset('images/codex-placeholder.png');
		}
	}
