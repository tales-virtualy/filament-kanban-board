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
    }

    public function updatedFile(): void
    {
        $this->validate([
            'file' => 'required|max:10240', // 10MB
        ]);

        $name = $this->file->getClientOriginalName();
        $disk = config('kanban.storage_disk', 'public');
        $path = $this->file->store('kanban/card-attachments/' . $this->card->id, $disk);

        $this->card->attachments()->create([
            'user_id' => auth()->id(),
            'file_name' => $name,
            'file_path' => $path,
            'mime_type' => $this->file->getMimeType(),
            'size' => $this->file->getSize(),
        ]);

        $this->reset('file');
        $this->open = false;

        $this->dispatch('card-updated');
    }

    public function render()
    {
        return view('kanban::livewire.boards.card.attachments-popover');
    }
}
