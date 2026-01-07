<div class="space-y-4">
    <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
        <x-heroicon-o-check-circle class="w-5 h-5" />
        <h4 class="font-semibold">{{ __('kanban::kanban.checklist.title') }}</h4>
    </div>

    @foreach($checklists as $checklist)
        <div class="space-y-3 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between group">
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
                    <h5 class="text-sm font-bold text-gray-800 dark:text-gray-200 cursor-pointer"
                        wire:click="startEditingChecklist({{ $checklist->id }})">
                        {{ $checklist->title }}
                    </h5>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                        <button wire:click="deleteChecklist({{ $checklist->id }})"
                            wire:confirm="{{ __('kanban::kanban.notification.checklists.confirm_delete') }}"
                            class="text-red-500 hover:text-red-700">
                            <x-heroicon-o-trash class="w-4 h-4" />
                        </button>
                    </div>
                @endif
            </div>

            {{-- Barra de Progresso --}}
            @php $progress = $checklist->progress @endphp
            <div class="flex items-center gap-2">
                <span class="text-[10px] text-gray-500 w-8">{{ $progress['percentage'] }}%</span>
                <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-primary-600 transition-all" style="width: {{ $progress['percentage'] }}%"></div>
                </div>
            </div>

            {{-- Itens --}}
            <div class="space-y-2">
                @foreach($checklist->items as $item)
                    <div class="flex items-start gap-2 group">
                        <input type="checkbox" wire:click="toggleItem({{ $item->id }})" @checked($item->is_completed)
                            class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500">

                        <span
                            class="text-sm flex-1 {{ $item->is_completed ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-300' }}">
                            {{ $item->description }}
                        </span>

                        <button wire:click="deleteItem({{ $item->id }})"
                            class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 transition">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </button>
                    </div>
                @endforeach

                @if($addingItemToChecklistId === $checklist->id)
                    <div class="space-y-2 pt-2">
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
                    <button wire:click="startAddingItem({{ $checklist->id }})"
                        class="text-xs text-gray-500 hover:text-primary-500 transition mt-2 ml-6">
                        + {{ __('kanban::kanban.buttons.Add item') }}
                    </button>
                @endif
            </div>
        </div>
    @endforeach
</div>