<?php

namespace App\Filament\Pages;

use App\Models\MaintenanceRequest;
use App\Models\Showroom;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class MaintenanceCalendar extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'تقويم الصيانة';
    protected static ?string $title           = 'جدول مواعيد الصيانة';
    protected static ?string $slug            = 'maintenance-calendar';
    protected static string $view             = 'filament.pages.maintenance-calendar';

    public ?string $from = null;
    public ?string $to   = null;

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

    public function groupedByDate(): array
    {
        $requests = $this->baseQueryForTable()
            ->orderBy('expected_start_at')
            ->orderBy('actual_start_at')
            ->get();

        return $requests
            ->groupBy(function (MaintenanceRequest $r) {
                $date = $r->expected_start_at ?? $r->actual_start_at;

                return \Illuminate\Support\Carbon::parse($date)->toDateString();
            })
            ->map(fn ($group) => $group->values()->all())
            ->toArray();
    }

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
                'showroom',
                'project.productionRequest.showroom',
                'requester',
                'ownerUser',
            ])
            ->whereNotNull('expected_start_at')
            ->orWhereNotNull('actual_start_at');

        $q->whereBetween(
            DB::raw('COALESCE(expected_start_at, actual_start_at)'),
            [$startAt, $endAt]
        )
            ->active();

        return $this->applyRoleScope($q);
    }

    protected function applyRoleScope(Builder $q): Builder
    {
        $u = auth()->user();

        if (! $u) {
            return $q->whereRaw('1=0');
        }

        if ($u->hasAnyRole(['admin', 'super-admin', 'factory_manager'])) {
            return $q;
        }

        if ($u->hasRole('showroom_manager')) {
            $employeeId = $u->id;
            if (! $employeeId) {
                return $q->whereRaw('1=0');
            }

            $showroomIds = Showroom::query()
                ->where('manager_id', $employeeId)
                ->pluck('id');

            if ($showroomIds->isEmpty()) {
                return $q->whereRaw('1=0');
            }

            $q->whereIn('showroom_id', $showroomIds);
        }

        if ($u->hasRole('sales')) {
            $q->where(function (Builder $qq) use ($u) {
                $qq->where('requested_by', $u->id);

                if (Schema::hasColumn('maintenance_requests', 'created_by')) {
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

        if ($u->hasAnyRole(['admin', 'super-admin', 'factory_manager'])) {
            return ! in_array($request->status, ['completed', 'cancelled'], true);
        }

        if ($u->hasRole('showroom_manager')) {
            $employeeId = $u->id;
            if (! $employeeId) {
                return false;
            }

            $showroomIds = Showroom::query()
                ->where('manager_id', $employeeId)
                ->pluck('id');

            $requestShowroomId = $request->showroom_id;

            if (! $requestShowroomId || $showroomIds->isEmpty()) {
                return false;
            }

            return $showroomIds->contains($requestShowroomId)
                && in_array($request->status, ['new', 'in_progress'], true);
        }

        return false;
    }

    public function openRescheduleModal(int $requestId): void
    {
        $request = MaintenanceRequest::with(['showroom'])->findOrFail($requestId);

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

    public function saveReschedule(): void
    {
        $this->validate([
            'rescheduleRequestId' => ['required', 'integer', 'exists:maintenance_requests,id'],
            'newMaintenanceDate'  => ['required', 'date', 'after_or_equal:today'],
        ], [], [
            'newMaintenanceDate' => 'تاريخ الصيانة الجديد',
        ]);

        $request = MaintenanceRequest::with(['showroom'])
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
