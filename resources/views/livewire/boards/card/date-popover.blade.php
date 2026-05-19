<div class="relative w-full" x-data="{ open: @entangle('open') }">
    <button type="button" @click="open = !open"
        class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
        <x-heroicon-o-calendar class="w-4 h-4 shrink-0 text-gray-500" />
        <span>{{ __('kanban::kanban.date.title') }}</span>
    </button>

    <div x-show="open" x-transition @click.outside="open = false" x-cloak
        class="absolute left-0 top-full mt-2 z-50 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-3">
        <input type="date" wire:model.defer="due_date"
            class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" />
        <div class="flex justify-end gap-2 mt-3">
            <button wire:click="clear"
                class="text-sm text-gray-500 hover:text-gray-700">{{ __('kanban::kanban.common.clear') }}</button>
            <x-filament::button size="sm" wire:click="save">{{ __('kanban::kanban.common.save') }}</x-filament::button>
        </div>
    </div>
</div>