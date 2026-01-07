<?php

namespace FilamentKanban\Livewire\Boards\Card;

use FilamentKanban\Models\Card;
use FilamentKanban\Models\CardAttachment;
use FilamentKanban\Services\CardActivityService;
use Livewire\Component;

class CardAttachments extends Component
{
    public Card $card;

    public function mount(Card $card): void
    {
        $this->card = $card;
    }

    public function deleteAttachment(int $attachmentId): void
    {
        $attachment = CardAttachment::findOrFail($attachmentId);

        $this->authorize('delete', $attachment->card);

        $fileName = $attachment->file_name;
        $attachment->delete();

        CardActivityService::attachmentRemoved($this->card, $fileName);

        $this->dispatch('card-updated');
    }

    public function render()
    {
        return view('kanban::livewire.boards.card.card-attachments', [
            'attachments' => $this->card->attachments()->latest()->get(),
        ]);
    }
}
