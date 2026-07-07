<?php

namespace App\Policies;

use App\Models\Layer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LayerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-layers');
    }

    public function view(User $user, Layer $layer): bool
    {
        return $user->hasPermissionTo('view-layers');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-layers');
    }

    public function update(User $user, Layer $layer): bool
    {
        return $user->hasPermissionTo('edit-layers');
    }

    public function delete(User $user, Layer $layer): bool
    {
        return $user->hasPermissionTo('delete-layers');
    }
}
