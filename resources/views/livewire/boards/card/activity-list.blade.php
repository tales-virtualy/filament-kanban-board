<div class="space-y-4">
    @foreach($activities as $activity)
        <div
            class="relative pl-6 before:absolute before:left-[11px] before:top-2 before:bottom-0 before:w-0.5 before:bg-gray-200 dark:before:bg-gray-700">
            <div
                class="absolute left-0 top-1.5 w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 border-2 border-white dark:border-gray-900">
            </div>
            <div class="flex flex-col">
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    <span class="font-bold">{{ $activity->user->name }}</span>
                    {{ $activity->formatted_message }}
                </p>
                <span class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</span>
            </div>
        </div>
    @endforeach
</div>