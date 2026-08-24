<?php

namespace App\Policies;

use App\Models\HeroSlide;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HeroSlidePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_hero::slide');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('view_hero::slide');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_hero::slide');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('update_hero::slide');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('delete_hero::slide');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_hero::slide');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_hero::slide');
    }
}
