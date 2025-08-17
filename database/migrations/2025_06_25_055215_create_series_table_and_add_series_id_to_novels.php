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
			// 1. Create the new 'series' table
			Schema::create('series', function (Blueprint $table) {
				$table->id();
				$table->foreignId('user_id')->constrained('users')->onDelete('cascade');
				$table->string('title');
				$table->text('description')->nullable();
				$table->timestamps();
			});

			// 2. Update the existing 'novels' table to link to a series
			Schema::table('novels', function (Blueprint $table) {
				// The foreign key to the series table.
				// It's nullable because a novel can be a standalone work.
				// onDelete('set null') means if a series is deleted, the novels within it
				// become standalone rather than being deleted themselves.
				$table->foreignId('series_id')->nullable()->after('id')->constrained('series')->onDelete('set null');

				// The reading order of the novel within the series (e.g., Book 1, Book 2).
				// It's nullable as it only applies if the novel is in a series.
				$table->unsignedInteger('order_in_series')->nullable()->after('series_id');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			// Reverse the operations in the opposite order
			Schema::table('novels', function (Blueprint $table) {
				$table->dropForeign(['series_id']);
				$table->dropColumn('series_id');
				$table->dropColumn('order_in_series');
			});

			Schema::dropIfExists('series');
		}
	};
