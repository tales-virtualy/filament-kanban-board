<?php

namespace FilamentKanban\Filament\Pages;

use FilamentKanban\Models\Board;
use FilamentKanban\Livewire\Boards\BoardView;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Panel;

class BoardViewPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-view-columns';

    protected string $view = 'kanban::filament.pages.boards.board-view-wrapper';

    protected static bool $shouldRegisterNavigation = false;

    public Board $board;

    public bool $accessDenied = false;

    protected $listeners = [
        'refresh-board' => 'refreshBoard',
    ];

    public static function getRoutePath(Panel $panel): string
    {
        return '/board-view-page/{board}';
    }

    public function mount(Board $board): void
    {
        $this->board = $board->load(['owner', 'members']);

        if (! auth()->user()?->can('view', $board)) {
            $this->accessDenied = true;

            return;
        }
    }

    public function getTitle(): string
    {
        return $this->board->name ?? __('kanban::kanban.title');
    }

    public function refreshBoard(): void
    {
        $this->board->refresh();
        $this->board->load(['owner', 'members']);
    }

    protected function getHeaderActions(): array
    {
        if ($this->accessDenied) {
            return [];
        }

        return [
            Action::make('settings')
                ->label(__('kanban::kanban.buttons.Board Settings'))
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->visible(fn() => auth()->user()->can('update', $this->board))
                ->fillForm(fn(): array => [
                    'name' => $this->board->name,
                    'description' => $this->board->description,
                    'is_private' => (bool) $this->board->is_private,
                ])
                ->form([
                    \Filament\Forms\Components\TextInput::make('name')
                        ->label(__('kanban::kanban.Board Name'))
                        ->required(),
                    \Filament\Forms\Components\Textarea::make('description')
                        ->label(__('kanban::kanban.Description'))
                        ->rows(3),
                    \Filament\Forms\Components\Toggle::make('is_private')
                        ->label(__('kanban::kanban.Private board')),
                ])
                ->action(function (array $data) {
                    $data['is_private'] = (bool) ($data['is_private'] ?? false);
                    $this->board->update($data);
                    Notification::make()
                        ->success()
                        ->title(__('kanban::kanban.notification.boards.Settings updated'))
                        ->send();
                }),

            Action::make('members')
                ->label(fn (): string => $this->membersActionLabel())
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
                    $this->board->load(['owner', 'members']);

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

                    $this->refreshBoard();
                    $this->dispatch('refresh-board')->to(BoardView::class);
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

    protected function membersActionLabel(): string
    {
        $count = $this->board->participants()->count();

        if ($count <= 1) {
            return __('kanban::kanban.member.title');
        }

        return __('kanban::kanban.member.title') . " ({$count})";
    }
}
