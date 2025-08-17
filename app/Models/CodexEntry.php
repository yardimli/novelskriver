<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
	}
