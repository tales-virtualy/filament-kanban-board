<div class="relative" x-data="{ open: @entangle('open') }">
    <button type="button" @click="open = !open" x-tooltip="'{{ __('kanban::kanban.member.title') }}'"
        class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-500">
        <x-heroicon-o-users class="w-5 h-5" />
    </button>

    <div x-show="open" x-transition @click.outside="open = false" x-cloak
        class="absolute left-0 top-full mt-2 z-50 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-3">
        <h4 class="text-sm font-semibold mb-3 flex items-center gap-2">
            <x-heroicon-o-users class="w-4 h-4" />
            {{ __('kanban::kanban.member.title') }}
        </h4>

        <div class="space-y-2 max-h-60 overflow-y-auto">
            @foreach($availableUsers as $user)
                <label
                    class="flex items-center gap-2 p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition">
                    <input type="checkbox" wire:click="toggleMember({{ $user->id }})" @checked(in_array($user->id, $members)) class="rounded border-gray-300 text-primary-600" />
                    <span class="text-sm">{{ $user->name }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>