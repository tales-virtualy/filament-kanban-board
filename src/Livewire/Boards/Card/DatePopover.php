<?php

namespace FilamentKanban\Livewire\Boards\Card;

use FilamentKanban\Models\Card;
use Livewire\Component;

class DatePopover extends Component
{
    public Card $card;
    public bool $open = false;
    public ?string $due_date = null;

    public function mount(Card $card): void
    {
        $this->card = $card;
        $this->due_date = optional($card->due_date)?->format('Y-m-d');
    }

    public function save(): void
    {
        $this->card->update([
            'due_date' => $this->due_date,
        ]);

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
