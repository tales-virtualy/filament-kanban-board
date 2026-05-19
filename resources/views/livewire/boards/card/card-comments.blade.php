@php
    $listClass = $compact ? 'space-y-4' : 'space-y-4 ml-6';
    $inputWrapperClass = $compact ? 'space-y-3' : 'flex gap-3 ml-6';
    $headingClass = $compact ? 'font-semibold text-base' : 'font-semibold';
@endphp

<div class="space-y-4">
    <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
        <x-heroicon-o-chat-bubble-left-right class="w-5 h-5" />
        <h4 class="{{ $headingClass }}">{{ __('kanban::kanban.comment.title') }}</h4>
    </div>

    <div class="{{ $inputWrapperClass }}">
        <div class="flex-1 space-y-2">
            <textarea wire:model.defer="body" placeholder="{{ __('kanban::kanban.comment.placeholder') }}"
                class="w-full rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 text-sm leading-relaxed focus:ring-primary-500 focus:border-primary-500"
                rows="{{ $compact ? 3 : 2 }}"></textarea>
            @error('body')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
            <x-filament::button size="sm" wire:click="addComment">
                {{ __('kanban::kanban.comment.post_button') }}
            </x-filament::button>
        </div>
    </div>

    <div class="{{ $listClass }}">
        @forelse($comments as $comment)
            <div class="flex gap-3" wire:key="comment-{{ $comment->id }}">
                <div class="flex-shrink-0 pt-0.5">
                    <div
                        class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white text-xs font-bold">
                        {{ substr($comment->user->name, 0, 1) }}
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    @if($editingCommentId === $comment->id)
                        <div class="space-y-2">
                            <textarea wire:model.defer="editingBody" rows="3"
                                class="w-full text-sm leading-relaxed rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500"></textarea>
                            @error('editingBody')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <div class="flex gap-2">
                                <x-filament::button size="sm" wire:click="updateComment">
                                    {{ __('kanban::kanban.common.save') }}
                                </x-filament::button>
                                <x-filament::button size="sm" color="gray" wire:click="cancelEditingComment">
                                    {{ __('kanban::kanban.common.cancel') }}
                                </x-filament::button>
                            </div>
                        </div>
                    @else
                        <div
                            class="rounded-lg bg-gray-100 dark:bg-gray-800 px-3 py-2.5 group">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 leading-tight">
                                        {{ $comment->user->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $comment->created_at->diffForHumans() }}
                                    </p>
                                </div>

                                @if($comment->user_id === auth()->id() || $card->list->board->isAdmin(auth()->user()))
                                    <div class="flex shrink-0 items-center gap-0.5 opacity-60 group-hover:opacity-100 transition">
                                        <button type="button" wire:click="startEditingComment({{ $comment->id }})"
                                            class="p-1 rounded text-gray-500 hover:text-primary-500 hover:bg-gray-200/80 dark:hover:bg-gray-700"
                                            title="{{ __('kanban::kanban.comment.edit') }}">
                                            <x-heroicon-o-pencil class="w-4 h-4" />
                                        </button>
                                        <button type="button" wire:click="deleteComment({{ $comment->id }})"
                                            class="p-1 rounded text-gray-500 hover:text-red-500 hover:bg-gray-200/80 dark:hover:bg-gray-700"
                                            wire:confirm="{{ __('kanban::kanban.comment.confirm_delete') }}">
                                            <x-heroicon-o-x-mark class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <p class="mt-2 text-sm leading-relaxed text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words">
                                {{ $comment->body }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('kanban::kanban.comment.empty') }}</p>
        @endforelse
    </div>
</div>
