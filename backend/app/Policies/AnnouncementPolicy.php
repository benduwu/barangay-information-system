<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Announcement;

class AnnouncementPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('staff');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Announcement $announcement): bool
    {
        return $user->hasRole('admin') || $user->hasRole('staff');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('staff');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Announcement $announcement): bool
    {
        return $user->hasRole('admin') || $user->hasRole('staff');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->hasRole('admin') || $user->hasRole('staff');
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publish(User $user, Announcement $announcement): bool
    {
        return $user->hasRole('admin') || $user->hasRole('staff');
    }
}
