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
			Schema::create('chapters', function (Blueprint $table) {
				$table->id();
				$table->foreignId('novel_id')->constrained()->onDelete('cascade');
				$table->foreignId('section_id')->nullable()->constrained()->onDelete('cascade');
				$table->string('title');
				$table->text('summary')->nullable();
				$table->enum('status', ['todo', 'in_progress', 'completed'])->default('todo');
				$table->unsignedInteger('order')->default(0);
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::dropIfExists('chapters');
		}
	};
