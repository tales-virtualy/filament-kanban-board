<div class="relative" x-data="{ open: @entangle('open') }">
    <button type="button" @click="open = !open" x-tooltip="'{{ __('kanban::kanban.date.tooltip') }}'"
        class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-500">
        <x-heroicon-o-calendar class="w-5 h-5" />
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