<?php

namespace FilamentKanban\Livewire\Boards;

use FilamentKanban\Models\Board;
use FilamentKanban\Models\BoardList;
use FilamentKanban\Models\Card;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;
use Livewire\Component;

class BoardView extends Component
{
    public Board $board;

    public bool $showArchivedCards = false;

    public function mount(Board $board): void
    {
        $this->board = $board;
    }

    #[On('lists-archived')]
    public function refreshBoard(): void
    {
        $this->board->refresh();
    }

    #[On('card-updated')]
    public function onCardUpdated(): void
    {
        $this->refreshBoard();
    }

    public function addList(string $name): void
    {
        $this->authorize('create', [BoardList::class, $this->board]);

        $this->board->lists()->create([
            'name' => $name,
            'order' => $this->board->lists()->max('order') + 1,
        ]);

        $this->refreshBoard();
    }

    public function updateListOrder(array $listIds): void
    {
        foreach ($listIds as $index => $id) {
            BoardList::where('id', $id)->update(['order' => $index]);
        }

        $this->refreshBoard();
    }

    public function updateCardOrder(array $groups): void
    {
        foreach ($groups as $group) {
            $listId = $group['value'];
            foreach ($group['items'] as $index => $item) {
                Card::where('id', $item['value'])->update([
                    'list_id' => $listId,
                    'order' => $index,
                ]);
            }
        }

        $this->refreshBoard();
    }

    public function archiveList(int $listId): void
    {
        $list = BoardList::findOrFail($listId);
        $this->authorize('archive', $list);

        $list->archive();

        Notification::make()
            ->success()
            ->title(__('kanban::kanban.notification.lists.List archived'))
            ->send();

        $this->refreshBoard();
    }

    public function toggleArchivedCards(): void
    {
        $this->showArchivedCards = !$this->showArchivedCards;
    }

    public function render()
    {
        return view('kanban::livewire.boards.view');
    }
}
