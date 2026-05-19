<?php

namespace FilamentKanban\Livewire\Boards\Components;

use FilamentKanban\Models\Board;
use FilamentKanban\Filament\Pages\BoardViewPage;
use Livewire\Component;

class BoardCard extends Component
{
    public Board $board;

    public function mount(Board $board): void
    {
        $this->board = $board;
    }

    public function getCanViewProperty(): bool
    {
        return auth()->user()?->can('view', $this->board) ?? false;
    }

    public function render()
    {
        return view('kanban::livewire.boards.components.board-card');
    }

    public function openBoard()
    {
        return $this->redirect(BoardViewPage::getUrl(['board' => $this->board]));
    }
}
