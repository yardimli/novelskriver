<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration
	{
		/**
		 * Run the migrations.
		 */
		public function up(): void
		{
			// This pivot table establishes a many-to-many relationship between
			// the 'chapters' and 'codex_entries' tables.
			Schema::create('chapter_codex_entry', function (Blueprint $table) {
				// Foreign key for the Chapter model.
				// onDelete('cascade') ensures that if a chapter is deleted,
				// all its links to codex entries are also removed.
				$table->foreignId('chapter_id')->constrained()->onDelete('cascade');

				// Foreign key for the CodexEntry model.
				// The table name 'codex_entries' is specified explicitly.
				// onDelete('cascade') ensures that if a codex entry is deleted,
				// all its links to chapters are also removed.
				$table->foreignId('codex_entry_id')->constrained('codex_entries')->onDelete('cascade');

				// A composite primary key prevents duplicate entries.
				// A codex entry can only be linked to a specific chapter once.
				$table->primary(['chapter_id', 'codex_entry_id']);
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::dropIfExists('chapter_codex_entry');
		}
	};
