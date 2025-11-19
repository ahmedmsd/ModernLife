<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\Pages\ActiveTasks;
use App\Filament\Resources\TaskResource\Pages\CompletedTasks;
use App\Filament\Resources\TaskResource\Pages\ListTasks;
use App\Filament\Resources\TaskResource\Pages\ReturnedToFactory;
use App\Filament\Resources\TaskResource\Pages\ViewTask;
use App\Models\ProductionTask;
use App\Support\Tenancy\RoleScope;
use App\Support\Tenancy\ShowroomFilter;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = ProductionTask::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = parent::getEloquentQuery()
            ->with(['project.productionRequest.showroom', 'department', 'employee','project.client'])
            ->latest('id');

        $user = auth()->user();

        $isSuper = $user && method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['admin','super-admin','factory_manager']);

        if (! $isSuper) {
            $isShowroomManager = $user && method_exists($user, 'hasRole') && $user->hasRole('showroom_manager');
            $employeeId = $user?->id;

            if ($isShowroomManager) {
                if (! $employeeId) {
                    return $q->whereRaw('1 = 0');
                }

                $q->whereExists(function ($sub) use ($employeeId) {
                    $sub->from('projects as p')
                        ->join('production_requests as pr', 'pr.id', '=', 'p.production_request_id')
                        ->join('showrooms as s', 's.id', '=', 'pr.showroom_id')
                        ->whereColumn('p.id', 'production_tasks.project_id')
                        ->where('s.manager_id', $employeeId);
                });
            }
        }

        return $q;
    }


    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('بيانات المهمة')
                ->schema([
                    TextEntry::make('id')->label('رقم المهمة'),
                    TextEntry::make('project.project_name')->label('المشروع')->placeholder('—'),
                    TextEntry::make('department.dept_name')->label('القسم')->placeholder('—'),
                    TextEntry::make('employee.employee_name')->label('المسؤول')->placeholder('—'),
                    TextEntry::make('estimated_cost')->label('الميزانية المتوقعة')->placeholder('—')
                        ->visible(fn () => ! auth()->user()?->hasRole('department_manager')),
                    TextEntry::make('status')->label('الحالة')->placeholder('—'),
                    TextEntry::make('due_date')->label('تاريخ التسليم')->date()->placeholder('—'),
                    TextEntry::make('assigned_at')->label('تاريخ الإسناد')->dateTime()->placeholder('—'),
                ])->columns(2),

            Section::make('الخط الزمني')
                ->schema([
                    RepeatableEntry::make('logs_list')
                        ->label('سجل العمليات')
                        // غذِّ RepeatableEntry بمصفوفة من السجلات
                        ->state(fn ($record) => $record->logs->map(fn ($log) => [
                            'happened_at' => $log->happened_at ?? $log->created_at,
                            'type'        => $log->type,
                            'status'      => is_array($log->data ?? null)
                                ? ($log->data['to'] ?? $log->data['status'] ?? null)
                                : null,
                        ])->toArray())
                        ->schema([
                            TextEntry::make('happened_at')->label('التاريخ')->dateTime(),
                            TextEntry::make('type')->label('الحدث')->badge(),
                            TextEntry::make('status')->label('الحالة الجديدة')->placeholder('—'),
                        ])
                        ->columns(4),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'active'    => ActiveTasks::route('/active'),
            'returned'  => ReturnedToFactory::route('/returned-to-factory'),
            'completed' => CompletedTasks::route('/completed'),
            'index'     => ListTasks::route('/'),
            'view'      => ViewTask::route('/{record}'),

        ];
    }
}
