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
			Schema::create('beats', function (Blueprint $table) {
				$table->id();
				$table->foreignId('novel_id')->constrained()->onDelete('cascade');
				$table->foreignId('chapter_id')->constrained()->onDelete('cascade');
				$table->string('title')->nullable();
				$table->text('summary')->nullable()->comment('A brief summary or goal for the beat/scene.');
				$table->longText('content')->nullable()->comment('The actual manuscript text for this beat.');
				$table->unsignedInteger('order')->default(0);
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::dropIfExists('beats');
		}
	};
