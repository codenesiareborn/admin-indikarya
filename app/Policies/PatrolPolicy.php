<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Patrol;
use Illuminate\Auth\Access\HandlesAuthorization;

class PatrolPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Patrol');
    }

    public function view(AuthUser $authUser, Patrol $patrol): bool
    {
        return $authUser->can('View:Patrol');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Patrol');
    }

    public function update(AuthUser $authUser, Patrol $patrol): bool
    {
        return $authUser->can('Update:Patrol');
    }

    public function delete(AuthUser $authUser, Patrol $patrol): bool
    {
        return $authUser->can('Delete:Patrol');
    }

    public function restore(AuthUser $authUser, Patrol $patrol): bool
    {
        return $authUser->can('Restore:Patrol');
    }

    public function forceDelete(AuthUser $authUser, Patrol $patrol): bool
    {
        return $authUser->can('ForceDelete:Patrol');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Patrol');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Patrol');
    }

    public function replicate(AuthUser $authUser, Patrol $patrol): bool
    {
        return $authUser->can('Replicate:Patrol');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Patrol');
    }

}