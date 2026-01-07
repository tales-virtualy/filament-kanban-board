<?php

namespace FilamentKanban;

use Filament\Contracts\Plugin;
use Filament\Panel;
use FilamentKanban\Filament\Pages\BoardListPage;
use FilamentKanban\Filament\Pages\BoardCreatePage;
use FilamentKanban\Filament\Pages\BoardViewPage;

class FilamentKanbanPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-kanban';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            BoardListPage::class,
            BoardCreatePage::class,
            BoardViewPage::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
