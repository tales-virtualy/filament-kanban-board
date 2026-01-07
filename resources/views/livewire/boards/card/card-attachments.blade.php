<div class="space-y-4">
    <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
        <x-heroicon-o-paper-clip class="w-5 h-5" />
        <h4 class="font-semibold">{{ __('kanban::kanban.attachment.title') }}</h4>
    </div>

    <div class="grid grid-cols-1 gap-3 ml-6">
        @foreach($attachments as $attachment)
            <div
                class="flex items-center gap-3 p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 group transition hover:shadow-sm">
                <div
                    class="w-12 h-12 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                    @if(str_contains($attachment->mime_type, 'image'))
                        <img src="{{ Storage::disk(config('kanban.storage_disk'))->url($attachment->file_path) }}"
                            class="w-full h-full object-cover">
                    @else
                        <x-heroicon-o-document class="w-6 h-6 text-gray-400" />
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-200 truncate"
                        title="{{ $attachment->file_name }}">
                        {{ $attachment->file_name }}
                    </p>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span>{{ $attachment->created_at->format('d M, Y') }}</span>
                        <span>•</span>
                        <a href="{{ Storage::disk(config('kanban.storage_disk'))->url($attachment->file_path) }}"
                            target="_blank" class="text-primary-600 hover:underline">
                            {{ __('kanban::kanban.attachment.download') }}
                        </a>
                    </div>
                </div>

                <button wire:click="deleteAttachment({{ $attachment->id }})"
                    wire:confirm="{{ __('kanban::kanban.notification.attachments.confirm_delete') }}"
                    class="p-2 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition">
                    <x-heroicon-o-trash class="w-4 h-4" />
                </button>
            </div>
        @endforeach
    </div>
</div>