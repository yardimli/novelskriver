<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\HasMany;

	class Series extends Model
	{
		use HasFactory;

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array<int, string>
		 */
		protected $fillable = [
			'user_id',
			'title',
			'description',
		];

		/**
		 * Get the user who owns the series.
		 */
		public function user(): BelongsTo
		{
			return $this->belongsTo(User::class);
		}

		/**
		 * Get the novels that belong to this series, ordered by their position in the series.
		 */
		public function novels(): HasMany
		{
			return $this->hasMany(Novel::class)->orderBy('order_in_series');
		}
	}
