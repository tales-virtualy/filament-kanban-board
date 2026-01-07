<?php

namespace FilamentKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardActivityLog extends Model
{
    protected $fillable = [
        'card_id',
        'user_id',
        'action',
        'new_value',
    ];

    protected $casts = [
        'new_value' => 'array',
    ];

    public function getTable()
    {
        return config('kanban.tables.card_activity_logs', parent::getTable());
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('kanban.user_model'));
    }

    public function getDescriptionAttribute(): string
    {
        return match ($this->action) {
            'created' => 'criou o cartão',
            'moved' => 'moveu o cartão para ' . ($this->new_value['list_name'] ?? ''),
            'comment_added' => 'comentou no cartão',
            'assigned' => 'atribuiu a ' . ($this->new_value['user_name'] ?? ''),
            'unassigned' => 'removeu a atribuição',
            'member_added' => 'adicionou ' . ($this->new_value['member_name'] ?? '') . ' ao cartão',
            'member_removed' => 'removeu ' . ($this->new_value['member_name'] ?? '') . ' do cartão',
            'tag_added' => 'adicionou a tag ' . ($this->new_value['tag_name'] ?? ''),
            'tag_removed' => 'removeu a tag ' . ($this->new_value['tag_name'] ?? ''),
            'due_date_changed' => 'alterou o prazo para ' . ($this->new_value['due_date'] ?? ''),
            'due_date_removed' => 'removeu o prazo',
            'attachment_added' => 'anexou ' . ($this->new_value['file_name'] ?? 'um arquivo'),
            'attachment_removed' => 'removeu o anexo ' . ($this->new_value['file_name'] ?? ''),
            'description_updated' => 'atualizou a descrição',
            'checklist_added' => 'adicionou o checklist ' . ($this->new_value['checklist_title'] ?? ''),
            'checklist_removed' => 'removeu o checklist ' . ($this->new_value['checklist_title'] ?? ''),
            'item_added' => 'adicionou a tarefa ' . ($this->new_value['item_description'] ?? '') . ' ao checklist ' . ($this->new_value['checklist_title'] ?? ''),
            'item_removed' => 'removeu a tarefa ' . ($this->new_value['item_description'] ?? ''),
            'item_completed' => 'concluiu a tarefa ' . ($this->new_value['item_description'] ?? ''),
            'item_uncompleted' => 'marcou como pendente a tarefa ' . ($this->new_value['item_description'] ?? ''),
            default => 'realizou uma ação',
        };
    }
}
