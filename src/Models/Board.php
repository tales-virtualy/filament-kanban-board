<?php

namespace FilamentKanban\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Board extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_private',
        'owner_id',
        'archived_at',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'archived_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getTable()
    {
        return config('kanban.tables.boards', parent::getTable());
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(config('kanban.user_model'), 'owner_id');
    }

    public function lists(): HasMany
    {
        return $this->hasMany(BoardList::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(config('kanban.user_model'), config('kanban.tables.board_has_users'))
            ->withPivot('role')
            ->withTimestamps();
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeWithKanban($query)
    {
        return $query->with([
            'lists' => function ($q) {
                $q->whereNull('archived_at')
                    ->orderBy('order')
                    ->with([
                        'cards' => function ($q) {
                            $q->whereNull('archived_at')
                                ->orderBy('order')
                                ->with([
                                    'members',
                                    'tags',
                                    'checklists.items',
                                    'attachments',
                                ]);
                        },
                    ]);
            },
        ]);
    }

    public function isOwner($user): bool
    {
        if (!$user) {
            return false;
        }

        return (string) $this->owner_id === (string) $user->getKey();
    }

    public function isAdmin($user): bool
    {
        if (!$user) {
            return false;
        }

        if ($this->isOwner($user)) {
            return true;
        }

        return $this->members()
            ->whereKey($user->getKey())
            ->wherePivotIn('role', ['admin'])
            ->exists();
    }

    public function isMember($user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->isOwner($user) || $this->members()->whereKey($user->getKey())->exists();
    }

    /**
     * Owner plus board members, deduplicated for display.
     */
    public function participants(): Collection
    {
        $this->loadMissing(['owner', 'members']);

        return collect([$this->owner])
            ->merge($this->members)
            ->filter()
            ->unique(fn ($user) => $user->getKey())
            ->values();
    }

    public function isArchived(): bool
    {
        return !is_null($this->archived_at);
    }

    public function archive(): void
    {
        if ($this->isArchived()) {
            return;
        }

        $this->update(['archived_at' => now()]);
    }

    public function unarchive(): void
    {
        $this->update(['archived_at' => null]);
    }

    public function archiveAllListsAndCards(): void
    {
        $listIds = $this->lists()->pluck('id');
        $this->lists()->whereNull('archived_at')->update(['archived_at' => now()]);
        Card::whereIn('list_id', $listIds)->whereNull('archived_at')->update(['archived_at' => now()]);
    }

    public function unarchiveAllLists(): void
    {
        $listIds = $this->lists()->whereNotNull('archived_at')->pluck('id');

        $this->lists()->whereNotNull('archived_at')->update(['archived_at' => null]);

        if ($listIds->isNotEmpty()) {
            Card::whereIn('list_id', $listIds)->whereNotNull('archived_at')->update(['archived_at' => null]);
        }
    }

    public function hasArchivedCardsInActiveLists(): bool
    {
        return $this->lists()
            ->active()
            ->whereHas('cards', fn($query) => $query->whereNotNull('archived_at'))
            ->exists();
    }

    public function hasArchivedLists(): bool
    {
        return $this->lists()->whereNotNull('archived_at')->exists();
    }

    public function hasActiveLists(): bool
    {
        return $this->lists()->whereNull('archived_at')->exists();
    }
}
