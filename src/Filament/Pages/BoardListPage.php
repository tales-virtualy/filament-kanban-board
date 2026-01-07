<?php

namespace FilamentKanban\Filament\Pages;

use FilamentKanban\Models\Board;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class BoardListPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    protected static string $view = 'kanban::filament.pages.boards.board-list-page';

    public bool $showArchived = false;

    public function getTitle(): string
    {
        return __('kanban::kanban.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('kanban::kanban.title');
    }

    public function toggleArchived(): void
    {
        $this->showArchived = !$this->showArchived;
    }

    public function getBoardsProperty()
    {
        return Board::query()
            ->when($this->showArchived, fn(Builder $q) => $q->archived(), fn(Builder $q) => $q->active())
            ->where(function (Builder $q) {
                $q->where('owner_id', auth()->id())
                    ->orWhereHas('members', fn(Builder $sq) => $sq->where('user_id', auth()->id()))
                    ->orWhere('is_private', false);
            })
            ->latest()
            ->get();
    }
}
