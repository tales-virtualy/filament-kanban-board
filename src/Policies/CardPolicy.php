<?php

namespace FilamentKanban\Policies;

use FilamentKanban\Models\BoardList;
use FilamentKanban\Models\Card;
use Illuminate\Auth\Access\Response;

class CardPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny($user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view($user, Card $card): bool
    {
        return $card->list->board->isMember($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create($user, BoardList $list): bool
    {
        if ($list->board->isArchived()) {
            return false;
        }

        return $list->board->isAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update($user, Card $card): bool
    {
        if ($card->list->board->isAdmin($user)) {
            return true;
        }

        return $card->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete($user, Card $card): bool
    {
        return $card->list->board->isOwner($user);
    }

    public function archive($user, Card $card): bool
    {
        return $card->list->board->isAdmin($user);
    }

    public function unarchive($user, Card $card): bool
    {
        return $card->list->board->isAdmin($user);
    }
}
