<?php

namespace FilamentKanban\Policies;

use FilamentKanban\Models\Board;

class BoardPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny($user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view($user, Board $board): bool
    {
        if (!$board->is_private) {
            return true;
        }

        return $board->isMember($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create($user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update($user, Board $board): bool
    {
        return $board->isAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete($user, Board $board): bool
    {
        return $board->isOwner($user);
    }

    /**
     * Arquivar
     */
    public function archive($user, Board $board): bool
    {
        return $board->isAdmin($user);
    }

    /**
     * Restaurar (desarquivar)
     */
    public function unarchive($user, Board $board): bool
    {
        return $board->isAdmin($user);
    }
}
