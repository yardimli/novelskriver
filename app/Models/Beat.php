<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class Beat extends Model
	{
		use HasFactory;

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array<int, string>
		 */
		protected $fillable = [
			'novel_id',
			'chapter_id',
			'title',
			'summary',
			'content',
			'order',
		];

		/**
		 * Get the novel that this beat belongs to.
		 */
		public function novel(): BelongsTo
		{
			return $this->belongsTo(Novel::class);
		}

		/**
		 * Get the chapter that this beat belongs to.
		 */
		public function chapter(): BelongsTo
		{
			return $this->belongsTo(Chapter::class);
		}
	}
