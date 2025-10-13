<?php

namespace App\Filament\Pages;

use App\Models\ProductionTask;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class InstallationCalendar extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'تقويم التركيب';
    protected static ?string $title           = 'تقويم مواعيد التركيب (متوقعة)';
    protected static ?string $slug            = 'installation-calendar';
//    protected static ?string $navigationGroup = 'إدارة المشاريع';
    protected static ?int    $navigationSort  = 40;

    protected static string $view = 'filament.pages.installation-calendar';

    public static function canAccess(): bool
    {
        return auth()->check()
            && auth()->user()->hasAnyRole(['admin','super-admin','showroom_manager', 'factory_manager']);
    }
    public bool $showDetail = false;
    public array $detail = [];

    public function fetchEvents(string $start, string $end): array
    {
        $startAt = Carbon::parse($start)->startOfDay();
        $endAt   = Carbon::parse($end)->endOfDay();

        $tasks = ProductionTask::query()
            ->with(['project.client', 'department'])
            ->whereNotNull('planned_install_at')
            ->whereBetween('planned_install_at', [$startAt, $endAt])
            ->whereNotIn('status', ['cancelled']) // استبعد الملغاة فقط
            ->get();

        $statusColor = function (?string $s): string {
            return match ($s) {
                'pending'            => '#71717a', // zinc
                'under_review'       => '#f59e0b', // amber
                'approved'           => '#10b981', // emerald/green
                'materials_prep'     => '#8b5cf6', // violet
                'materials_done'     => '#34d399', // emerald
                'waiting_production' => '#f59e0b', // amber
                'in_progress'        => '#0ea5e9', // sky
                'on_hold'            => '#eab308', // yellow
                'rework'             => '#ef4444', // red
                'completed'          => '#22c55e', // green
                default              => '#64748b', // slate
            };
        };

        return $tasks->map(function ($t) use ($statusColor) {
            $date = Carbon::parse($t->planned_install_at);

            $titleParts = [];
            $titleParts[] = $t->project?->project_name ?: "مهمة #{$t->id}";
            if ($t->project?->client?->client_name) {
                $titleParts[] = $t->project->client->client_name;
            }
            if ($t->department?->dept_name) {
                $titleParts[] = "قسم: {$t->department->dept_name}";
            }
            $title = implode(' — ', $titleParts);

            $color = $statusColor($t->status);

            return [
                'id'              => (string) $t->id,
                'title'           => $title,
                'start'           => $date->toDateString(),   // حدث يوم كامل
                'allDay'          => true,
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'status'     => $t->status,
                    'project_id' => $t->project_id,
                ],
            ];
        })->values()->all();
    }

    /**
     * تحميل تفاصيل مهمة عند الضغط على الحدث لعرضها في النافذة الجانبية
     */
    public function loadTaskDetails(int $taskId): void
    {
        $t = ProductionTask::with([
            'project.client',
            'project.productionRequest',
            'department',
            'materialRequests' => fn($q) => $q->latest(),
        ])->findOrFail($taskId);

        $statusLabel = match ((string) $t->status) {
            'pending'            => 'قيد الانتظار',
            'received'           => 'تم الاستلام',
            'under_review'       => 'قيد المراجعة',
            'approved'           => 'معتمد',
            'rejected'           => 'مرفوض',
            'materials_prep'     => 'تحضير الخامات',
            'materials_done'     => 'تم توفير الخامات',
            'waiting_production' => 'جاهز لبدء التصنيع',
            'in_progress'        => 'قيد التنفيذ',
            'on_hold'            => 'معلّق',
            'rework'             => 'إعادة عمل',
            'completed'          => 'مكتمل',
            'cancelled'          => 'ملغي',
            default              => (string) $t->status,
        };

        $this->detail = [
            'task_id'            => $t->id,
            'task_title'         => $t->task_title ?? $t->title ?? ("مهمة #{$t->id}"),
            'status'             => $statusLabel,
            'planned_install_at' => optional($t->planned_install_at)->format('Y-m-d') ?: '—',
            'planned_start_at'   => optional($t->planned_start_at)->format('Y-m-d') ?: '—',
            'planned_end_at'     => optional($t->planned_end_at)->format('Y-m-d') ?: '—',
            'project_id'         => $t->project?->id,
            'project_name'       => $t->project?->project_name,
            'client_name'        => $t->project?->client?->client_name,
            'production_request' => $t->project?->productionRequest?->id,
            'department'         => $t->department?->dept_name,
            'owner_role'         => $t->current_owner_role,
            'owner_user_id'      => $t->current_owner_user_id,
            'links'              => [
                'task'    => class_exists(\App\Filament\Resources\TaskResource::class)
                    ? \App\Filament\Resources\TaskResource::getUrl('view', ['record' => $t])
                    : null,
                'project' => class_exists(\App\Filament\Resources\ProjectResource::class)
                    ? \App\Filament\Resources\ProjectResource::getUrl('view', ['record' => $t->project])
                    : null,
                'request' => ($t->project && class_exists(\App\Filament\Resources\ProductionRequestResource::class) && $t->project->productionRequest)
                    ? \App\Filament\Resources\ProductionRequestResource::getUrl('view', ['record' => $t->project->productionRequest])
                    : null,
            ],
            'materials'          => $t->materialRequests->map(function ($mr) {
                return [
                    'id'         => $mr->id,
                    'status'     => $mr->status,
                    'expected'   => optional($mr->expected_delivery_at)->format('Y-m-d H:i') ?: '—',
                    'provided'   => optional($mr->provided_at)->format('Y-m-d H:i') ?: '—',
                    'po_number'  => $mr->po_number ?: '—',
                    'actual_cost'=> $mr->actual_cost ? number_format($mr->actual_cost, 2) : '—',
                ];
            })->all(),
        ];

        $this->dispatch('open-modal', id: 'task-detail');
    }
}
