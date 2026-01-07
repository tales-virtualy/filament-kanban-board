<div class="space-y-4">
    <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
        <x-heroicon-o-list-bullet class="w-5 h-5" />
        <h4 class="font-semibold">{{ __('kanban::kanban.activity.title') }}</h4>
    </div>

    <div
        class="relative pl-6 space-y-4 before:absolute before:left-[11px] before:top-2 before:bottom-0 before:w-0.5 before:bg-gray-200 dark:before:bg-gray-700">
        @foreach($activities as $activity)
            <div class="relative">
                <div
                    class="absolute -left-[20px] top-1.5 w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 border-2 border-white dark:border-gray-900 shadow-sm">
                </div>
                <div class="flex flex-col">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        <span class="font-bold">{{ $activity->user->name }}</span>
                        {{ $activity->formatted_message }}
                    </p>
                    <span class="text-[10px] text-gray-500">{{ $activity->created_at->diffForHumans() }}</span>
                </div>
            </div>
        @endforeach
    </div>

    @if($hasMore)
        <button wire:click="toggleShowAll" class="text-xs text-primary-600 hover:text-primary-700 font-medium ml-6">
            {{ __('kanban::kanban.activity.show_all') }}
        </button>
    @endif
</div>