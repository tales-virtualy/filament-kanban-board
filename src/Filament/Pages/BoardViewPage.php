<?php

namespace FilamentKanban\Filament\Pages;

use FilamentKanban\Models\Board;
use FilamentKanban\Livewire\Boards\BoardView;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;

class BoardViewPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-view-columns';

    protected static string $view = 'kanban::filament.pages.boards.board-view-wrapper';

    protected static bool $shouldRegisterNavigation = false;

    public Board $board;

    protected $listeners = [
        'refresh-board' => '$refresh',
    ];

    public function mount(Board $board): void
    {
        $this->board = $board;
        $this->authorize('view', $board);
    }

    public function getTitle(): string
    {
        return $this->board->name;
    }

    public function refreshBoard(): void
    {
        $this->board->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('settings')
                ->label(__('kanban::kanban.buttons.Board Settings'))
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->visible(fn() => auth()->user()->can('update', $this->board))
                ->form([
                    \Filament\Forms\Components\TextInput::make('name')
                        ->label(__('kanban::kanban.Board Name'))
                        ->required()
                        ->default($this->board->name),
                    \Filament\Forms\Components\Textarea::make('description')
                        ->label(__('kanban::kanban.Description'))
                        ->default($this->board->description),
                    \Filament\Forms\Components\Toggle::make('is_private')
                        ->label(__('kanban::kanban.Private board'))
                        ->default($this->board->is_private),
                ])
                ->action(function (array $data) {
                    $this->board->update($data);
                    Notification::make()
                        ->success()
                        ->title(__('kanban::kanban.notification.boards.Settings updated'))
                        ->send();
                }),

            Action::make('members')
                ->label(__('kanban::kanban.member.title'))
                ->icon('heroicon-o-users')
                ->color('gray')
                ->visible(fn() => auth()->user()->can('update', $this->board))
                ->form([
                    Select::make('members')
                        ->label(__('kanban::kanban.member.title'))
                        ->multiple()
                        ->options(config('kanban.user_model')::where('id', '!=', $this->board->owner_id)->pluck('name', 'id'))
                        ->default(fn() => $this->board->members->pluck('id')->toArray())
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $this->board->members()->sync($data['members'] ?? []);
                    Notification::make()
                        ->success()
                        ->title(__('kanban::kanban.notification.boards.Members updated'))
                        ->send();
                }),

            Action::make('archiveAllLists')
                ->label(fn() => $this->board->hasActiveLists() ? __('kanban::kanban.buttons.Archive all lists') : __('kanban::kanban.buttons.Unarchive all lists'))
                ->icon(fn() => $this->board->hasActiveLists() ? 'heroicon-o-archive-box-arrow-down' : 'heroicon-o-arrow-path')
                ->color(fn() => $this->board->hasActiveLists() ? 'warning' : 'gray')
                ->visible(fn() => !$this->board->isArchived() && auth()->user()->can('update', $this->board) && ($this->board->hasActiveLists() || $this->board->hasArchivedLists()))
                ->requiresConfirmation()
                ->action(function () {
                    if (!$this->board->hasActiveLists() && $this->board->hasArchivedLists()) {
                        $this->board->unarchiveAllLists();
                        Notification::make()->success()->title(__('kanban::kanban.notification.boards.Lists unarchived'))->send();
                    } else {
                        $this->board->archiveAllListsAndCards();
                        Notification::make()->success()->title(__('kanban::kanban.notification.boards.Lists archived'))->send();
                    }
                    $this->dispatch('refresh-board');
                }),

            Action::make('archive')
                ->label(fn() => $this->board->isArchived() ? __('kanban::kanban.common.unarchive') : __('kanban::kanban.common.archive'))
                ->icon(fn() => $this->board->isArchived() ? 'heroicon-o-arrow-path' : 'heroicon-o-archive-box')
                ->color(fn() => $this->board->isArchived() ? 'gray' : 'warning')
                ->visible(fn() => auth()->user()->can($this->board->isArchived() ? 'unarchive' : 'archive', $this->board))
                ->requiresConfirmation()
                ->action(function () {
                    $this->board->isArchived() ? $this->board->unarchive() : $this->board->archive();
                    Notification::make()->success()->title($this->board->isArchived() ? __('kanban::kanban.notification.boards.Board archived') : __('kanban::kanban.notification.boards.Board unarchived'))->send();
                    $this->redirect(BoardListPage::getUrl());
                }),

            Action::make('delete')
                ->label(__('kanban::kanban.buttons.Delete board'))
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn() => auth()->user()->can('delete', $this->board))
                ->requiresConfirmation()
                ->action(function () {
                    $this->board->delete();
                    Notification::make()->success()->title(__('kanban::kanban.notification.boards.Board deleted'))->send();
                    $this->redirect(BoardListPage::getUrl());
                }),
        ];
    }
}
