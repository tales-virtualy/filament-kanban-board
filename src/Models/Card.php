<?php

namespace FilamentKanban\Models;

use FilamentKanban\Services\CardActivityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Card extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'list_id',
        'title',
        'description',
        'assigned_to',
        'created_by',
        'due_date',
        'order',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'archived_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function getTable()
    {
        return config('kanban.tables.cards', parent::getTable());
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(BoardList::class, 'list_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(config('kanban.user_model'), 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('kanban.user_model'), 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(config('kanban.user_model'), config('kanban.tables.card_has_users'))->withTimestamps();
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(
            CustomTag::class,
            'taggable',
            config('kanban.tables.taggables'),
            'taggable_id',
            'tag_id',
        );
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CardComment::class)->orderBy('created_at', 'desc');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(CardChecklist::class)->orderBy('position');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CardAttachment::class)->orderBy('created_at', 'desc');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CardActivityLog::class)->orderBy('created_at', 'desc');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function isArchived(): bool
    {
        return !is_null($this->archived_at);
    }

    public function archive(): void
    {
        if ($this->isArchived() || $this->list->isArchived()) {
            return;
        }

        $this->update(['archived_at' => now()]);
    }

    public function unarchive(): void
    {
        $this->update(['archived_at' => null]);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }

    public function isDueSoon(): bool
    {
        return $this->due_date && $this->due_date->isFuture() && $this->due_date->diffInDays() <= 7;
    }

    public function getDueDateStatusAttribute(): string
    {
        if (!$this->due_date) {
            return 'text-gray-500';
        }

        if ($this->isOverdue()) {
            return 'text-red-600 bg-red-50 dark:bg-red-950';
        }

        if ($this->isDueSoon()) {
            return 'text-orange-600 bg-orange-50 dark:bg-orange-950';
        }

        return 'text-green-600 bg-green-50 dark:bg-green-950';
    }

    public function getChecklistProgressAttribute(): array
    {
        $totalItems = 0;
        $completedItems = 0;

        foreach ($this->checklists as $checklist) {
            $progress = $checklist->progress;
            $totalItems += $progress['total'];
            $completedItems += $progress['completed'];
        }

        return [
            'total' => $totalItems,
            'completed' => $completedItems,
            'percentage' => $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0,
        ];
    }

    public function addMember(int $userId): void
    {
        if (!$this->members()->where('user_id', $userId)->exists()) {
            $this->members()->attach($userId);

            $userModel = config('kanban.user_model');
            CardActivityService::memberAdded($this, $userModel::find($userId)?->name);
        }
    }

    public function removeMember(int $userId): void
    {
        $userModel = config('kanban.user_model');
        $user = $userModel::find($userId);
        $this->members()->detach($userId);

        CardActivityService::memberRemoved($this, $user?->name);
    }

    public function addTag(int $tagId): void
    {
        if (!$this->tags()->where('tag_id', $tagId)->exists()) {
            $this->tags()->attach($tagId);

            CardActivityService::tagAdded($this, CustomTag::find($tagId)?->name);
        }
    }

    public function removeTag(int $tagId): void
    {
        $tag = CustomTag::find($tagId);
        $this->tags()->detach($tagId);

        CardActivityService::tagRemoved($this, $tag?->name);
    }

    public function syncTagsWithLog(array $tagIds): void
    {
        $before = $this->tags()->pluck(config('kanban.tables.tags', 'tags') . '.id')->toArray();
        $this->tags()->sync($tagIds);
        $after = $this->tags()->pluck(config('kanban.tables.tags', 'tags') . '.id')->toArray();

        $added = array_diff($after, $before);
        foreach ($added as $tagId) {
            CardActivityService::tagAdded($this, CustomTag::find($tagId)?->name);
        }

        $removed = array_diff($before, $after);
        foreach ($removed as $tagId) {
            CardActivityService::tagRemoved($this, CustomTag::find($tagId)?->name);
        }
    }

    protected static function booted(): void
    {
        static::created(function ($card) {
            CardActivityService::created($card);
        });

        static::updated(function ($card) {
            if ($card->wasChanged('list_id')) {
                CardActivityService::moved($card, $card->list->name);
            }

            if ($card->wasChanged('due_date')) {
                CardActivityService::dueDateChanged($card, $card->due_date?->format('d/m/Y'));
            }

            if ($card->wasChanged('assigned_to')) {
                if ($card->assigned_to) {
                    CardActivityService::assigned($card, $card->assignee?->name);
                } else {
                    CardActivityService::unassigned($card);
                }
            }

            if ($card->wasChanged('description')) {
                CardActivityService::descriptionUpdated($card);
            }
        });
    }
}
