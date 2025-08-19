<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void
		{
			Schema::create('codex_entry_links', function (Blueprint $table) {
				// This column stores the ID of the entry that INITIATES the link.
				$table->unsignedBigInteger('codex_entry_id');
				// This column stores the ID of the entry that IS BEING LINKED TO.
				$table->unsignedBigInteger('linked_codex_entry_id');

				// Foreign key constraints to ensure data integrity.
				// If a codex entry is deleted, any links to/from it are also deleted.
				$table->foreign('codex_entry_id')->references('id')->on('codex_entries')->onDelete('cascade');
				$table->foreign('linked_codex_entry_id')->references('id')->on('codex_entries')->onDelete('cascade');

				// A composite primary key prevents the same link from being created more than once.
				// For example, you can't link Character A to Location B twice.
				$table->primary(['codex_entry_id', 'linked_codex_entry_id']);
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::dropIfExists('codex_entry_links');
		}
	};
