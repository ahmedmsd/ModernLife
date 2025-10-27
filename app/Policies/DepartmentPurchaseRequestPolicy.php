<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DepartmentPurchaseRequest;

class DepartmentPurchaseRequestPolicy
{
    public function viewAny(User $u): bool {
        return $u->hasAnyRole(['admin','super-admin','factory_manager','purchasing_manager','department_manager']);
    }
    public function view(User $u, DepartmentPurchaseRequest $r): bool {
        return $this->viewAny($u) || $r->requested_by === $u->id;
    }
    public function create(User $u): bool {
        return $u->hasAnyRole(['admin','super-admin','department_manager']);
    }
    public function update(User $u, DepartmentPurchaseRequest $r): bool {
        if (in_array($r->status, ['factory_approved','sent_to_purchasing','purchased','delivered'])) {
            return $u->hasAnyRole(['admin','super-admin']);
        }
        return $r->requested_by === $u->id || $u->hasAnyRole(['admin','super-admin','department_manager']);
    }
    public function delete(User $u, DepartmentPurchaseRequest $r): bool {
        return $u->hasAnyRole(['admin','super-admin']);
    }
}

