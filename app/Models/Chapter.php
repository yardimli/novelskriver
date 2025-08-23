<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\BelongsToMany;
	use Illuminate\Database\Eloquent\Relations\HasMany;

	class Chapter extends Model
	{
		use HasFactory;

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array<int, string>
		 */
		protected $fillable = [
			'novel_id',
			'section_id',
			'title',
			'summary',
			'status',
			'order',
		];

		/**
		 * Get the novel that this chapter belongs to.
		 */
		public function novel(): BelongsTo
		{
			return $this->belongsTo(Novel::class);
		}

		/**
		 * Get the section that this chapter belongs to.
		 */
		public function section(): BelongsTo
		{
			return $this->belongsTo(Section::class);
		}

		 public function beats(): HasMany
		 {
		 	return $this->hasMany(Beat::class)->orderBy('order');
		 }

		/**
		 * Get the codex entries linked to this chapter.
		 */
		public function codexEntries(): BelongsToMany
		{
			return $this->belongsToMany(CodexEntry::class, 'chapter_codex_entry');
		}
	}
