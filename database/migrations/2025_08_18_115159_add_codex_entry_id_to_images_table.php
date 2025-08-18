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
			Schema::table('images', function (Blueprint $table) {
				// NEW: Add the foreign key for the codex entry relationship.
				// It's nullable because an image might belong to a novel cover instead of a codex entry.
				// We place it after novel_id for logical grouping.
				$table->foreignId('codex_entry_id')->nullable()->after('novel_id')->constrained('codex_entries')->onDelete('set null');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::table('images', function (Blueprint $table) {
				// NEW: Define the reverse operation to drop the column and its foreign key.
				// The foreign key constraint must be dropped before the column.
				$table->dropForeign(['codex_entry_id']);
				$table->dropColumn('codex_entry_id');
			});
		}
	};
