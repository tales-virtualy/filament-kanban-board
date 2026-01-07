<?php

namespace FilamentKanban\Policies;

use FilamentKanban\Models\Board;
use FilamentKanban\Models\BoardList;
use Illuminate\Auth\Access\Response;

class BoardListPolicy
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
    public function view($user, BoardList $list): bool
    {
        return $list->board->isMember($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create($user, Board $board): bool
    {
        if ($board->isArchived()) {
            return false;
        }

        return $board->isAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update($user, BoardList $list): bool
    {
        return $list->board->isAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete($user, BoardList $list): bool
    {
        return $list->board->isOwner($user);
    }

    public function archive($user, BoardList $list): bool
    {
        return $list->board->isAdmin($user);
    }

    public function unarchive($user, BoardList $list): bool
    {
        return $list->board->isAdmin($user);
    }
}
