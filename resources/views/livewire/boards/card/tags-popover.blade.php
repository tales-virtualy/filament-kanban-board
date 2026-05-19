<div class="relative w-full" x-data="{ open: @entangle('open') }">
    <button type="button" @click="open = !open"
        class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
        <x-heroicon-o-tag class="w-4 h-4 shrink-0 text-gray-500" />
        <span>{{ __('kanban::kanban.tag.title') }}</span>
    </button>

    <div x-show="open" x-transition @click.outside="open = false" x-cloak
        class="absolute left-0 top-full mt-2 z-50 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-3">
        <h4 class="text-sm font-semibold mb-3 flex items-center gap-2">
            <x-heroicon-o-tag class="w-4 h-4" />
            {{ __('kanban::kanban.tag.title') }}
        </h4>

        <div class="space-y-2 max-h-60 overflow-y-auto">
            @foreach($availableTags as $tag)
                <label
                    class="flex items-center gap-2 p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition">
                    <input type="checkbox" wire:click="toggleTag({{ $tag->id }})" @checked(in_array($tag->id, $tags))
                        class="rounded border-gray-300 text-primary-600" />
                    <span class="px-2 py-0.5 rounded text-xs"
                        style="background-color: {{ $tag->badge_color }}20; color: {{ $tag->badge_color }}">
                        {{ $tag->name }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>
</div>