<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\HasMany;

	class CodexCategory extends Model
	{
		use HasFactory;

		protected $table = 'codex_categories';

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array<int, string>
		 */
		protected $fillable = [
			'novel_id',
			'name',
			'description',
		];

		/**
		 * Get the novel that this codex category belongs to.
		 */
		public function novel(): BelongsTo
		{
			return $this->belongsTo(Novel::class);
		}

		/**
		 * Get the entries for the codex category.
		 */
		public function entries(): HasMany
		{
			return $this->hasMany(CodexEntry::class);
		}
	}
