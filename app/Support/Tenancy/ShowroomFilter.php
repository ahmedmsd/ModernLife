<?php

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Facades\Auth;

final class ShowroomFilter
{

    public static function forUserEloquent(
        EloquentBuilder $q,
        string $column = 'showroom_id'
    ): EloquentBuilder {
        $u = Auth::user();
        if (! $u) return $q;

        if (self::userBypassesFilter($u)) return $q;

        $showroomId = $u->showroom_id ?? null;
        if ($showroomId) {
            $q->where($q->getModel()->getTable().'.'.$column, $showroomId);
        }

        return $q;
    }

    public static function forUserQuery(BaseBuilder $q, string $column = 'showroom_id'): BaseBuilder
    {
        $u = Auth::user();
        if (! $u) return $q;

        if (self::userBypassesFilter($u)) return $q;

        $showroomId = $u->showroom_id ?? null;
        if ($showroomId) {
            $q->where($column, $showroomId);
        }

        return $q;
    }

    private static function userBypassesFilter($user): bool
    {
        return method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['admin','super-admin']);
    }
}

