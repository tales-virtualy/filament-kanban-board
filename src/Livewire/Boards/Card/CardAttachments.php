<?php

namespace FilamentKanban\Livewire\Boards\Card;

use FilamentKanban\Models\Card;
use FilamentKanban\Models\CardAttachment;
use FilamentKanban\Services\CardActivityService;
use Livewire\Component;

class CardAttachments extends Component
{
    public Card $card;

    protected $listeners = [
        'card-updated' => 'refreshCard',
    ];

    public function mount(Card $card): void
    {
        $this->card = $card;
        $this->authorize('view', $this->card);
    }

    public function refreshCard(): void
    {
        $this->card->refresh();
        $this->card->load('attachments');
    }

    public function deleteAttachment(int $attachmentId): void
    {
        $attachment = CardAttachment::findOrFail($attachmentId);

        $this->authorize('update', $this->card);

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
