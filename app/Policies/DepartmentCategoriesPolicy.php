<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DepartmentCategories;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentCategoriesPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_department::categories');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DepartmentCategories $departmentCategories): bool
    {
        return $user->can('view_department::categories');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_department::categories');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DepartmentCategories $departmentCategories): bool
    {
        return $user->can('update_department::categories');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DepartmentCategories $departmentCategories): bool
    {
        return $user->can('delete_department::categories');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_department::categories');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, DepartmentCategories $departmentCategories): bool
    {
        return $user->can('force_delete_department::categories');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_department::categories');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, DepartmentCategories $departmentCategories): bool
    {
        return $user->can('restore_department::categories');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_department::categories');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, DepartmentCategories $departmentCategories): bool
    {
        return $user->can('replicate_department::categories');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_department::categories');
    }
}
