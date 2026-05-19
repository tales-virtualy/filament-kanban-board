<x-filament-panels::page>
    @if ($this->accessDenied)
        <div class="mx-auto flex max-w-lg flex-col items-center px-4 py-16 text-center">
            <div
                class="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                <x-filament::icon icon="heroicon-o-lock-closed" class="h-8 w-8 text-gray-500 dark:text-gray-400" />
            </div>

            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                {{ __('kanban::kanban.board.access_denied_title') }}
            </h2>

            <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                {{ __('kanban::kanban.board.access_denied_message', ['name' => $board->name]) }}
            </p>

            <x-filament::button
                tag="a"
                class="mt-8"
                color="gray"
                href="{{ \FilamentKanban\Filament\Pages\BoardListPage::getUrl() }}"
                icon="heroicon-o-arrow-left"
            >
                {{ __('kanban::kanban.board.back_to_boards') }}
            </x-filament::button>
        </div>
    @else
        @php
            $participants = $board->participants();
            $canManageMembers = auth()->user()?->can('update', $board) ?? false;
        @endphp

        @if ($participants->isNotEmpty())
            <div class="mb-4 flex flex-wrap items-center gap-3">
                @if ($canManageMembers)
                    <button
                        type="button"
                        wire:click="mountAction('members')"
                        class="group flex items-center gap-3 rounded-lg px-1 py-1 transition hover:bg-gray-50 dark:hover:bg-white/5"
                        title="{{ __('kanban::kanban.member.manage') }}"
                    >
                        <x-kanban::user-avatars :users="$participants" max="6" size="md" />
                        <span class="text-sm text-gray-500 transition group-hover:text-primary-600 dark:text-gray-400 dark:group-hover:text-primary-400">
                            {{ trans_choice('kanban::kanban.member.count', $participants->count(), ['count' => $participants->count()]) }}
                        </span>
                    </button>
                @else
                    <div class="flex items-center gap-3" title="{{ trans_choice('kanban::kanban.member.count', $participants->count(), ['count' => $participants->count()]) }}">
                        <x-kanban::user-avatars :users="$participants" max="6" size="md" />
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ trans_choice('kanban::kanban.member.count', $participants->count(), ['count' => $participants->count()]) }}
                        </span>
                    </div>
                @endif
            </div>
        @endif

        @livewire(\FilamentKanban\Livewire\Boards\BoardView::class, ['board' => $board], key('board-view-' . $board->id))
    @endif
</x-filament-panels::page>
