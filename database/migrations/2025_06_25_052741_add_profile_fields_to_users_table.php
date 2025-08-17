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
			Schema::table('users', function (Blueprint $table) {
				// Splitting 'name' into first and last for better data structure
				$table->string('first_name')->nullable()->after('name');
				$table->string('last_name')->nullable()->after('first_name');

				// Additional profile information
				$table->string('phone')->nullable()->after('email');
				$table->date('birthday')->nullable()->after('phone');
				$table->string('address')->nullable()->after('birthday');
				$table->string('country')->nullable()->after('address');
				$table->string('state')->nullable()->after('country'); // For state, region, or county
				$table->string('city')->nullable()->after('state');
				$table->string('zip')->nullable()->after('city'); // For zip or postal code
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::table('users', function (Blueprint $table) {
				$table->dropColumn([
					'first_name',
					'last_name',
					'phone',
					'birthday',
					'address',
					'country',
					'state',
					'city',
					'zip',
				]);
			});
		}
	};
