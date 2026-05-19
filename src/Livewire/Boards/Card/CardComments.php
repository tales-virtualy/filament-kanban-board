<?php

namespace FilamentKanban\Livewire\Boards\Card;

use FilamentKanban\Models\Card;
use FilamentKanban\Models\CardComment;
use Livewire\Component;

class CardComments extends Component
{
    public Card $card;

    public bool $compact = false;

    public string $body = '';

    public ?int $editingCommentId = null;

    public string $editingBody = '';

    protected $listeners = [
        'card-updated' => '$refresh',
    ];

    public function mount(Card $card, bool $compact = false): void
    {
        $this->card = $card;
        $this->compact = $compact;
        $this->authorize('view', $this->card);
    }

    public function addComment(): void
    {
        $this->authorize('update', $this->card);

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

    public function startEditingComment(int $commentId): void
    {
        $comment = $this->findEditableComment($commentId);

        $this->editingCommentId = $commentId;
        $this->editingBody = $comment->body;
    }

    public function updateComment(): void
    {
        $comment = $this->findEditableComment($this->editingCommentId);

        $this->validate([
            'editingBody' => 'required|string',
        ]);

        $comment->update([
            'body' => $this->editingBody,
        ]);

        $this->reset(['editingCommentId', 'editingBody']);
        $this->dispatch('card-updated');
    }

    public function cancelEditingComment(): void
    {
        $this->reset(['editingCommentId', 'editingBody']);
    }

    public function deleteComment(int $commentId): void
    {
        $comment = $this->findEditableComment($commentId);

        $comment->delete();
        $this->dispatch('card-updated');
    }

    protected function findEditableComment(int $commentId): CardComment
    {
        $comment = CardComment::query()
            ->where('card_id', $this->card->getKey())
            ->findOrFail($commentId);

        if ($comment->user_id !== auth()->id() && !$this->card->list->board->isAdmin(auth()->user())) {
            abort(403);
        }

        return $comment;
    }

    public function render()
    {
        return view('kanban::livewire.boards.card.card-comments', [
            'comments' => $this->card->comments()->with('user')->latest()->get(),
        ]);
    }
}
