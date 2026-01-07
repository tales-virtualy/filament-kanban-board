<?php

namespace FilamentKanban\Services;

use FilamentKanban\Models\Card;
use FilamentKanban\Models\CardActivityLog;

class CardActivityService
{
    public static function log($card, string $action, ?array $data = null): void
    {
        $cardId = $card instanceof Card ? $card->id : $card;

        CardActivityLog::create([
            'card_id' => $cardId,
            'user_id' => auth()->id() ?? config('kanban.system_user_id'),
            'action' => $action,
            'new_value' => $data,
        ]);
    }

    public static function created(Card $card): void
    {
        self::log($card, 'created');
    }

    public static function moved(Card $card, string $listName): void
    {
        self::log($card, 'moved', ['list_name' => $listName]);
    }

    public static function descriptionUpdated(Card $card): void
    {
        self::log($card, 'description_updated');
    }

    public static function commentAdded(Card $card): void
    {
        self::log($card, 'comment_added');
    }

    public static function dueDateChanged(Card $card, ?string $dueDate): void
    {
        self::log($card, 'due_date_changed', ['due_date' => $dueDate]);
    }

    public static function assigned(Card $card, ?string $userName): void
    {
        self::log($card, 'assigned', ['user_name' => $userName]);
    }

    public static function unassigned(Card $card): void
    {
        self::log($card, 'unassigned');
    }

    public static function memberAdded(Card $card, string $memberName): void
    {
        self::log($card, 'member_added', ['member_name' => $memberName]);
    }

    public static function memberRemoved(Card $card, string $memberName): void
    {
        self::log($card, 'member_removed', ['member_name' => $memberName]);
    }

    public static function tagAdded(Card $card, string $tagName): void
    {
        self::log($card, 'tag_added', ['tag_name' => $tagName]);
    }

    public static function tagRemoved(Card $card, string $tagName): void
    {
        self::log($card, 'tag_removed', ['tag_name' => $tagName]);
    }

    public static function attachmentAdded(Card $card, string $fileName): void
    {
        self::log($card, 'attachment_added', ['file_name' => $fileName]);
    }

    public static function attachmentRemoved(Card $card, string $fileName): void
    {
        self::log($card, 'attachment_removed', ['file_name' => $fileName]);
    }

    public static function checklistAdded(Card $card, string $title): void
    {
        self::log($card, 'checklist_added', ['checklist_title' => $title]);
    }

    public static function checklistRemoved(Card $card, string $title): void
    {
        self::log($card, 'checklist_removed', ['checklist_title' => $title]);
    }

    public static function itemAdded(Card $card, string $itemDescription, string $checklistTitle): void
    {
        self::log($card, 'item_added', [
            'item_description' => $itemDescription,
            'checklist_title' => $checklistTitle,
        ]);
    }

    public static function itemToggled(Card $card, string $itemDescription, bool $completed): void
    {
        $action = $completed ? 'item_completed' : 'item_uncompleted';
        self::log($card, $action, ['item_description' => $itemDescription]);
    }

    public static function itemRemoved(Card $card, string $itemDescription): void
    {
        self::log($card, 'item_removed', ['item_description' => $itemDescription]);
    }
}
