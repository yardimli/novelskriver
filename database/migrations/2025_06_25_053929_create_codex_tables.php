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
			// Categories for codex entries, e.g., "Characters", "Locations", "Lore"
			Schema::create('codex_categories', function (Blueprint $table) {
				$table->id();
				$table->foreignId('novel_id')->constrained()->onDelete('cascade');
				$table->string('name');
				$table->text('description')->nullable();
				$table->timestamps();
			});

			// Individual entries within a codex category
			Schema::create('codex_entries', function (Blueprint $table) {
				$table->id();
				$table->foreignId('novel_id')->constrained()->onDelete('cascade');
				$table->foreignId('codex_category_id')->constrained('codex_categories')->onDelete('cascade');
				$table->string('title');
				$table->text('description')->nullable();
				$table->longText('content')->nullable();
				$table->string('image_path')->nullable()->comment('Path to an associated image, like a map or character portrait.');
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::dropIfExists('codex_entries');
			Schema::dropIfExists('codex_categories');
		}
	};
