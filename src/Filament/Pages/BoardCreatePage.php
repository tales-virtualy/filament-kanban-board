<?php

namespace FilamentKanban\Filament\Pages;

use FilamentKanban\Models\Board;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;

class BoardCreatePage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-plus';

    protected string $view = 'kanban::filament.pages.boards.board-create-page';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function getTitle(): string
    {
        return __('kanban::kanban.buttons.Create Board');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('kanban::kanban.Board Name'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(__('kanban::kanban.Description'))
                    ->rows(3)
                    ->maxLength(1000),
                Toggle::make('is_private')
                    ->label(__('kanban::kanban.Private board'))
                    ->helperText(__('kanban::kanban.Only members will be able to see this board'))
                    ->default(false),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $data['owner_id'] = auth()->id();

        $board = Board::create($data);

        Notification::make()
            ->success()
            ->title(__('kanban::kanban.notification.boards.Board created successfully'))
            ->send();

        $this->redirect(BoardViewPage::getUrl(['board' => $board->id]));
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label(__('kanban::kanban.common.cancel'))
            ->color('gray')
            ->url(BoardListPage::getUrl());
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('kanban::kanban.buttons.Create Board'))
                ->submit('create'),
        ];
    }
}
