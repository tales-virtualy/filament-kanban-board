<div class="space-y-6">
    <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
        <x-heroicon-o-chat-bubble-left-right class="w-5 h-5" />
        <h4 class="font-semibold">{{ __('kanban::kanban.comment.title') }}</h4>
    </div>

    {{-- Input --}}
    <div class="flex gap-3 ml-6">
        <div class="flex-1 space-y-2">
            <textarea wire:model.defer="body" placeholder="{{ __('kanban::kanban.comment.placeholder') }}"
                class="w-full rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 text-sm focus:ring-primary-500 focus:border-primary-500"
                rows="2"></textarea>
            <x-filament::button size="sm" wire:click="addComment">
                {{ __('kanban::kanban.comment.post_button') }}
            </x-filament::button>
        </div>
    </div>

    {{-- Lista --}}
    <div class="space-y-4 ml-6">
        @foreach($comments as $comment)
            <div class="flex gap-3 group">
                <div class="flex-shrink-0">
                    <div
                        class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white text-xs font-bold">
                        {{ substr($comment->user->name, 0, 1) }}
                    </div>
                </div>
                <div class="flex-1">
                    <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg relative">
                        <div class="flex items-center justify-between mb-1">
                            <span
                                class="text-xs font-bold text-gray-900 dark:text-gray-200">{{ $comment->user->name }}</span>
                            <span class="text-[10px] text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $comment->body }}</div>

                        @if($comment->user_id === auth()->id())
                            <button wire:click="deleteComment({{ $comment->id }})"
                                class="absolute top-2 right-2 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition">
                                <x-heroicon-o-x-mark class="w-3 h-3" />
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>