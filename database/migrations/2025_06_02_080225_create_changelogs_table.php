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
			Schema::create('changelogs', function (Blueprint $table) {
				$table->id();
				$table->string('commit_hash')->unique();
				$table->timestamp('commit_date');
				$table->string('author_name');
				$table->string('author_email');
				$table->text('message');
				$table->boolean('hide')->default(false);
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::dropIfExists('changelogs');
		}
	};
