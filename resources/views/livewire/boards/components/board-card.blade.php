<div wire:click="openBoard" class="group relative rounded-lg border overflow-hidden cursor-pointer transition-all duration-200
        {{ $board->isArchived()
    ? 'bg-gray-100 dark:bg-gray-900 border-gray-300 dark:border-gray-700 opacity-70'
    : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-primary-400 dark:hover:border-primary-600 hover:shadow-lg'
        }}
    ">
    @if(!$board->isArchived())
        <div
            class="absolute inset-0 bg-gradient-to-br from-primary-50/50 to-transparent dark:from-primary-950/30 dark:to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
        </div>
    @endif

    @if($board->isArchived())
        <span
            class="absolute top-3 right-3 z-10 inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-md bg-yellow-900/30 text-yellow-400">
            <x-filament::icon icon="heroicon-o-archive-box" class="h-3 w-3" />
            {{ __('kanban::kanban.common.archived') }}
        </span>
    @endif

    <div class="relative p-5">
        <div class="flex items-start justify-between mb-3">
            <h3 class="text-lg font-semibold line-clamp-2 transition-colors
                    {{ $board->isArchived()
    ? 'text-gray-500 dark:text-gray-400'
    : 'text-gray-900 dark:text-gray-100 group-hover:text-primary-600 dark:group-hover:text-primary-400'
                    }}">
                {{ $board->name }}
            </h3>

            @if($board->is_private)
                <x-filament::icon icon="heroicon-o-lock-closed"
                    class="h-5 w-5 text-gray-400 dark:text-gray-600 flex-shrink-0 ml-2" />
            @endif
        </div>

        @if($board->description)
            <p
                class="text-sm line-clamp-2 mb-4 {{ $board->isArchived() ? 'text-gray-500' : 'text-gray-600 dark:text-gray-400' }}">
                {{ $board->description }}
            </p>
        @else
            <p class="text-sm italic mb-4 text-gray-400 dark:text-gray-600">
                {{ __('kanban::kanban.description.empty') }}
            </p>
        @endif

        <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <div class="flex items-center gap-1">
                    <x-heroicon-o-queue-list class="h-4 w-4" />
                    <span>{{ $board->lists->count() }} {{ __('kanban::kanban.title_lists') }}</span>
                </div>
            </div>

            <x-heroicon-o-arrow-right
                class="h-5 w-5 transition-all {{ $board->isArchived() ? 'text-gray-400' : 'text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 group-hover:translate-x-1' }}" />
        </div>
    </div>
</div>