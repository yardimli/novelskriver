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
			// To track daily writing progress per user and novel
			Schema::create('writing_stats', function (Blueprint $table) {
				$table->id();
				$table->foreignId('user_id')->constrained()->onDelete('cascade');
				$table->foreignId('novel_id')->constrained()->onDelete('cascade');
				$table->date('date');
				$table->integer('words_written')->default(0);
				$table->integer('time_spent_seconds')->default(0)->comment('Time spent writing in seconds');
				$table->timestamps();

				// Ensure one stat entry per day per user per novel
				$table->unique(['user_id', 'novel_id', 'date']);
			});

			// To log AI interactions for usage tracking and history
			Schema::create('ai_logs', function (Blueprint $table) {
				$table->id();
				$table->foreignId('user_id')->constrained()->onDelete('cascade');
				$table->foreignId('novel_id')->nullable()->constrained()->onDelete('set null');
				$table->text('prompt');
				$table->longText('response');
				$table->string('model_used')->nullable();
				$table->unsignedInteger('token_count')->nullable();
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::dropIfExists('ai_logs');
			Schema::dropIfExists('writing_stats');
		}
	};
