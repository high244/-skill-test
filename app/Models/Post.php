<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'is_draft',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_draft' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isDraft(): bool
    {
        return (bool) $this->is_draft;
    }

    public function isScheduled(): bool
    {
        if ($this->isDraft() || $this->published_at === null) {
            return false;
        }

        return $this->published_at->isFuture();
    }

    public function isPublished(): bool
    {
        if ($this->isDraft() || $this->published_at === null) {
            return false;
        }

        return $this->published_at->isPast() || $this->published_at->isToday();
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('is_draft', true);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query
            ->where('is_draft', false)
            ->whereNotNull('published_at')
            ->where('published_at', '>', now());
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_draft', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
