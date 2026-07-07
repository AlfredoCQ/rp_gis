<?php

namespace App\Policies;

use App\Models\MapFeature;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MapFeaturePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-features');
    }

    public function view(User $user, MapFeature $mapFeature): bool
    {
        return $user->hasPermissionTo('view-features');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-features');
    }

    public function update(User $user, MapFeature $mapFeature): bool
    {
        return $user->hasPermissionTo('edit-features');
    }

    public function delete(User $user, MapFeature $mapFeature): bool
    {
        return $user->hasPermissionTo('delete-features');
    }

    public function restore(User $user, MapFeature $mapFeature): bool
    {
        return $user->hasPermissionTo('restore-features');
    }
}
