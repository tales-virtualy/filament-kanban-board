<x-filament-panels::page>
    <div class="max-w-2xl mx-auto">
        <div
            class="mb-6 p-4 bg-primary-50 dark:bg-primary-950 rounded-lg border border-primary-200 dark:border-primary-800">
            <div class="flex items-start gap-3">
                <x-filament::icon icon="heroicon-o-light-bulb"
                    class="h-6 w-6 text-primary-600 dark:text-primary-400 flex-shrink-0 mt-0.5" />
                <div>
                    <h3 class="text-sm font-semibold text-primary-900 dark:text-primary-100 mb-1">
                        {{ __('kanban::kanban.common.hint') }}
                    </h3>
                    <p class="text-sm text-primary-700 dark:text-primary-300">
                        {{ __('kanban::kanban.notification.boards.create_hint') }}
                    </p>
                </div>
            </div>
        </div>

        <form wire:submit="create">
            {{ $this->getForm('form') }}

            <div class="flex items-center justify-end gap-3 mt-6">
                {{ $this->getCancelFormAction() }}
                {{ $this->getFormActions()[0] }}
            </div>
        </form>
    </div>
</x-filament-panels::page>