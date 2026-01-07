<x-filament-panels::page>
    <div class="mb-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <x-filament::button icon="heroicon-o-arrow-left" outlined tag="a"
                href="{{ \FilamentKanban\Filament\Pages\BoardListPage::getUrl() }}">
                {{ __('kanban::kanban.common.back') }}
            </x-filament::button>

            @if(!$board->isArchived() && $board->lists()->whereNotNull('archived_at')->exists())
                <x-filament::button color="gray" size="sm" outlined x-data="{
                            showArchivedLists: false,
                            labelShow: '{{ __('kanban::kanban.buttons.Show archived') }}',
                            labelHide: '{{ __('kanban::kanban.buttons.Hide archived') }}'
                        }"
                    x-on:click="showArchivedLists = !showArchivedLists; $dispatch('toggle-archived-lists', { show: showArchivedLists })">
                    <x-filament::icon x-bind:icon="showArchivedLists ? 'heroicon-o-eye-slash' : 'heroicon-o-eye'"
                        class="w-5 h-5" />
                    <span x-text="showArchivedLists ? labelHide : labelShow"></span>
                </x-filament::button>
            @endif
        </div>
    </div>

    @livewire(\FilamentKanban\Livewire\Boards\BoardView::class, ['board' => $board], key('board-view-' . $board->id))
</x-filament-panels::page>