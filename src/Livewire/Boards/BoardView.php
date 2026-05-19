<?php

namespace FilamentKanban\Livewire\Boards;

use Illuminate\Support\Facades\DB;
use Filament\Facades\Filament;
use FilamentKanban\Models\Board;
use FilamentKanban\Models\BoardList;
use FilamentKanban\Models\Card;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;

class BoardView extends Component
{
    public int $boardId;

    public bool $showArchivedLists = false;

    public bool $showArchivedCards = false;

    public ?int $activeCardId = null;

    public ?int $activeListId = null;

    public int $cardModalIteration = 0;

    public function mount(Board $board): void
    {
        $this->boardId = (int) $board->getKey();
    }

    #[On('lists-archived')]
    #[On('refresh-board')]
    public function refreshBoard(): void
    {
        // The board is reloaded from its ID on each request.
    }

    #[On('card-updated')]
    public function onCardUpdated(): void
    {
        $this->refreshBoard();
    }

    protected function authorizePanelUser(string $ability, mixed $arguments = []): void
    {
        $user = Filament::auth()->user() ?? auth()->user();

        abort_unless($user, 403, 'Kanban debug: unauthenticated Livewire request.');

        Gate::forUser($user)->authorize($ability, $arguments);
    }

    public function addList(string $name): void
    {
        $name = trim($name);

        if ($name === '') {
            return;
        }

        $board = $this->getBoard();

        $this->authorizePanelUser('create', [BoardList::class, $board]);

        $board->lists()->create([
            'name' => $name,
            'order' => $board->lists()->max('order') + 1,
        ]);

        $this->dispatch('refresh-board');
        $this->refreshBoard();
    }

    public function openCreateCardModal(int $listId): void
    {
        $list = BoardList::query()->findOrFail($listId);

        $this->authorizePanelUser('create', [Card::class, $list]);

        $this->activeCardId = null;
        $this->activeListId = $listId;
        $this->cardModalIteration++;

        $this->dispatch('open-modal', id: 'card-modal');
    }

    public function openEditCardModal(int $cardId): void
    {
        $card = Card::query()->findOrFail($cardId);

        $this->authorizePanelUser('view', $card);

        $this->activeCardId = $cardId;
        $this->activeListId = null;
        $this->cardModalIteration++;

        $this->dispatch('open-modal', id: 'card-modal');
    }

    public function updateListOrder(array $listIds): void
    {
        $this->authorizePanelUser('update', $this->getBoard());

        foreach ($listIds as $index => $id) {
            BoardList::where('id', $id)->update(['order' => $index]);
        }

        $this->refreshBoard();
    }

    public function moveList(int $listId, int $targetIndex): void
    {
        $board = $this->getBoard();

        $this->authorizePanelUser('update', $board);

        $listIds = $board->lists()
            ->active()
            ->orderBy('order')
            ->pluck('id')
            ->all();

        if (!in_array($listId, $listIds, true)) {
            return;
        }

        $listIds = array_values(array_filter($listIds, fn(int $id): bool => $id !== $listId));
        $targetIndex = max(0, min($targetIndex, count($listIds)));

        array_splice($listIds, $targetIndex, 0, [$listId]);

        foreach ($listIds as $index => $id) {
            BoardList::query()
                ->where('board_id', $board->getKey())
                ->whereKey($id)
                ->update(['order' => $index]);
        }

        $this->refreshBoard();
    }

    public function updateCardOrder(array $groups): void
    {
        $this->authorizePanelUser('update', $this->getBoard());

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

    public function moveCard(int $cardId, int $targetListId, int $targetIndex): void
    {
        $board = $this->getBoard();

        $this->authorizePanelUser('update', $board);

        $card = Card::query()
            ->whereHas('list', fn($query) => $query->where('board_id', $board->getKey()))
            ->findOrFail($cardId);

        $targetList = BoardList::query()
            ->where('board_id', $board->getKey())
            ->findOrFail($targetListId);

        $sourceListId = $card->list_id;

        $targetCardIds = $targetList->cards()
            ->active()
            ->whereKeyNot($cardId)
            ->orderBy('order')
            ->pluck('id')
            ->all();

        $targetIndex = max(0, min($targetIndex, count($targetCardIds)));
        array_splice($targetCardIds, $targetIndex, 0, [$cardId]);

        DB::transaction(function () use ($cardId, $sourceListId, $targetList, $targetCardIds): void {
            foreach ($targetCardIds as $index => $id) {
                Card::query()
                    ->whereKey($id)
                    ->update([
                        'list_id' => $targetList->getKey(),
                        'order' => $index,
                    ]);
            }

            if ($sourceListId === $targetList->getKey()) {
                return;
            }

            $sourceCardIds = Card::query()
                ->where('list_id', $sourceListId)
                ->active()
                ->whereKeyNot($cardId)
                ->orderBy('order')
                ->pluck('id')
                ->all();

            foreach ($sourceCardIds as $index => $id) {
                Card::query()
                    ->whereKey($id)
                    ->update(['order' => $index]);
            }
        });

        $this->refreshBoard();
    }

    public function archiveList(int $listId): void
    {
        $list = BoardList::findOrFail($listId);
        $this->authorizePanelUser('archive', $list);

        $list->archive();

        Notification::make()
            ->success()
            ->title(__('kanban::kanban.notification.lists.List archived'))
            ->send();

        $this->dispatch('refresh-board');
        $this->refreshBoard();
    }

    public function unarchiveList(int $listId): void
    {
        $list = BoardList::findOrFail($listId);
        $this->authorizePanelUser('unarchive', $list);

        $list->unarchive();

        Notification::make()
            ->success()
            ->title(__('kanban::kanban.notification.lists.List unarchived'))
            ->send();

        $this->dispatch('refresh-board');
        $this->refreshBoard();
    }

    public function toggleArchivedLists(): void
    {
        $this->showArchivedLists = !$this->showArchivedLists;
    }

    public function toggleArchivedCards(): void
    {
        $this->showArchivedCards = !$this->showArchivedCards;
    }

    public function unarchiveCard(int $cardId): void
    {
        $card = Card::query()->findOrFail($cardId);

        $this->authorizePanelUser('unarchive', $card);

        $card->unarchive();

        Notification::make()
            ->success()
            ->title(__('kanban::kanban.notification.cards.Card unarchived'))
            ->send();

        $this->dispatch('card-updated');
        $this->refreshBoard();
    }

    public function getCardsForList(BoardList $list)
    {
        return $list->cards()
            ->when(
                $list->isArchived(),
                fn($query) => $query->archived(),
                fn($query) => $query->when(
                    !$this->showArchivedCards,
                    fn($q) => $q->active(),
                ),
            )
            ->orderBy('order')
            ->get();
    }

    protected function getBoard(): Board
    {
        return Board::query()->findOrFail($this->boardId);
    }

    protected function getVisibleLists(Board $board)
    {
        return $board->lists()
            ->when(
                !$this->showArchivedLists,
                fn($query) => $query->active(),
            )
            ->orderBy('order')
            ->get();
    }

    public function render()
    {
        $board = $this->getBoard();

        return view('kanban::livewire.boards.view', [
            'board' => $board,
            'lists' => $this->getVisibleLists($board),
        ]);
    }
}
