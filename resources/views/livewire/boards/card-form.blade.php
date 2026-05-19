<div class="kanban-card-form">
    <style>
        .kanban-card-form {
            display: flex;
            flex-direction: column;
            max-height: min(85vh, 56rem);
        }

        .kanban-card-form__body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .kanban-card-form__layout {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .kanban-card-form__layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(26rem, 1.05fr);
                align-items: start;
                gap: 2.5rem;
            }
        }

        .kanban-card-form__footer {
            flex-shrink: 0;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border-top: 1px solid rgb(229 231 235);
            background-color: rgb(255 255 255);
        }

        .dark .kanban-card-form__footer {
            border-top-color: rgb(55 65 81);
            background-color: rgb(17 24 39);
        }

        /* Evita scroll duplo no modal Filament: só o corpo do formulário rola. */
        .fi-modal-window:has(.kanban-card-form) {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .fi-modal-window:has(.kanban-card-form) .fi-modal-content {
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
            padding: 0;
        }

        .fi-modal-window:has(.kanban-card-form) .kanban-card-form {
            max-height: 100%;
            height: 100%;
        }
    </style>

    <div class="kanban-card-form__body">
        <div class="space-y-6">
            {{-- Título --}}
            <div>
                <input type="text" wire:model.defer="title"
                    class="w-full text-2xl font-bold border-none focus:ring-0 bg-transparent dark:text-white"
                    placeholder="{{ __('kanban::kanban.Card Title') }}">
                @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="kanban-card-form__layout">
                {{-- Conteúdo Principal --}}
                <div class="space-y-6 min-w-0">
                    {{-- Membros e Tags --}}
                    <div class="flex flex-wrap gap-4">
                        @if($card && $card->members->count() > 0)
                            <div>
                                <h5 class="text-xs font-semibold text-gray-500 uppercase mb-2">
                                    {{ __('kanban::kanban.member.title') }}</h5>
                                <div class="flex -space-x-2">
                                    @foreach($card->members as $member)
                                        <div class="w-8 h-8 rounded-full border-2 border-white dark:border-gray-900 bg-gray-200 overflow-hidden"
                                            title="{{ $member->name }}">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&color=7F9CF5&background=EBF4FF"
                                                alt="{{ $member->name }}">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($card && $card->tags->count() > 0)
                            <div>
                                <h5 class="text-xs font-semibold text-gray-500 uppercase mb-2">
                                    {{ __('kanban::kanban.tag.title') }}</h5>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($card->tags as $tag)
                                        <span class="px-2 py-1 rounded text-xs font-medium"
                                            style="background-color: {{ $tag->badge_color }}; color: {{ $tag->text_color }}">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($card && $card->due_date)
                            <div>
                                <h5 class="text-xs font-semibold text-gray-500 uppercase mb-2">
                                    {{ __('kanban::kanban.date.title') }}</h5>
                                <div
                                    class="inline-flex items-center gap-1.5 rounded px-2 py-1 text-sm font-medium {{ $card->due_date_status }}">
                                    <x-heroicon-o-calendar class="w-4 h-4" />
                                    <span>{{ $card->due_date->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Descrição --}}
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                            <x-heroicon-o-bars-3-bottom-left class="w-5 h-5" />
                            <h4 class="font-semibold">{{ __('kanban::kanban.description.title') }}</h4>
                        </div>
                        <textarea wire:model.defer="description" rows="4"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="{{ __('kanban::kanban.Describe the purpose of this board') }}"></textarea>
                    </div>

                    {{-- Anexos --}}
                    @if($card && $card->attachments->isNotEmpty())
                        @livewire(
                            'kanban-card-attachments',
                            ['card' => $card],
                            key('card-attachments-' . $card->id . '-' . $cardContentKey)
                        )
                    @endif

                    {{-- Checklists --}}
                    @if($card)
                        @livewire('kanban-card-checklists', ['card' => $card], key('checklists-' . $card->id))
                    @endif

                </div>

                {{-- Sidebar de Ações --}}
                <div class="kanban-card-form__sidebar space-y-5">
                    <h5 class="text-xs font-semibold text-gray-500 uppercase">{{ __('kanban::kanban.buttons.Add to card') }}
                    </h5>
                    <div class="grid grid-cols-1 gap-2">
                        @if($card)
                            @livewire('kanban-members-popover', ['card' => $card], key('members-' . $card->id))
                            @livewire('kanban-tags-popover', ['card' => $card], key('tags-popover-' . $card->id))
                            @livewire('kanban-date-popover', ['card' => $card], key('date-' . $card->id))
                            @livewire('kanban-attachments-popover', ['card' => $card], key('attachments-popover-' . $card->id))

                            <button wire:click="createChecklist"
                                class="flex items-center gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition text-sm">
                                <x-heroicon-o-check-circle class="w-4 h-4" />
                                {{ __('kanban::kanban.buttons.Add Checklist') }}
                            </button>
                        @endif
                    </div>

                    @if($card)
                        <h5 class="text-xs font-semibold text-gray-500 uppercase mt-6">
                            {{ __('kanban::kanban.buttons.Actions') }}</h5>
                        <div class="grid grid-cols-1 gap-2">
                            <x-filament::dropdown placement="bottom-start">
                                <x-slot name="trigger">
                                    <button
                                        class="flex items-center gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition text-sm w-full">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" />
                                        {{ __('kanban::kanban.buttons.Move') }}
                                    </button>
                                </x-slot>
                                <x-filament::dropdown.list>
                                    @foreach($currentBoardLists as $list)
                                        <x-filament::dropdown.list.item wire:click="moveCard({{ $list->id }})"
                                            :color="$list->id === $listId ? 'primary' : 'gray'">
                                            {{ $list->name }}
                                        </x-filament::dropdown.list.item>
                                    @endforeach
                                </x-filament::dropdown.list>
                            </x-filament::dropdown>

                            @if($card->isArchived())
                                <button wire:click="unarchiveCard"
                                    class="flex items-center gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-800 text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-950 rounded transition text-sm">
                                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                                    {{ __('kanban::kanban.buttons.Unarchive card') }}
                                </button>
                            @else
                                <button wire:click="archiveCard"
                                    class="flex items-center gap-2 px-3 py-2 {{ $confirmingArchive ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-red-600' }} hover:bg-red-700 hover:text-white rounded transition text-sm">
                                    <x-heroicon-o-archive-box class="w-4 h-4" />
                                    {{ $confirmingArchive ? __('kanban::kanban.buttons.Confirm Archiving') : __('kanban::kanban.buttons.Archive card') }}
                                </button>
                            @endif
                        </div>

                        <div class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                            @livewire(
                                'kanban-card-comments',
                                ['card' => $card, 'compact' => true],
                                key('card-comments-' . $card->id . '-' . $cardContentKey)
                            )

                            @livewire(
                                'kanban-card-activity-feed',
                                ['card' => $card, 'compact' => true],
                                key('card-activity-' . $card->id . '-' . $cardContentKey)
                            )
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Footer fixo --}}
    <div class="kanban-card-form__footer">
        <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'card-modal' })">
            {{ __('kanban::kanban.common.cancel') }}
        </x-filament::button>
        <x-filament::button color="primary" wire:click="save">
            {{ __('kanban::kanban.common.save') }}
        </x-filament::button>
    </div>
</div>
