<?php

namespace FilamentKanban\Livewire\Boards\Card;

use FilamentKanban\Models\Card;
use Livewire\Component;

class MembersPopover extends Component
{
    public Card $card;
    public bool $open = false;
    public array $members = [];
    public $availableUsers;

    public function mount(Card $card): void
    {
        $this->card = $card;
        $this->members = $card->members->pluck('id')->toArray();
        $board = $card->list->board;

        $userModel = config('kanban.user_model');
        $this->availableUsers = $userModel::whereIn(
            'id',
            $board->members->pluck('id')->push($board->owner_id)
        )->orderBy('name')->get();
    }

    public function toggleMember(int $userId): void
    {
        if (in_array($userId, $this->members)) {
            $this->members = array_diff($this->members, [$userId]);
            $this->card->removeMember($userId);
        } else {
            $this->members[] = $userId;
            $this->card->addMember($userId);
        }

        $this->dispatch('card-updated');
    }

    public function render()
    {
        return view('kanban::livewire.boards.card.members-popover');
    }
}
