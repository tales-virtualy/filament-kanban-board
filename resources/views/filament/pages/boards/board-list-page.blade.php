<x-filament-panels::page>
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <x-filament::button
                color="gray"
                size="sm"
                icon="{{ $this->showArchived ? 'heroicon-o-eye-slash' : 'heroicon-o-eye' }}"
                wire:click="toggleArchived"
                outlined
            >
                {{ $this->showArchived ? __('kanban::kanban.buttons.Hide archived') : __('kanban::kanban.buttons.Show archived') }}
            </x-filament::button>
        </div>

        @if($this->boards->isNotEmpty())
            <x-filament::button
                tag="a"
                color="primary"
                href="{{ \FilamentKanban\Filament\Pages\BoardCreatePage::getUrl() }}"
                icon="heroicon-o-plus"
            >
                {{ __('kanban::kanban.buttons.Create Board') }}
            </x-filament::button>
        @endif
    </div>

    @if($this->boards->isNotEmpty())
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
            @foreach($this->boards as $board)
                @livewire(
                    'kanban-board-card',
                    ['board' => $board],
                    key('board-card-' . $board->id)
                )
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-12">
            <div class="text-center">
                <x-filament::icon
                    icon="heroicon-o-view-columns"
                    class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600 mb-4"
                />
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    {{ __('kanban::kanban.notification.boards.No board created yet') }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    {{ __('kanban::kanban.notification.boards.Start by creating your first board to organize your tasks') }}
                </p>
                <x-filament::button
                    tag="a"
                    href="{{ \FilamentKanban\Filament\Pages\BoardCreatePage::getUrl() }}"
                    icon="heroicon-o-plus"
                    color="primary"
                    size="lg"
                >
                    {{ __('kanban::kanban.buttons.Create first board') }}
                </x-filament::button>
            </div>
        </div>
    @endif
</x-filament-panels::page>
