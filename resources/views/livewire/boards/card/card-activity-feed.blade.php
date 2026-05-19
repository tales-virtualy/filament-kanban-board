<div class="space-y-4">
    <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
        <x-heroicon-o-list-bullet class="w-5 h-5" />
        <h4 class="font-semibold {{ $compact ? 'text-base' : 'text-sm' }}">{{ __('kanban::kanban.activity.title') }}</h4>
    </div>

    <div
        class="relative {{ $compact ? 'pl-4 space-y-3.5' : 'pl-6 space-y-4' }} before:absolute before:left-[7px] before:top-2 before:bottom-0 before:w-0.5 before:bg-gray-200 dark:before:bg-gray-700">
        @forelse($activities as $activity)
            <div class="relative" wire:key="activity-{{ $activity->id }}">
                <div
                    class="absolute -left-[12px] top-1.5 w-2.5 h-2.5 rounded-full bg-gray-300 dark:bg-gray-600 border-2 border-white dark:border-gray-900 shadow-sm">
                </div>
                <div class="flex flex-col">
                    <p class="{{ $compact ? 'text-sm leading-relaxed' : 'text-xs leading-snug' }} text-gray-700 dark:text-gray-300">
                        <span class="font-bold">{{ $activity->user?->name ?? __('kanban::kanban.activity.system') }}</span>
                        {{ $activity->formatted_message }}
                    </p>
                    <span class="{{ $compact ? 'text-xs' : 'text-[10px]' }} text-gray-500">{{ $activity->created_at->diffForHumans() }}</span>
                </div>
            </div>
        @empty
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('kanban::kanban.activity.empty') }}</p>
        @endforelse
    </div>

    @if($hasMore)
        <button type="button" wire:click="toggleShowAll"
            class="text-xs text-primary-600 hover:text-primary-700 font-medium">
            {{ __('kanban::kanban.activity.show_all') }}
        </button>
    @endif
</div>
