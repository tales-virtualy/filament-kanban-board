<?php

namespace FilamentKanban\Providers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Livewire\Livewire;
use FilamentKanban\Livewire\Boards\BoardView;
use FilamentKanban\Livewire\Boards\CardForm;
use FilamentKanban\Livewire\Boards\CardChecklists;
use FilamentKanban\Livewire\Boards\Card\AttachmentsPopover;
use FilamentKanban\Livewire\Boards\Card\CardActivityFeed;
use FilamentKanban\Livewire\Boards\Card\CardAttachments;
use FilamentKanban\Livewire\Boards\Card\CardComments;
use FilamentKanban\Livewire\Boards\Card\DatePopover;
use FilamentKanban\Livewire\Boards\Card\MembersPopover;
use FilamentKanban\Livewire\Boards\Card\TagsPopover;
use FilamentKanban\Livewire\Boards\Components\BoardCard;

class KanbanServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-kanban-board')
            ->hasConfigFile('kanban')
            ->hasViews('kanban')
            ->hasTranslations()
            ->hasMigrations([
                'create_kanban_tables',
                'create_kanban_tag_tables'
            ]);
    }

    public function packageBooted(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'kanban');
        $this->registerLivewireComponents();
    }

    protected function registerLivewireComponents(): void
    {
        Livewire::component('kanban-board-view', BoardView::class);
        Livewire::component('kanban-card-form', CardForm::class);
        Livewire::component('kanban-card-checklists', CardChecklists::class);

        // Popovers
        Livewire::component('kanban-attachments-popover', AttachmentsPopover::class);
        Livewire::component('kanban-date-popover', DatePopover::class);
        Livewire::component('kanban-members-popover', MembersPopover::class);
        Livewire::component('kanban-tags-popover', TagsPopover::class);

        // Feeds & Lists
        Livewire::component('kanban-card-activity-feed', CardActivityFeed::class);
        Livewire::component('kanban-card-attachments', CardAttachments::class);
        Livewire::component('kanban-card-comments', CardComments::class);

        // Components
        Livewire::component('kanban-board-card', BoardCard::class);
    }
}
