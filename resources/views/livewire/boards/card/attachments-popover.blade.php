<div class="relative w-full" x-data="{ open: @entangle('open') }">
    <button type="button" @click="open = !open"
        class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
        <x-heroicon-o-paper-clip class="w-4 h-4 shrink-0 text-gray-500" />
        <span>{{ __('kanban::kanban.attachment.title') }}</span>
    </button>

    <div x-show="open" x-transition @click.outside="open = false" x-cloak
        class="absolute left-0 top-full mt-2 z-50 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center justify-between mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                <x-heroicon-o-paper-clip class="w-4 h-4" />
                {{ __('kanban::kanban.attachment.upload_title') }}
            </h4>
        </div>

        <div class="space-y-4">
            <label class="block w-full cursor-pointer group">
                <div
                    class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition group">
                    <input type="file" wire:model="file" class="hidden">
                    <x-heroicon-o-cloud-arrow-up
                        class="w-8 h-8 mx-auto text-gray-400 group-hover:text-primary-500 mb-2" />
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('kanban::kanban.attachment.drop_text') }}
                    </p>
                    <p class="text-[10px] text-gray-400 mt-1">{{ __('kanban::kanban.attachment.max_size') }}</p>
                </div>
            </label>

            @error('file')
                <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
            @enderror

            <div wire:loading wire:target="file" class="text-center">
                <span class="text-xs text-gray-500 flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                            fill="none"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    {{ __('kanban::kanban.attachment.uploading') }}
                </span>
            </div>
        </div>
    </div>
</div>