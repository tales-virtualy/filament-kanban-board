<?php

namespace FilamentKanban\Livewire\Boards\Card;

use FilamentKanban\Models\Card;
use Livewire\Component;

class DatePopover extends Component
{
    public Card $card;

    public bool $open = false;

    public ?string $due_date = null;

    protected $listeners = [
        'card-updated' => 'syncDueDateFromCard',
    ];

    public function mount(Card $card): void
    {
        $this->card = $card;
        $this->authorize('view', $this->card);
        $this->syncDueDateFromCard();
    }

    public function syncDueDateFromCard(): void
    {
        $this->card->refresh();
        $this->due_date = optional($this->card->due_date)?->format('Y-m-d');
    }

    public function save(): void
    {
        $this->authorize('update', $this->card);

        $this->card->update([
            'due_date' => $this->due_date ?: null,
        ]);

        $this->card->refresh();

        $this->dispatch('card-updated');
        $this->open = false;
    }

    public function clear(): void
    {
        $this->due_date = null;
        $this->save();
    }

    public function render()
    {
        return view('kanban::livewire.boards.card.date-popover');
    }
}
