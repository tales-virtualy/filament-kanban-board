<?php

namespace FilamentKanban\Livewire\Boards\Card;

use FilamentKanban\Models\Card;
use FilamentKanban\Models\CardComment;
use Livewire\Component;

class CardComments extends Component
{
    public Card $card;
    public string $body = '';

    public function mount(Card $card): void
    {
        $this->card = $card;
    }

    public function addComment(): void
    {
        $this->validate([
            'body' => 'required|string',
        ]);

        $this->card->comments()->create([
            'user_id' => auth()->id(),
            'body' => $this->body,
        ]);

        $this->reset('body');
        $this->dispatch('card-updated');
    }

    public function deleteComment(int $commentId): void
    {
        $comment = CardComment::findOrFail($commentId);

        if ($comment->user_id !== auth()->id() && !$this->card->list->board->isAdmin(auth()->user())) {
            abort(403);
        }

        $comment->delete();
        $this->dispatch('card-updated');
    }

    public function render()
    {
        return view('kanban::livewire.boards.card.card-comments', [
            'comments' => $this->card->comments()->with('user')->latest()->get(),
        ]);
    }
}
