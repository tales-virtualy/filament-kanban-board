<div class="flex-shrink-0 w-80 h-full flex flex-col bg-gray-100/50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700"
    wire:key="list-{{ $list->id }}">

    {{-- Header --}}
    <div class="p-4 flex items-center justify-between group">
        <div class="flex items-center gap-2 overflow-hidden">
            <h3 class="font-bold text-gray-900 dark:text-gray-100 truncate">
                {{ $list->name }}
            </h3>
            <span
                class="bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-[10px] font-bold px-2 py-0.5 rounded-full">
                {{ $list->cards_count }}
            </span>
        </div>

        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
            <button wire:click="archiveList({{ $list->id }})"
                wire:confirm="{{ __('kanban::kanban.notification.lists.confirm_archive') }}"
                class="p-1 text-gray-400 hover:text-red-500 rounded transition">
                <x-heroicon-o-archive-box class="w-4 h-4" />
            </button>
        </div>
    </div>

    {{-- Cards Container --}}
    <div class="flex-1 overflow-y-auto px-3 pb-3 space-y-3" wire:sortable-group.item-group="{{ $list->id }}"
        wire:sortable-group.options="{ animation: 150, ghostClass: 'opacity-50' }">

        @foreach($list->cards as $card)
            <div wire:key="card-{{ $card->id }}" wire:sortable-group.item="{{ $card->id }}"
                wire:click="openCard({{ $card->id }})"
                class="group bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-primary-500/50 cursor-pointer transition">

                <div class="space-y-3">
                    {{-- Tags --}}
                    @if($card->tags->count() > 0)
                        <div class="flex flex-wrap gap-1">
                            @foreach($card->tags as $tag)
                                <div class="h-1.5 w-8 rounded-full" style="background-color: {{ $tag->badge_color }}"
                                    title="{{ $tag->name }}"></div>
                            @endforeach
                        </div>
                    @endif

                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-200 leading-tight">
                        {{ $card->title }}
                    </h4>

                    {{-- Badges/Meta --}}
                    <div class="flex items-center gap-3 text-gray-400">
                        @if($card->description)
                            <x-heroicon-o-bars-3-bottom-left class="w-3.5 h-3.5"
                                title="{{ __('kanban::kanban.description.title') }}" />
                        @endif

                        @if($card->comments_count > 0)
                            <div class="flex items-center gap-1" title="{{ __('kanban::kanban.comment.title') }}">
                                <x-heroicon-o-chat-bubble-left-right class="w-3.5 h-3.5" />
                                <span class="text-[10px]">{{ $card->comments_count }}</span>
                            </div>
                        @endif

                        @if($card->attachments_count > 0)
                            <div class="flex items-center gap-1" title="{{ __('kanban::kanban.attachment.title') }}">
                                <x-heroicon-o-paper-clip class="w-3.5 h-3.5" />
                                <span class="text-[10px]">{{ $card->attachments_count }}</span>
                            </div>
                        @endif

                        @if($card->checklist_stats['total'] > 0)
                            <div class="flex items-center gap-1 {{ $card->checklist_stats['completed'] === $card->checklist_stats['total'] ? 'text-green-500' : '' }}"
                                title="{{ __('kanban::kanban.checklist.title') }}">
                                <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                                <span
                                    class="text-[10px]">{{ $card->checklist_stats['completed'] }}/{{ $card->checklist_stats['total'] }}</span>
                            </div>
                        @endif

                        @if($card->due_date)
                            <div class="flex items-center gap-1 ml-auto {{ $card->due_date->isPast() ? 'text-red-500' : '' }}"
                                title="{{ __('kanban::kanban.date.title') }}">
                                <x-heroicon-o-calendar class="w-3.5 h-3.5" />
                                <span class="text-[10px]">{{ $card->due_date->format('d M') }}</span>
                            </div>
                        @endif

                        @if($card->members->count() > 0)
                            <div class="flex -space-x-1.5 ml-auto">
                                @foreach($card->members->take(3) as $member)
                                    <div class="w-5 h-5 rounded-full border border-white dark:border-gray-800 bg-gray-200 overflow-hidden"
                                        title="{{ $member->name }}">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&size=20"
                                            alt="{{ $member->name }}">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Add Card Placeholder --}}
        <button wire:click="openCardCreate({{ $list->id }})"
            class="w-full py-2 px-3 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg text-sm text-gray-500 hover:border-primary-500 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition">
            + {{ __('kanban::kanban.buttons.Add card') }}
        </button>
    </div>
</div>