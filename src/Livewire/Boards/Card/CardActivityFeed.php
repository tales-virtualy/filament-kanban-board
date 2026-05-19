<?php

namespace FilamentKanban\Livewire\Boards\Card;

use FilamentKanban\Models\Card;
use Livewire\Component;

class CardActivityFeed extends Component
{
    public Card $card;

    public bool $compact = false;

    public bool $showAll = false;

    protected $listeners = [
        'card-updated' => '$refresh',
    ];

    public function mount(Card $card, bool $compact = false): void
    {
        $this->card = $card;
        $this->compact = $compact;
        $this->authorize('view', $this->card);
    }

    public function toggleShowAll(): void
    {
        $this->showAll = !$this->showAll;
    }

    public function render()
    {
        $query = $this->card->activities()->with('user')->latest();

        $activities = $this->showAll ? $query->get() : $query->take(5)->get();

        return view('kanban::livewire.boards.card.card-activity-feed', [
            'activities' => $activities,
            'hasMore' => $this->card->activities()->count() > 5 && !$this->showAll,
        ]);
    }
}
