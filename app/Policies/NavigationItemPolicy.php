<?php

namespace App\Policies;

use App\Models\NavigationItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NavigationItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_navigation_item');
    }

    public function view(User $user, NavigationItem $navigationItem): bool
    {
        return $user->can('view_navigation_item');
    }

    public function create(User $user): bool
    {
        return $user->can('create_navigation_item');
    }

    public function update(User $user, NavigationItem $navigationItem): bool
    {
        return $user->can('update_navigation_item');
    }

    public function delete(User $user, NavigationItem $navigationItem): bool
    {
        return $user->can('delete_navigation_item');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_navigation_item');
    }

    public function forceDelete(User $user, NavigationItem $navigationItem): bool
    {
        return $user->can('force_delete_navigation_item');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_navigation_item');
    }

    public function restore(User $user, NavigationItem $navigationItem): bool
    {
        return $user->can('restore_navigation_item');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_navigation_item');
    }

    public function replicate(User $user, NavigationItem $navigationItem): bool
    {
        return $user->can('replicate_navigation_item');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_navigation_item');
    }
}
