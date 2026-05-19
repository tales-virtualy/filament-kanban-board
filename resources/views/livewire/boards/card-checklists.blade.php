<div class="space-y-4"
    x-data="{
        dragging: null,
        startChecklistDrag(event, checklistId) {
            this.dragging = { type: 'checklist', id: checklistId }
            event.dataTransfer.effectAllowed = 'move'
            event.dataTransfer.setData('text/plain', 'checklist:' + checklistId)
        },
        startItemDrag(event, itemId, checklistId) {
            this.dragging = { type: 'item', id: itemId, checklistId }
            event.dataTransfer.effectAllowed = 'move'
            event.dataTransfer.setData('text/plain', 'item:' + itemId)
            event.stopPropagation()
        },
        endDrag() {
            window.setTimeout(() => { this.dragging = null }, 0)
        },
        handleItemDragOver(event) {
            if (this.dragging?.type === 'item') {
                event.dataTransfer.dropEffect = 'move'
            }
        },
        handleChecklistDragOver(event) {
            if (this.dragging?.type === 'checklist') {
                event.dataTransfer.dropEffect = 'move'
            }
        },
        dropItem(event, checklistId, targetItemId = null) {
            if (this.dragging?.type !== 'item' || this.dragging.checklistId !== checklistId) {
                return
            }
            const container = event.currentTarget.closest('[data-checklist-items]')
            if (!container) {
                this.endDrag()
                return
            }
            const itemIds = Array.from(container.querySelectorAll('[data-checklist-item-id]'))
                .map((el) => Number(el.dataset.checklistItemId))
                .filter((id) => id !== this.dragging.id)
            let targetIndex = itemIds.length
            if (targetItemId !== null) {
                const pos = itemIds.indexOf(Number(targetItemId))
                if (pos !== -1) {
                    const rect = event.currentTarget.getBoundingClientRect()
                    const after = event.clientY > (rect.top + rect.height / 2)
                    targetIndex = pos + (after ? 1 : 0)
                }
            }
            this.$wire.moveChecklistItem(this.dragging.id, targetIndex)
            this.endDrag()
        },
        dropChecklist(event, targetChecklistId = null) {
            if (this.dragging?.type !== 'checklist') {
                return
            }
            const board = event.currentTarget.closest('[data-checklist-board]')
            if (!board) {
                this.endDrag()
                return
            }
            const checklistIds = Array.from(board.querySelectorAll('[data-checklist-id]'))
                .map((el) => Number(el.dataset.checklistId))
                .filter((id) => id !== this.dragging.id)
            let targetIndex = checklistIds.length
            if (targetChecklistId !== null) {
                const pos = checklistIds.indexOf(Number(targetChecklistId))
                if (pos !== -1) {
                    const rect = event.currentTarget.closest('[data-checklist-id]')?.getBoundingClientRect()
                        ?? event.currentTarget.getBoundingClientRect()
                    const after = event.clientY > (rect.top + rect.height / 2)
                    targetIndex = pos + (after ? 1 : 0)
                }
            }
            this.$wire.moveChecklist(this.dragging.id, targetIndex)
            this.endDrag()
        },
    }">
    <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
        <x-heroicon-o-check-circle class="w-5 h-5" />
        <h4 class="font-semibold">{{ __('kanban::kanban.checklist.title') }}</h4>
    </div>

    <div class="space-y-4" data-checklist-board
        x-on:dragover.prevent="handleChecklistDragOver($event)"
        x-on:drop.prevent="dropChecklist($event)">
        @foreach($checklists as $checklist)
            <div wire:key="checklist-{{ $checklist->id }}"
                data-checklist-id="{{ $checklist->id }}"
                x-on:dragover.prevent.stop="handleChecklistDragOver($event)"
                x-on:drop.prevent.stop="dropChecklist($event, {{ $checklist->id }})"
                x-bind:class="{ 'opacity-60': dragging?.type === 'checklist' && dragging.id === {{ $checklist->id }} }"
                class="space-y-3 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between group gap-2">
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                        <div class="cursor-grab active:cursor-grabbing flex-shrink-0"
                            draggable="true"
                            x-on:dragstart="startChecklistDrag($event, {{ $checklist->id }})"
                            x-on:dragend="endDrag()">
                            <x-heroicon-m-bars-3 class="w-4 h-4 text-gray-400" />
                        </div>

                        @if($editingChecklistId === $checklist->id)
                            <div class="flex items-center gap-2 w-full">
                                <input type="text" wire:model.defer="editingChecklistTitle"
                                    class="flex-1 text-sm rounded border-gray-300 dark:bg-gray-800 dark:border-gray-600 focus:ring-primary-500"
                                    wire:keydown.enter="updateChecklist">
                                <x-filament::button size="sm" color="success" wire:click="updateChecklist">
                                    <x-heroicon-m-check class="w-4 h-4" />
                                </x-filament::button>
                                <x-filament::button size="sm" color="gray" wire:click="cancelEditingChecklist">
                                    <x-heroicon-m-x-mark class="w-4 h-4" />
                                </x-filament::button>
                            </div>
                        @else
                            <h5 class="text-sm font-bold text-gray-800 dark:text-gray-200 truncate">
                                {{ $checklist->title }}
                            </h5>
                        @endif
                    </div>

                    @if($editingChecklistId !== $checklist->id)
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                            <button type="button" wire:click="startEditingChecklist({{ $checklist->id }})"
                                class="p-1 text-gray-400 hover:text-primary-500">
                                <x-heroicon-o-pencil class="w-4 h-4" />
                            </button>
                            <button type="button" wire:click="deleteChecklist({{ $checklist->id }})"
                                wire:confirm="{{ __('kanban::kanban.notification.checklists.confirm_delete') }}"
                                class="p-1 text-red-500 hover:text-red-700">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        </div>
                    @endif
                </div>

                @php $progress = $checklist->progress @endphp
                <div class="flex items-center gap-2">
                    <span class="text-[10px] text-gray-500 w-8">{{ $progress['percentage'] }}%</span>
                    <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-primary-600 transition-all" style="width: {{ $progress['percentage'] }}%"></div>
                    </div>
                </div>

                <div class="space-y-2"
                    data-checklist-items="{{ $checklist->id }}"
                    x-on:dragover.prevent="handleItemDragOver($event)"
                    x-on:drop.prevent.stop="dropItem($event, {{ $checklist->id }})">
                    @foreach($checklist->items as $item)
                        <div wire:key="checklist-item-{{ $item->id }}"
                            data-checklist-item-id="{{ $item->id }}"
                            class="flex items-start gap-2 group"
                            x-on:dragover.prevent="handleItemDragOver($event)"
                            x-on:drop.prevent.stop="dropItem($event, {{ $checklist->id }}, {{ $item->id }})"
                            x-bind:class="{ 'opacity-50': dragging?.type === 'item' && dragging.id === {{ $item->id }} }">

                            <div class="mt-0.5 cursor-grab active:cursor-grabbing flex-shrink-0"
                                draggable="true"
                                x-on:dragstart="startItemDrag($event, {{ $item->id }}, {{ $checklist->id }})"
                                x-on:dragend="endDrag()"
                                x-on:click.stop>
                                <x-heroicon-m-bars-3 class="w-4 h-4 text-gray-400" />
                            </div>

                            @if($editingItemId === $item->id)
                                <div class="flex-1 space-y-2">
                                    <input type="text" wire:model.defer="editingItemDescription"
                                        class="w-full text-sm rounded border-gray-300 dark:bg-gray-800 dark:border-gray-600 focus:ring-primary-500"
                                        wire:keydown.enter="updateItem">
                                    <div class="flex gap-2">
                                        <x-filament::button size="sm" wire:click="updateItem">
                                            {{ __('kanban::kanban.common.save') }}
                                        </x-filament::button>
                                        <x-filament::button size="sm" color="gray" wire:click="cancelEditingItem">
                                            {{ __('kanban::kanban.common.cancel') }}
                                        </x-filament::button>
                                    </div>
                                </div>
                            @else
                                <input type="checkbox" wire:change="toggleItem({{ $item->id }})" @checked($item->is_completed)
                                    class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500">

                                <span
                                    class="text-sm flex-1 {{ $item->is_completed ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $item->description }}
                                </span>

                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                    <button type="button" wire:click="startEditingItem({{ $item->id }})"
                                        class="text-gray-400 hover:text-primary-500">
                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                    </button>
                                    <button type="button" wire:click="deleteItem({{ $item->id }})"
                                        class="text-gray-400 hover:text-red-500">
                                        <x-heroicon-o-x-mark class="w-4 h-4" />
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    @if($addingItemToChecklistId === $checklist->id)
                        <div class="space-y-2 pt-2 ml-6">
                            <textarea wire:model.defer="newItemDescription"
                                placeholder="{{ __('kanban::kanban.checklist.item_placeholder') }}"
                                class="w-full text-sm rounded border-gray-300 dark:bg-gray-800 dark:border-gray-600"
                                rows="2"></textarea>
                            <div class="flex gap-2">
                                <x-filament::button size="sm"
                                    wire:click="addItem">{{ __('kanban::kanban.common.save') }}</x-filament::button>
                                <x-filament::button size="sm" color="gray"
                                    wire:click="cancelAddingItem">{{ __('kanban::kanban.common.cancel') }}</x-filament::button>
                            </div>
                        </div>
                    @else
                        <button type="button" wire:click="startAddingItem({{ $checklist->id }})"
                            class="text-xs text-gray-500 hover:text-primary-500 transition mt-2 ml-6">
                            + {{ __('kanban::kanban.buttons.Add item') }}
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
