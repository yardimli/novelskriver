<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Relations\Pivot;

	/**
	 * Represents the pivot model for the chapter_codex_entry table.
	 *
	 * This model defines the relationship between a Chapter and a CodexEntry.
	 * While a dedicated model isn't always necessary for a simple pivot table,
	 * creating one allows for future customization of the pivot data.
	 */
	class ChapterCodexEntry extends Pivot
	{
		/**
		 * The table associated with the model.
		 *
		 * @var string
		 */
		protected $table = 'chapter_codex_entry';

		/**
		 * Indicates if the model should be timestamped.
		 * Pivot tables often do not need timestamps unless you want to track
		 * when a relationship was created or updated.
		 *
		 * @var bool
		 */
		public $timestamps = false;
	}
