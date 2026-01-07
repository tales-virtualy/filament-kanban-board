<div class="kanban-board-container">
    <div class="flex items-start gap-4 p-4 overflow-x-auto min-h-[calc(100vh-150px)]" wire:sortable="updateListOrder"
        wire:sortable-group="updateCardOrder">

        @foreach($board->lists()->active()->orderBy('order')->get() as $list)
            <div wire:key="list-{{ $list->id }}" wire:sortable.item="{{ $list->id }}"
                class="flex-shrink-0 w-80 bg-gray-100 dark:bg-gray-800 rounded-lg shadow-sm max-h-full flex flex-col">

                {{-- Header da Lista --}}
                <div class="p-3 flex items-center justify-between cursor-grab active:cursor-grabbing" wire:sortable.handle>
                    <h3 class="font-bold text-gray-700 dark:text-gray-200">{{ $list->name }}</h3>
                    <div class="flex items-center gap-1">
                        <span
                            class="text-xs bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded-full text-gray-600 dark:text-gray-400">
                            {{ $list->cards()->active()->count() }}
                        </span>

                        <x-filament::dropdown placement="bottom-end">
                            <x-slot name="trigger">
                                <button class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition">
                                    <x-heroicon-m-ellipsis-horizontal class="w-5 h-5 text-gray-500" />
                                </button>
                            </x-slot>

                            <x-filament::dropdown.list>
                                <x-filament::dropdown.list.item icon="heroicon-o-archive-box"
                                    wire:click="archiveList({{ $list->id }})"
                                    wire:confirm="{{ __('kanban::kanban.buttons.Confirm Archiving') }}">
                                    {{ __('kanban::kanban.buttons.Archive lists') }}
                                </x-filament::dropdown.list.item>
                            </x-filament::dropdown.list>
                        </x-filament::dropdown>
                    </div>
                </div>

                {{-- Cards Container --}}
                <div class="flex-1 overflow-y-auto p-2 space-y-2 min-h-[50px]"
                    wire:sortable-group.item-group="{{ $list->id }}">

                    @foreach($list->cards()->when(!$showArchivedCards, fn($q) => $q->active())->orderBy('order')->get() as $card)
                        <div wire:key="card-{{ $card->id }}" wire:sortable-group.item="{{ $card->id }}"
                            class="bg-white dark:bg-gray-900 p-3 rounded shadow-sm border border-gray-200 dark:border-gray-700 hover:border-primary-500 transition group relative cursor-pointer"
                            onclick="Livewire.dispatch('openModal', { component: 'kanban-card-form', arguments: { card: {{ $card->id }} } })">

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
                <div class="p-2">
                    <button
                        class="w-full text-left px-2 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition flex items-center gap-2"
                        onclick="Livewire.dispatch('openModal', { component: 'kanban-card-form', arguments: { listId: {{ $list->id }} } })">
                        <x-heroicon-m-plus class="w-4 h-4" />
                        {{ __('kanban::kanban.buttons.Add Card') }}
                    </button>
                </div>
            </div>
        @endforeach

        {{-- Nova Lista --}}
        <div class="flex-shrink-0 w-80">
            <template x-if="!showAddList">
                <button @click="showAddList = true; $nextTick(() => $refs.listName.focus())"
                    class="w-full p-3 text-left bg-white/50 hover:bg-white/80 dark:bg-gray-800/50 dark:hover:bg-gray-800/80 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 font-medium transition flex items-center gap-2">
                    <x-heroicon-m-plus class="w-5 h-5" />
                    {{ __('kanban::kanban.buttons.Create New List') }}
                </button>
            </template>
            <div x-show="showAddList" x-cloak class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                <input x-ref="listName" type="text"
                    wire:keydown.enter="addList($event.target.value); showAddList = false"
                    @keydown.escape="showAddList = false" @blur="showAddList = false"
                    placeholder="{{ __('kanban::kanban.List Name') }}"
                    class="w-full px-3 py-2 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md focus:ring-primary-500 focus:border-primary-500 mb-2">
                <div class="flex items-center gap-2">
                    <button @click="addList($refs.listName.value); showAddList = false"
                        class="px-3 py-1.5 bg-primary-600 text-white text-xs font-bold rounded hover:bg-primary-700 transition">
                        {{ __('kanban::kanban.buttons.Add Card') }}
                    </button>
                    <button @click="showAddList = false"
                        class="p-1.5 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <x-heroicon-m-x-mark class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('kanban', () => ({
            showAddList: false,
        }))
    })
</script>