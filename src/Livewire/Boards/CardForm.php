<?php

namespace FilamentKanban\Livewire\Boards;

use FilamentKanban\Models\BoardList;
use FilamentKanban\Models\Card;
use FilamentKanban\Services\CardActivityService;
use Filament\Notifications\Notification;
use Livewire\Component;

class CardForm extends Component
{
    public ?Card $card = null;
    public ?int $listId = null;
    public string $title = '';
    public ?string $description = null;
    public $currentBoardLists = [];
    public bool $confirmingArchive = false;

    protected $listeners = [
        'checklist-updated' => '$refresh',
        'card-updated' => 'refreshCard',
    ];

    public function refreshCard(): void
    {
        if ($this->card) {
            $this->card->refresh();
            $this->card->load(['checklists.items', 'members', 'tags']);
        }
    }

    protected function rules(): array
    {
        return [
            "title" => "required|string|max:255",
            "description" => "nullable|string",
        ];
    }

    public function mount(?int $listId = null, ?int $cardId = null): void
    {
        $this->listId = $listId;

        if ($cardId) {
            $this->card = Card::with([
                'checklists.items',
                'members',
                'tags',
                'list.board.lists'
            ])->findOrFail($cardId);
            $this->title = $this->card->title;
            $this->description = $this->card->description;
            $this->currentBoardLists = $this->card->list->board->lists()
                ->active()
                ->orderBy('order')
                ->get();
            $this->listId = $this->card->list_id;
        } elseif ($listId) {
            $list = BoardList::findOrFail($listId);
            $this->currentBoardLists = $list->board->lists()
                ->active()
                ->orderBy('order')
                ->get();
        }
    }

    public function save(bool $close = true): void
    {
        $this->validate();

        if ($this->card) {
            $this->authorize('update', $this->card);

            $this->card->update([
                'title' => $this->title,
                'description' => $this->description,
            ]);
        } else {
            $list = BoardList::findOrFail($this->listId);
            $this->authorize('create', [Card::class, $list]);
            $order = (int) $list->cards()->max('order') + 1;

            $this->card = $list->cards()->create([
                'title' => $this->title,
                'description' => $this->description,
                'order' => $order,
                'created_by' => auth()->id(),
            ]);
        }

        $this->dispatch('card-updated');

        if ($close) {
            $this->dispatch('close-modal', id: 'card-modal');
        } else {
            Notification::make()
                ->success()
                ->title(__('kanban::kanban.notification.cards.Card saved'))
                ->send();
        }
    }

    public function createChecklist(): void
    {
        if (!$this->card) {
            return;
        }

        $position = (int) $this->card->checklists()->max('position') + 1;

        $this->card->checklists()->create([
            'title' => __('kanban::kanban.checklist.default_title'),
            'position' => $position,
        ]);

        $this->card->load('checklists');
        $this->dispatch('checklist-updated');
    }

    public function moveCard(int $newListId): void
    {
        if (!$this->card)
            return;

        $this->authorize('update', $this->card);

        $newList = BoardList::findOrFail($newListId);
        $order = (int) $newList->cards()->max('order') + 1;

        $this->card->update([
            'list_id' => $newListId,
            'order' => $order
        ]);

        CardActivityService::moved($this->card, $newList->name);

        $this->dispatch('card-updated');
        $this->card->refresh();
        $this->listId = $newListId;

        Notification::make()
            ->success()
            ->title(__('kanban::kanban.notification.cards.Card moved successfully'))
            ->send();
    }

    public function archiveCard(): void
    {
        if (!$this->card) {
            return;
        }

        $this->authorize('archive', $this->card);

        if (!$this->confirmingArchive) {
            $this->confirmingArchive = true;
            return;
        }

        $this->card->archive();

        $this->dispatch('card-updated');
        $this->dispatch('close-modal', id: 'card-modal');

        Notification::make()
            ->success()
            ->title(__('kanban::kanban.notification.cards.Card archived'))
            ->send();
    }

    public function render()
    {
        return view('kanban::livewire.boards.card-form');
    }
}
