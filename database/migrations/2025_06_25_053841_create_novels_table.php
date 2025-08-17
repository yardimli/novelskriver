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
			Schema::create('novels', function (Blueprint $table) {
				$table->id();
				$table->foreignId('user_id')->comment('The original author/owner of the novel')->constrained('users')->onDelete('cascade');
				$table->string('title');
				$table->string('subtitle')->nullable();
				$table->string('author')->nullable()->comment('Author name for the book, defaults to user name');
				$table->string('genre')->nullable();
				$table->text('logline')->nullable()->comment('A one-sentence summary of the story.');
				$table->longText('synopsis')->nullable();
				$table->enum('status', ['planning', 'draft', 'editing', 'completed'])->default('planning');
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::dropIfExists('novels');
		}
	};
