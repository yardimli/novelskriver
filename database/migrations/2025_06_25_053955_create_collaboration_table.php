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
			Schema::create('novel_user', function (Blueprint $table) {
				$table->id();
				$table->foreignId('novel_id')->constrained()->onDelete('cascade');
				$table->foreignId('user_id')->constrained()->onDelete('cascade');
				$table->string('role')->default('viewer')->comment('e.g., owner, editor, viewer');
				$table->timestamps();

				// Each user can only have one role per novel
				$table->unique(['novel_id', 'user_id']);
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::dropIfExists('novel_user');
		}
	};
