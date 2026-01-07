<?php

namespace FilamentKanban\Models;

use FilamentKanban\Services\CardActivityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CardAttachment extends Model
{
    protected $fillable = [
        'card_id',
        'user_id',
        'file_name',
        'file_path',
        'mime_type',
        'size',
    ];

    public function getTable()
    {
        return config('kanban.tables.card_attachments', parent::getTable());
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('kanban.user_model'));
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('s3')->url($this->file_path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    protected static function booted(): void
    {
        static::created(function ($attachment) {
            CardActivityService::attachmentAdded($attachment->card, $attachment->file_name);
        });

        static::deleting(function ($attachment) {
            Storage::disk('s3')->delete($attachment->file_path);
        });
    }
}
