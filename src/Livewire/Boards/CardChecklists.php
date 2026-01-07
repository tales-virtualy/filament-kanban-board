<?php

namespace FilamentKanban\Livewire\Boards;

use FilamentKanban\Models\Card;
use FilamentKanban\Models\CardChecklist;
use FilamentKanban\Models\CardChecklistItem;
use FilamentKanban\Services\CardActivityService;
use Livewire\Component;

class CardChecklists extends Component
{
    public Card $card;
    public $checklists;
    public string $newChecklistTitle = '';
    public ?int $editingChecklistId = null;
    public string $editingChecklistTitle = '';

    public ?int $addingItemToChecklistId = null;
    public string $newItemDescription = '';

    protected $listeners = [
        'checklist-updated' => 'refreshChecklists',
    ];

    public function mount(Card $card): void
    {
        $this->card = $card;
        $this->refreshChecklists();
    }

    public function refreshChecklists(): void
    {
        $this->checklists = $this->card->checklists()
            ->with(['items' => fn($q) => $q->orderBy('position')])
            ->orderBy('position')
            ->get();
    }

    public function createChecklist(): void
    {
        $this->validate([
            'newChecklistTitle' => 'required|string|max:255',
        ]);

        $position = (int) $this->card->checklists()->max('position') + 1;

        $this->card->checklists()->create([
            'title' => $this->newChecklistTitle,
            'position' => $position,
        ]);

        $this->reset('newChecklistTitle');
        $this->refreshChecklists();
    }

    public function startEditingChecklist(int $checklistId): void
    {
        $checklist = CardChecklist::findOrFail($checklistId);
        $this->editingChecklistId = $checklistId;
        $this->editingChecklistTitle = $checklist->title;
    }

    public function updateChecklist(): void
    {
        $this->validate([
            'editingChecklistTitle' => 'required|string|max:255',
        ]);

        $checklist = CardChecklist::findOrFail($this->editingChecklistId);
        $checklist->update(['title' => $this->editingChecklistTitle]);

        $this->reset(['editingChecklistId', 'editingChecklistTitle']);
        $this->refreshChecklists();
    }

    public function cancelEditingChecklist(): void
    {
        $this->reset(['editingChecklistId', 'editingChecklistTitle']);
    }

    public function deleteChecklist(int $checklistId): void
    {
        $checklist = CardChecklist::findOrFail($checklistId);
        $title = $checklist->title;
        $checklist->delete();

        CardActivityService::checklistRemoved($this->card, $title);
        $this->refreshChecklists();

        $this->dispatch('checklist-updated');
    }

    public function startAddingItem(int $checklistId): void
    {
        $this->addingItemToChecklistId = $checklistId;
        $this->reset('newItemDescription');
    }

    public function addItem(): void
    {
        $this->validate([
            'newItemDescription' => 'required|string|max:255',
        ]);

        $checklist = CardChecklist::findOrFail($this->addingItemToChecklistId);
        $position = (int) $checklist->items()->max('position') + 1;

        $checklist->items()->create([
            'description' => $this->newItemDescription,
            'position' => $position,
            'created_by' => auth()->id(),
        ]);

        CardActivityService::itemAdded($this->card, $this->newItemDescription, $checklist->title);

        $this->reset(['newItemDescription', 'addingItemToChecklistId']);
        $this->refreshChecklists();

        $this->dispatch('checklist-updated');
    }

    public function cancelAddingItem(): void
    {
        $this->reset(['addingItemToChecklistId', 'newItemDescription']);
    }

    public function toggleItem(int $itemId): void
    {
        $item = CardChecklistItem::findOrFail($itemId);
        $item->toggle();

        CardActivityService::itemToggled($this->card, $item->description, $item->is_completed);

        $this->refreshChecklists();
        $this->dispatch('checklist-updated');
    }

    public function deleteItem(int $itemId): void
    {
        $item = CardChecklistItem::findOrFail($itemId);
        $description = $item->description;
        $item->delete();

        CardActivityService::itemRemoved($this->card, $description);

        $this->refreshChecklists();
        $this->dispatch('checklist-updated');
    }

    public function render()
    {
        return view('kanban::livewire.boards.card-checklists');
    }
}
