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
			Schema::table('novels', function (Blueprint $table) {
				// Add the new JSON column to store the editor's state (window positions, canvas zoom, etc.).
				// It's nullable because existing novels won't have this data initially.
				// The 'after' method is for organizational purposes in the database structure.
				$table->json('editor_state')->nullable()->after('order_in_series');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::table('novels', function (Blueprint $table) {
				// Remove the column if the migration is rolled back.
				$table->dropColumn('editor_state');
			});
		}
	};
