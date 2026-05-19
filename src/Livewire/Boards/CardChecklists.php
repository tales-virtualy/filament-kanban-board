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

    public ?int $editingItemId = null;
    public string $editingItemDescription = '';

    protected $listeners = [
        'checklist-updated' => 'refreshChecklists',
        'card-updated' => 'refreshChecklists',
    ];

    public function mount(Card $card): void
    {
        $this->card = $card;
        $this->authorize('view', $this->card);
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
        $this->authorize('update', $this->card);

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
        $this->authorize('update', $this->card);

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
        $this->authorize('update', $this->card);

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
        $this->authorize('update', $this->card);

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
        $this->authorize('update', $this->card);

        $item = CardChecklistItem::findOrFail($itemId);
        $item->toggle();

        CardActivityService::itemToggled($this->card, $item->description, $item->is_completed);

        $this->refreshChecklists();
        $this->dispatch('checklist-updated');
    }

    public function deleteItem(int $itemId): void
    {
        $this->authorize('update', $this->card);

        $item = CardChecklistItem::findOrFail($itemId);
        $description = $item->description;
        $item->delete();

        CardActivityService::itemRemoved($this->card, $description);

        $this->refreshChecklists();
        $this->dispatch('checklist-updated');
    }

    public function startEditingItem(int $itemId): void
    {
        $item = CardChecklistItem::findOrFail($itemId);
        $this->editingItemId = $itemId;
        $this->editingItemDescription = $item->description;
        $this->addingItemToChecklistId = null;
    }

    public function updateItem(): void
    {
        $this->authorize('update', $this->card);

        $this->validate([
            'editingItemDescription' => 'required|string|max:255',
        ]);

        CardChecklistItem::query()
            ->whereKey($this->editingItemId)
            ->update(['description' => $this->editingItemDescription]);

        $this->reset(['editingItemId', 'editingItemDescription']);
        $this->refreshChecklists();
        $this->dispatch('checklist-updated');
    }

    public function cancelEditingItem(): void
    {
        $this->reset(['editingItemId', 'editingItemDescription']);
    }

    public function moveChecklist(int $checklistId, int $targetIndex): void
    {
        $this->authorize('update', $this->card);

        $checklistIds = $this->card->checklists()
            ->orderBy('position')
            ->pluck('id')
            ->all();

        if (!in_array($checklistId, $checklistIds, true)) {
            return;
        }

        $checklistIds = array_values(array_filter($checklistIds, fn (int $id): bool => $id !== $checklistId));
        $targetIndex = max(0, min($targetIndex, count($checklistIds)));
        array_splice($checklistIds, $targetIndex, 0, [$checklistId]);

        foreach ($checklistIds as $index => $id) {
            CardChecklist::query()
                ->whereKey($id)
                ->update(['position' => $index]);
        }

        $this->refreshChecklists();
        $this->dispatch('checklist-updated');
    }

    public function moveChecklistItem(int $itemId, int $targetIndex): void
    {
        $this->authorize('update', $this->card);

        $item = CardChecklistItem::findOrFail($itemId);
        $checklistId = $item->checklist_id;

        $itemIds = CardChecklistItem::query()
            ->where('checklist_id', $checklistId)
            ->orderBy('position')
            ->pluck('id')
            ->all();

        if (!in_array($itemId, $itemIds, true)) {
            return;
        }

        $itemIds = array_values(array_filter($itemIds, fn (int $id): bool => $id !== $itemId));
        $targetIndex = max(0, min($targetIndex, count($itemIds)));
        array_splice($itemIds, $targetIndex, 0, [$itemId]);

        foreach ($itemIds as $index => $id) {
            CardChecklistItem::query()
                ->whereKey($id)
                ->update(['position' => $index]);
        }

        $this->refreshChecklists();
        $this->dispatch('checklist-updated');
    }

    public function render()
    {
        return view('kanban::livewire.boards.card-checklists');
    }
}
