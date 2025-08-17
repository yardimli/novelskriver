<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Novel extends Model
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
		'subtitle',
		'author',
		'genre',
		'logline',
		'synopsis',
		'status',
		'series_id',
		'order_in_series',
	];

	/**
	 * Get the user who owns the novel.
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * Get the series that this novel belongs to.
	 */
	public function series(): BelongsTo
	{
		return $this->belongsTo(Series::class);
	}

	/**
	 * Get the sections for the novel.
	 */
	public function sections(): HasMany
	{
		return $this->hasMany(Section::class)->orderBy('order');
	}

	/**
	 * Get the chapters for the novel.
	 */
	public function chapters(): HasMany
	{
		return $this->hasMany(Chapter::class)->orderBy('order');
	}

	/**
	 * Get the beats (scenes) for the novel.
	 */
	public function beats(): HasMany
	{
		return $this->hasMany(Beat::class)->orderBy('order');
	}

	/**
	 * Get the codex categories for the novel.
	 */
	public function codexCategories(): HasMany
	{
		return $this->hasMany(CodexCategory::class);
	}

	/**
	 * Get all codex entries for the novel.
	 */
	public function codexEntries(): HasMany
	{
		return $this->hasMany(CodexEntry::class);
	}

	/**
	 * Get the outlines for the novel.
	 */
	public function outlines(): HasMany
	{
		return $this->hasMany(Outline::class);
	}

	/**
	 * Get the notes for the novel.
	 */
	public function notes(): HasMany
	{
		return $this->hasMany(Note::class);
	}

	/**
	 * The users that have collaborative access to the novel.
	 */
	public function collaborators(): BelongsToMany
	{
		return $this->belongsToMany(User::class, 'novel_user')
			->withPivot('role')
			->withTimestamps();
	}

	/**
	 * Get the writing stats for the novel.
	 */
	public function writingStats(): HasMany
	{
		return $this->hasMany(WritingStat::class);
	}

	/**
	 * Get the AI logs for the novel.
	 */
	public function aiLogs(): HasMany
	{
		return $this->hasMany(AiLog::class);
	}
}
