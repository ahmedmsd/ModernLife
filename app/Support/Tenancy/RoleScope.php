<?php
namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class RoleScope
{
    public static function apply(Builder $q, string $table): Builder
    {
        $u   = auth()->user();
        $emp = $u?->employee;

        if (! $u) {
            // بدون مستخدم -> لا شيء
            return $q->whereRaw('1=0');
        }

        // 1) أدوار تَرى الكل
        if ($u->hasAnyRole(['admin','super-admin','factory_manager'])) {
            return $q;
        }

        // Helpers
        $hasCol = fn (string $col) => Schema::hasColumn($table, $col);
        $uid    = (int) $u->id;
        $eid    = (int) ($emp?->getKey() ?? 0);

        // ===== showroom_manager: عناصر تخص المعارض التي يديرها =====
        if ($u->hasRole('showroom_manager') && $eid) {
            // كل المعارض التي يديرها هذا الموظف
            $showroomIds = \App\Models\Showroom::query()
                ->where('manager_id', $eid)
                ->pluck('id');

            if ($table === 'production_requests') {
                // عمود showroom_id موجود مباشرة
                return $q->whereIn($table.'.showroom_id', $showroomIds);
            }

            if ($table === 'projects') {
                // projects -> production_requests(showroom_id)
                return $q->whereExists(function ($sub) use ($showroomIds, $table) {
                    $sub->from('production_requests as pr')
                        ->whereColumn('pr.id', $table.'.production_request_id')
                        ->whereIn('pr.showroom_id', $showroomIds);
                });
            }

            if (in_array($table, ['production_tasks','tasks'])) {
                // tasks -> projects -> production_requests(showroom_id)
                return $q->whereExists(function ($sub) use ($showroomIds, $table) {
                    $sub->from('projects as p')
                        ->join('production_requests as pr', 'pr.id', '=', 'p.production_request_id')
                        ->whereColumn('p.id', $table.'.project_id')
                        ->whereIn('pr.showroom_id', $showroomIds);
                });
            }

            // جداول أخرى: إن وُجد عمود showroom_id استخدمه
            if ($hasCol('showroom_id')) {
                return $q->whereIn($table.'.showroom_id', $showroomIds);
            }

            // افتراضي آمن
            return $q->whereRaw('1=0');
        }

        // ===== sales: يرى ما أنشأه فقط =====
        if ($u->hasRole('sales')) {
            if ($hasCol('created_by')) {
                return $q->where($table.'.created_by', $uid);
            }

            // إن لم يوجد created_by على الجدول، نحاول المرور عبر العلاقات المعروفة
            if ($table === 'projects') {
                // projects -> production_requests.created_by
                return $q->whereExists(function ($sub) use ($uid, $table) {
                    $sub->from('production_requests as pr')
                        ->whereColumn('pr.id', $table.'.production_request_id')
                        ->where('pr.created_by', $uid);
                });
            }

            if (in_array($table, ['production_tasks','tasks'])) {
                // tasks -> projects -> production_requests.created_by
                return $q->whereExists(function ($sub) use ($uid, $table) {
                    $sub->from('projects as p')
                        ->join('production_requests as pr', 'pr.id', '=', 'p.production_request_id')
                        ->whereColumn('p.id', $table.'.project_id')
                        ->where('pr.created_by', $uid);
                });
            }

            // افتراضي آمن
            return $q->whereRaw('1=0');
        }

        // ===== department_manager: قسمه فقط (ينطبق منطقيًا على المهام) =====
        if ($u->hasRole('department_manager') && $emp?->department_id) {
            if (in_array($table, ['production_tasks','tasks']) && $hasCol('department_id')) {
                $q->where($table.'.department_id', $emp->department_id);
            }
            // على جداول أخرى: لا قيد إضافي.
        }

        // ===== quality_manager: ما يخص الجودة (على المهام فقط) =====
        if ($u->hasRole('quality_manager')) {
            if (in_array($table, ['production_tasks','tasks'])) {
                // إمّا مملوكة للجودة، أو أُرسلت للجودة عبر اللوج
                $q->where(function ($qq) use ($table) {
                    $qq->when(Schema::hasColumn($table, 'current_owner_role'),
                        fn ($w) => $w->where($table.'.current_owner_role', 'quality_manager')
                    )->orWhereExists(function ($sub) use ($table) {
                        $sub->from('task_logs as tl')
                            ->whereColumn('tl.task_id', $table.'.id')
                            ->whereIn('tl.type', ['manufacturing_sent_to_qa','installation_sent_to_qa']);
                    });
                });
            }
        }

        // ===== installation_manager: ما يخص التركيب (على المهام) =====
        if ($u->hasRole('installation_manager')) {
            if (in_array($table, ['production_tasks','tasks']) && Schema::hasColumn($table, 'current_owner_role')) {
                $q->where($table.'.current_owner_role', 'installation_manager');
            }
        }

        return $q;
    }
}
