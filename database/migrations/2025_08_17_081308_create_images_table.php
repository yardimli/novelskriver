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
			Schema::create('images', function (Blueprint $table) {
				$table->id();
				$table->foreignId('user_id')->constrained()->onDelete('cascade');
				$table->foreignId('novel_id')->nullable()->constrained()->onDelete('set null');
				$table->string('image_local_path')->nullable();
				$table->string('thumbnail_local_path')->nullable();
				$table->string('remote_url', 1024)->nullable();
				$table->text('prompt')->nullable();
				$table->enum('image_type', ['upload', 'generated']);
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::dropIfExists('images');
		}
	};
