<?php

namespace App\Filament\Pages;

use App\Models\MaintenanceRequest;
use App\Models\Showroom;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class MaintenanceCalendar extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'تقويم الصيانة';
    protected static ?string $title           = 'جدول مواعيد الصيانة';
    protected static ?string $slug            = 'maintenance-calendar';
    protected static string $view             = 'filament.pages.maintenance-calendar';

    // نطاق التاريخ
    public ?string $from = null;
    public ?string $to   = null;

    // إعادة جدولة
    public ?int    $rescheduleRequestId = null;
    public ?string $newMaintenanceDate  = null;
    public ?string $rescheduleNote      = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole([
            'admin',
            'super-admin',
            'factory_manager',
            'showroom_manager',
            'sales',
        ]);
    }

    public function mount(): void
    {
        $today      = Carbon::today();
        $this->from = $today->copy()->startOfMonth()->toDateString();
        $this->to   = $today->copy()->endOfMonth()->toDateString();
    }

    /**
     * ترجع الطلبات مجمعة حسب اليوم (تستخدم في الـ Blade).
     */
    public function groupedByDate(): array
    {
        $requests = $this->baseQueryForTable()
            ->orderBy('expected_start_at')
            ->orderBy('request_date')
            ->get();

        return $requests
            ->groupBy(function (MaintenanceRequest $r) {
                $date = $r->expected_start_at
                    ?? $r->actual_start_at
                    ?? $r->request_date;

                return Carbon::parse($date)->toDateString();
            })
            ->map(fn ($group) => $group->values()->all())
            ->toArray();
    }

    /**
     * الاستعلام الأساسي لطلبات الصيانة التي تظهر في التقويم.
     */
    protected function baseQueryForTable(): Builder
    {
        $startAt = $this->from
            ? Carbon::parse($this->from)->startOfDay()
            : now()->startOfMonth();

        $endAt = $this->to
            ? Carbon::parse($this->to)->endOfDay()
            : now()->endOfMonth();

        $q = MaintenanceRequest::query()
            ->with([
                'client',
                'project.productionRequest.showroom',
                'requester',
                'ownerUser',
            ])
            ->whereBetween(
                \DB::raw('COALESCE(expected_start_at, request_date)'),
                [$startAt, $endAt]
            )
            ->whereNotIn('status', ['cancelled']);

        return $this->applyRoleScope($q);
    }

    /**
     * حصر النتائج حسب دور المستخدم الحالي.
     */
    protected function applyRoleScope(Builder $q): Builder
    {
        $u = auth()->user();

        if (! $u) {
            return $q->whereRaw('1=0');
        }

        // الأدمن + السوبر أدمن + مدير المصنع يرون الكل
        if ($u->hasAnyRole(['admin', 'super-admin', 'factory_manager'])) {
            return $q;
        }

        // مدير المعرض: الطلبات التابعة لمعارضه
        if ($u->hasRole('showroom_manager')) {
            $employeeId = $u->employee?->getKey();
            if (! $employeeId) {
                // لا يوجد Employee مرتبط → لا شيء
                return $q->whereRaw('1=0');
            }

            // المعارض التي يديرها هذا الـ Employee
            $showroomIds = Showroom::query()
                ->where('manager_id', $employeeId)
                ->pluck('id');

            if ($showroomIds->isEmpty()) {
                return $q->whereRaw('1=0');
            }

            $q->whereHas('project.productionRequest', function (Builder $qq) use ($showroomIds) {
                $qq->whereIn('showroom_id', $showroomIds);
            });
        }

        // المبيعات: الطلبات التي طلبها أو أنشأها
        if ($u->hasRole('sales')) {
            $q->where(function (Builder $qq) use ($u) {
                $qq->where('requested_by', $u->id);

                // لو لديك عمود created_by فعلياً يمكنك إبقاء هذا السطر
                if (\Schema::hasColumn('maintenance_requests', 'created_by')) {
                    $qq->orWhere('created_by', $u->id);
                }
            });
        }

        return $q;
    }

    public function canReschedule(MaintenanceRequest $request): bool
    {
        $u = auth()->user();

        if (! $u) {
            return false;
        }

        // الأدمن ومدير المصنع: يمكنهم التعديل ما دام الطلب غير مكتمل/ملغي
        if ($u->hasAnyRole(['admin', 'super-admin', 'factory_manager'])) {
            return ! in_array($request->status, ['completed', 'cancelled'], true);
        }

        // مدير المعرض: لو الطلب تابع لأحد معارضه وفي حالة new أو in_progress
        if ($u->hasRole('showroom_manager')) {
            $employeeId = $u->employee?->getKey();
            if (! $employeeId) {
                return false;
            }

            $showroomIds = Showroom::query()
                ->where('manager_id', $employeeId)
                ->pluck('id');

            $requestShowroomId = $request->project?->productionRequest?->showroom_id;

            if (! $requestShowroomId || $showroomIds->isEmpty()) {
                return false;
            }

            return $showroomIds->contains($requestShowroomId)
                && in_array($request->status, ['new', 'in_progress'], true);
        }

        // بقية الأدوار: عرض فقط
        return false;
    }

    /**
     * فتح مودال تعديل موعد الصيانة.
     */
    public function openRescheduleModal(int $requestId): void
    {
        $request = MaintenanceRequest::with(['project.productionRequest.showroom'])->findOrFail($requestId);

        if (! $this->canReschedule($request)) {
            throw ValidationException::withMessages([
                'newMaintenanceDate' => 'ليست لديك الصلاحية لتعديل موعد هذه الصيانة.',
            ]);
        }

        $this->rescheduleRequestId = $request->id;
        $this->newMaintenanceDate  = optional(
            $request->expected_start_at ?? $request->request_date
        )->toDateString();

        $this->rescheduleNote = null;

        $this->dispatch('open-modal', id: 'reschedule-modal');
    }

    /**
     * حفظ تعديل موعد الصيانة.
     */
    public function saveReschedule(): void
    {
        $this->validate([
            'rescheduleRequestId' => ['required', 'integer', 'exists:maintenance_requests,id'],
            'newMaintenanceDate'  => ['required', 'date', 'after_or_equal:today'],
        ], [], [
            'newMaintenanceDate' => 'تاريخ الصيانة الجديد',
        ]);

        $request = MaintenanceRequest::with(['project.productionRequest.showroom'])
            ->findOrFail($this->rescheduleRequestId);

        if (! $this->canReschedule($request)) {
            throw ValidationException::withMessages([
                'newMaintenanceDate' => 'ليست لديك الصلاحية لتعديل موعد هذه الصيانة.',
            ]);
        }

        $oldDate = optional(
            $request->expected_start_at ?? $request->request_date
        )->toDateString();

        $request->expected_start_at = Carbon::parse($this->newMaintenanceDate)->startOfDay();
        $request->save();

        // يمكن لاحقاً إضافة Log + تنبيه باستخدام MaintenanceNotifier

        $this->dispatch('close-modal', id: 'reschedule-modal');
        $this->dispatch(
            'notify',
            type: 'success',
            title: 'تم',
            body: 'تم تحديث موعد الصيانة بنجاح من ' . $oldDate . ' إلى ' . $this->newMaintenanceDate . '.'
        );

        $this->reset([
            'rescheduleRequestId',
            'newMaintenanceDate',
            'rescheduleNote',
        ]);
    }
}
