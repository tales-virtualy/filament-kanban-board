<?php

namespace FilamentKanban\Livewire\Boards\Card;

use FilamentKanban\Models\Card;
use FilamentKanban\Models\CustomTag;
use Livewire\Component;

class TagsPopover extends Component
{
    public Card $card;
    public bool $open = false;
    public array $tags = [];
    public $availableTags;

    public function mount(Card $card): void
    {
        $this->card = $card;
        $this->tags = $card->tags->pluck('id')->toArray();
        $this->availableTags = CustomTag::orderBy('name')->get();
    }

    public function toggleTag(int $tagId): void
    {
        if (in_array($tagId, $this->tags)) {
            $this->tags = array_values(array_diff($this->tags, [$tagId]));
        } else {
            $this->tags[] = $tagId;
        }

        $this->card->syncTagsWithLog($this->tags);
        $this->dispatch('card-updated');
    }

    public function render()
    {
        return view('kanban::livewire.boards.card.tags-popover');
    }
}
