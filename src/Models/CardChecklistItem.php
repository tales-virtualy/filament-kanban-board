<?php

namespace FilamentKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardChecklistItem extends Model
{
    protected $fillable = [
        'checklist_id',
        'description',
        'is_completed',
        'position',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function getTable()
    {
        return config('kanban.tables.card_checklist_items', parent::getTable());
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(CardChecklist::class, 'checklist_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('kanban.user_model'), 'created_by');
    }

    /**
     * Marca o item como completo
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    /**
     * Marca o item como incompleto
     */
    public function markAsIncomplete(): void
    {
        $this->update([
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }

    /**
     * Toggle do status de conclusão
     */
    public function toggle(): void
    {
        if ($this->is_completed) {
            $this->markAsIncomplete();
        } else {
            $this->markAsCompleted();
        }
    }
}
