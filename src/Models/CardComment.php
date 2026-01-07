<?php

namespace FilamentKanban\Models;

use FilamentKanban\Services\CardActivityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardComment extends Model
{
    protected $fillable = [
        'card_id',
        'user_id',
        'body',
    ];

    public function getTable()
    {
        return config('kanban.tables.card_comments', parent::getTable());
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('kanban.user_model'));
    }

    protected static function booted(): void
    {
        static::created(function ($comment) {
            CardActivityService::commentAdded($comment->card);
        });
    }
}
