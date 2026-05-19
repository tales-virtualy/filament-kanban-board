<?php

namespace FilamentKanban\Livewire\Boards\Card;

use FilamentKanban\Models\Card;
use Livewire\Component;
use Livewire\WithFileUploads;

class AttachmentsPopover extends Component
{
    use WithFileUploads;

    public Card $card;
    public $file;
    public bool $open = false;

    public function mount(Card $card): void
    {
        $this->card = $card;
        $this->authorize('view', $this->card);
    }

    public function updatedFile(): void
    {
        $this->authorize('update', $this->card);

        $this->validate([
            'file' => 'required|file|max:10240',
        ]);

        $name = $this->file->getClientOriginalName();
        $disk = config('kanban.storage_disk', 'public');
        $directory = trim(config('kanban.storage_directory', 'kanban/card-attachments'), '/');
        $path = $this->file->store("{$directory}/{$this->card->id}", $disk);

        $this->card->attachments()->create([
            'user_id' => auth()->id(),
            'file_name' => $name,
            'file_path' => $path,
            'mime_type' => $this->file->getMimeType(),
            'size' => $this->file->getSize(),
        ]);

        $this->card->refresh();
        $this->reset('file');
        $this->open = false;

        $this->dispatch('card-updated');
    }

    public function render()
    {
        return view('kanban::livewire.boards.card.attachments-popover');
    }
}
