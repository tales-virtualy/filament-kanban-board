<?php

namespace FilamentKanban\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardList extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'board_id',
        'name',
        'order',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getTable()
    {
        return config('kanban.tables.lists', parent::getTable());
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class, 'list_id');
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

    public function hasArchivedCards(): bool
    {
        return $this->cards()->whereNotNull('archived_at')->exists();
    }

    public function archive(): void
    {
        if ($this->isArchived()) {
            return;
        }

        $this->update(['archived_at' => now()]);
        $this->cards()->whereNull('archived_at')->update(['archived_at' => now()]);
    }

    public function unarchive(): void
    {
        if (!$this->isArchived()) {
            return;
        }

        $this->update(['archived_at' => null]);
        $this->cards()->whereNotNull('archived_at')->update(['archived_at' => null]);
    }
}
