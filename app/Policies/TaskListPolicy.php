<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TaskList;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskListPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TaskList');
    }

    public function view(AuthUser $authUser, TaskList $taskList): bool
    {
        return $authUser->can('View:TaskList');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TaskList');
    }

    public function update(AuthUser $authUser, TaskList $taskList): bool
    {
        return $authUser->can('Update:TaskList');
    }

    public function delete(AuthUser $authUser, TaskList $taskList): bool
    {
        return $authUser->can('Delete:TaskList');
    }

    public function restore(AuthUser $authUser, TaskList $taskList): bool
    {
        return $authUser->can('Restore:TaskList');
    }

    public function forceDelete(AuthUser $authUser, TaskList $taskList): bool
    {
        return $authUser->can('ForceDelete:TaskList');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TaskList');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TaskList');
    }

    public function replicate(AuthUser $authUser, TaskList $taskList): bool
    {
        return $authUser->can('Replicate:TaskList');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TaskList');
    }

}