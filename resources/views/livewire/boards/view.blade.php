<div class="kanban-board-container" x-data="kanban">
    <div class="mb-6 flex items-center justify-between gap-4 px-4">
        <div class="flex items-center gap-3">
            <x-filament::button icon="heroicon-o-arrow-left" outlined tag="a"
                href="{{ \FilamentKanban\Filament\Pages\BoardListPage::getUrl() }}">
                {{ __('kanban::kanban.common.back') }}
            </x-filament::button>

            @if(!$board->isArchived() && $board->lists()->archived()->exists())
                <x-filament::button color="gray" size="sm" outlined wire:click="toggleArchivedLists">
                    {{ $showArchivedLists ? __('kanban::kanban.buttons.Hide archived lists') : __('kanban::kanban.buttons.Show archived lists') }}
                </x-filament::button>
            @endif

            @if(!$board->isArchived() && $board->hasArchivedCardsInActiveLists())
                <x-filament::button color="gray" size="sm" outlined wire:click="toggleArchivedCards">
                    {{ $showArchivedCards ? __('kanban::kanban.buttons.Hide archived cards') : __('kanban::kanban.buttons.Show archived cards') }}
                </x-filament::button>
            @endif
        </div>
    </div>

    <div class="flex items-start gap-4 p-4 overflow-x-auto min-h-[calc(100vh-150px)]"
        data-kanban-lists
        x-on:dragover.prevent="handleBoardDragOver($event)"
        x-on:drop.prevent="dropList($event)">

        @foreach($lists as $list)
            <div wire:key="list-{{ $list->id }}"
                data-list-id="{{ $list->id }}"
                @unless($list->isArchived())
                    x-on:dragover.prevent.stop="handleListDragOver($event)"
                    x-on:drop.prevent.stop="dropList($event, {{ $list->id }})"
                @endunless
                x-bind:class="{ 'opacity-60': dragging?.type === 'list' && dragging.id === {{ $list->id }} }"
                class="flex-shrink-0 w-80 bg-gray-100 dark:bg-gray-800 rounded-lg shadow-sm max-h-full flex flex-col transition-opacity">

                {{-- Header da Lista --}}
                <div class="p-3 flex items-center justify-between">
                    <div class="flex items-center gap-2 {{ $list->isArchived() ? '' : 'cursor-grab active:cursor-grabbing' }}"
                        draggable="{{ $list->isArchived() ? 'false' : 'true' }}"
                        @unless($list->isArchived())
                            x-on:dragstart="startListDrag($event, {{ $list->id }})"
                            x-on:dragend="endDrag()"
                        @endunless>
                        <x-heroicon-m-bars-3 class="w-4 h-4 text-gray-400" />
                        <h3 class="font-bold text-gray-700 dark:text-gray-200">{{ $list->name }}</h3>
                    </div>
                    <div class="flex items-center gap-1">
                        <span
                            class="text-xs bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded-full text-gray-600 dark:text-gray-400">
                            {{ $list->isArchived() ? $list->cards()->archived()->count() : $list->cards()->active()->count() }}
                        </span>

                        <x-filament::dropdown placement="bottom-end">
                            <x-slot name="trigger">
                                <button class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition">
                                    <x-heroicon-m-ellipsis-horizontal class="w-5 h-5 text-gray-500" />
                                </button>
                            </x-slot>

                            <x-filament::dropdown.list>
                                @if($list->isArchived())
                                    <x-filament::dropdown.list.item icon="heroicon-o-arrow-path"
                                        wire:click="unarchiveList({{ $list->id }})">
                                        {{ __('kanban::kanban.common.unarchive') }}
                                    </x-filament::dropdown.list.item>
                                @else
                                    <x-filament::dropdown.list.item icon="heroicon-o-archive-box"
                                        wire:click="archiveList({{ $list->id }})"
                                        wire:confirm="{{ __('kanban::kanban.buttons.Confirm Archiving') }}">
                                        {{ __('kanban::kanban.buttons.Archive list') }}
                                    </x-filament::dropdown.list.item>
                                @endif
                            </x-filament::dropdown.list>
                        </x-filament::dropdown>
                    </div>
                </div>

                {{-- Cards Container --}}
                <div class="flex-1 overflow-y-auto p-2 space-y-2 min-h-[50px]"
                    data-card-container="{{ $list->id }}"
                    @unless($list->isArchived())
                        x-on:dragover.prevent="handleCardDragOver($event)"
                        x-on:drop.prevent.stop="dropCard($event, {{ $list->id }})"
                    @endunless>

                    @foreach($this->getCardsForList($list) as $card)
                        <div wire:key="card-{{ $card->id }}"
                            data-card-id="{{ $card->id }}"
                            draggable="{{ ($list->isArchived() || $card->isArchived()) ? 'false' : 'true' }}"
                            @unless($list->isArchived() || $card->isArchived())
                                x-on:dragstart="startCardDrag($event, {{ $card->id }}, {{ $list->id }})"
                                x-on:dragend="endDrag()"
                                x-on:dragover.prevent="handleCardDragOver($event)"
                                x-on:drop.prevent.stop="dropCard($event, {{ $list->id }}, {{ $card->id }})"
                            @endunless
                            x-on:click="handleCardClick({{ $card->id }})"
                            x-bind:class="{ 'opacity-50': dragging?.type === 'card' && dragging.id === {{ $card->id }} }"
                            @class([
                                'bg-white dark:bg-gray-900 p-3 rounded shadow-sm border transition group relative cursor-pointer',
                                'border-gray-200 dark:border-gray-700 hover:border-primary-500' => !$card->isArchived(),
                                'border-dashed border-gray-300 dark:border-gray-600 opacity-70' => $card->isArchived(),
                            ])
                        >
                            @if($card->isArchived())
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">
                                        {{ __('kanban::kanban.common.archived') }}
                                    </span>
                                    <button
                                        type="button"
                                        wire:click.stop="unarchiveCard({{ $card->id }})"
                                        class="text-[10px] font-medium text-primary-600 hover:underline"
                                    >
                                        {{ __('kanban::kanban.common.unarchive') }}
                                    </button>
                                </div>
                            @endif

                            {{-- Tags --}}
                            <div class="flex flex-wrap gap-1 mb-2">
                                @foreach($card->tags as $tag)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium"
                                        style="background-color: {{ $tag->badge_color }}; color: {{ $tag->text_color }}">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>

                            <h4 class="text-sm font-medium text-gray-800 dark:text-gray-100 mb-2 leading-tight">
                                {{ $card->title }}
                            </h4>

                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <div class="flex items-center gap-2">
                                    @if($card->description)
                                        <x-heroicon-o-bars-3-bottom-left class="w-4 h-4" />
                                    @endif

                                    @if($card->checklists()->count() > 0)
                                        @php $progress = $card->checklist_progress @endphp
                                        <div
                                            class="flex items-center gap-1 {{ $progress['percentage'] == 100 ? 'text-green-600' : '' }}">
                                            <x-heroicon-o-check-circle class="w-4 h-4" />
                                            <span>{{ $progress['completed'] }}/{{ $progress['total'] }}</span>
                                        </div>
                                    @endif

                                    @if($card->attachments()->count() > 0)
                                        <div class="flex items-center gap-1">
                                            <x-heroicon-o-paper-clip class="w-4 h-4" />
                                            <span>{{ $card->attachments()->count() }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex -space-x-2">
                                    @foreach($card->members as $member)
                                        <div class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-900 bg-gray-200 overflow-hidden"
                                            title="{{ $member->name }}">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&color=7F9CF5&background=EBF4FF"
                                                alt="{{ $member->name }}">
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if($card->due_date)
                                <div
                                    class="mt-2 text-[10px] px-1.5 py-0.5 rounded inline-flex items-center gap-1 {{ $card->due_date_status }}">
                                    <x-heroicon-o-clock class="w-3 h-3" />
                                    <span>{{ $card->due_date->format('d/m/Y') }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Footer da Lista --}}
                @unless($list->isArchived())
                    <div class="p-2">
                        <button type="button"
                            wire:click="openCreateCardModal({{ $list->id }})"
                            class="w-full text-left px-2 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition flex items-center gap-2">
                            <x-heroicon-m-plus class="w-4 h-4" />
                            {{ __('kanban::kanban.buttons.Add Card') }}
                        </button>
                    </div>
                @endunless
            </div>
        @endforeach

        {{-- Nova Lista --}}
        <div class="flex-shrink-0 w-80">
            <template x-if="!showAddList">
                <button @click="showAddList = true; newListName = ''; $nextTick(() => $refs.listName.focus())"
                    class="w-full p-3 text-left bg-white/50 hover:bg-white/80 dark:bg-gray-800/50 dark:hover:bg-gray-800/80 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 font-medium transition flex items-center gap-2">
                    <x-heroicon-m-plus class="w-5 h-5" />
                    {{ __('kanban::kanban.buttons.Create New List') }}
                </button>
            </template>
            <div x-show="showAddList" x-cloak @click.outside="newListName = ''; showAddList = false"
                class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                <input x-ref="listName" x-model="newListName" type="text"
                    @keydown.enter.prevent="if (newListName.trim()) { $wire.addList(newListName.trim()); newListName = ''; showAddList = false }"
                    @keydown.escape="newListName = ''; showAddList = false"
                    placeholder="{{ __('kanban::kanban.List Name') }}"
                    class="w-full px-3 py-2 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md focus:ring-primary-500 focus:border-primary-500 mb-2">
                <div class="flex items-center gap-2">
                    <button @click="if (newListName.trim()) { $wire.addList(newListName.trim()); newListName = ''; showAddList = false }"
                        class="px-3 py-1.5 bg-primary-600 text-white text-xs font-bold rounded hover:bg-primary-700 transition">
                        {{ __('kanban::kanban.buttons.Create New List') }}
                    </button>
                    <button @click="newListName = ''; showAddList = false"
                        class="p-1.5 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <x-heroicon-m-x-mark class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <x-filament::modal id="card-modal" width="6xl">
        @if($activeCardId || $activeListId)
            @livewire(
                'kanban-card-form',
                ['cardId' => $activeCardId, 'listId' => $activeListId],
                key('card-modal-' . ($activeCardId ?? 'new') . '-' . ($activeListId ?? 'none') . '-' . $cardModalIteration)
            )
        @endif
    </x-filament::modal>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('kanban', () => ({
            showAddList: false,
            newListName: '',
            dragging: null,
            suppressCardClick: false,

            startListDrag(event, listId) {
                this.dragging = { type: 'list', id: listId }
                this.suppressCardClick = true
                event.dataTransfer.effectAllowed = 'move'
                event.dataTransfer.setData('text/plain', `list:${listId}`)
            },

            startCardDrag(event, cardId, listId) {
                this.dragging = { type: 'card', id: cardId, sourceListId: listId }
                this.suppressCardClick = true
                event.dataTransfer.effectAllowed = 'move'
                event.dataTransfer.setData('text/plain', `card:${cardId}`)
            },

            endDrag() {
                window.setTimeout(() => {
                    this.dragging = null
                    this.suppressCardClick = false
                }, 0)
            },

            handleCardClick(cardId) {
                if (this.suppressCardClick || this.dragging) {
                    return
                }

                this.$wire.openEditCardModal(cardId)
            },

            handleBoardDragOver(event) {
                if (this.dragging?.type !== 'list') {
                    return
                }

                event.dataTransfer.dropEffect = 'move'
            },

            handleListDragOver(event) {
                if (this.dragging?.type !== 'list') {
                    return
                }

                event.dataTransfer.dropEffect = 'move'
            },

            dropList(event, targetListId = null) {
                if (this.dragging?.type !== 'list') {
                    return
                }

                const listIds = Array.from(document.querySelectorAll('[data-list-id]'))
                    .map((element) => Number(element.dataset.listId))
                    .filter((id) => id !== this.dragging.id)

                let targetIndex = listIds.length

                if (targetListId !== null) {
                    const targetPosition = listIds.indexOf(Number(targetListId))
                    if (targetPosition !== -1) {
                        const targetElement = event.currentTarget
                        const targetRect = targetElement.getBoundingClientRect()
                        const shouldInsertAfter = event.clientX > (targetRect.left + (targetRect.width / 2))

                        targetIndex = targetPosition + (shouldInsertAfter ? 1 : 0)
                    }
                }

                this.$wire.moveList(this.dragging.id, targetIndex)
                this.endDrag()
            },

            handleCardDragOver(event) {
                if (this.dragging?.type !== 'card') {
                    return
                }

                event.dataTransfer.dropEffect = 'move'
            },

            dropCard(event, targetListId, targetCardId = null) {
                if (this.dragging?.type !== 'card') {
                    return
                }

                const container = document.querySelector(`[data-card-container="${targetListId}"]`)

                if (!container) {
                    this.endDrag()
                    return
                }

                const cardIds = Array.from(container.querySelectorAll('[data-card-id]'))
                    .map((element) => Number(element.dataset.cardId))
                    .filter((id) => id !== this.dragging.id)

                let targetIndex = cardIds.length

                if (targetCardId !== null) {
                    const targetPosition = cardIds.indexOf(Number(targetCardId))
                    if (targetPosition !== -1) {
                        const targetElement = event.currentTarget
                        const targetRect = targetElement.getBoundingClientRect()
                        const shouldInsertAfter = event.clientY > (targetRect.top + (targetRect.height / 2))

                        targetIndex = targetPosition + (shouldInsertAfter ? 1 : 0)
                    }
                }

                this.$wire.moveCard(this.dragging.id, Number(targetListId), targetIndex)
                this.endDrag()
            },
        }))
    })
</script>
