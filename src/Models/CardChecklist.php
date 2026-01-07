<?php

namespace FilamentKanban\Models;

use FilamentKanban\Services\CardActivityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardChecklist extends Model
{
    protected $fillable = [
        'card_id',
        'title',
        'position',
    ];

    public function getTable()
    {
        return config('kanban.tables.card_checklists', parent::getTable());
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CardChecklistItem::class, 'checklist_id')->orderBy('position');
    }

    public function getProgressAttribute(): array
    {
        $total = $this->items()->count();
        $completed = $this->items()->where('is_completed', true)->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }

    protected static function booted(): void
    {
        static::created(function ($checklist) {
            CardActivityService::checklistAdded($checklist->card, $checklist->title);
        });
    }
}
