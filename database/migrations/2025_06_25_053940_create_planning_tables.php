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
			// For structured outlines like Three-Act Structure, etc.
			Schema::create('outlines', function (Blueprint $table) {
				$table->id();
				$table->foreignId('novel_id')->constrained()->onDelete('cascade');
				$table->string('title');
				$table->longText('content')->comment('Can store Markdown or JSON for structured outlines.');
				$table->string('type')->default('default');
				$table->timestamps();
			});

			// For general, unstructured notes related to the novel
			Schema::create('notes', function (Blueprint $table) {
				$table->id();
				$table->foreignId('novel_id')->constrained()->onDelete('cascade');
				$table->string('title')->nullable();
				$table->longText('content');
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::dropIfExists('notes');
			Schema::dropIfExists('outlines');
		}
	};
