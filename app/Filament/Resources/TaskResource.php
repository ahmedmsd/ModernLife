<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\ProductionTask;
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

    public static function getEloquentQuery(): Builder
    {
        // احذف أي علاقة غير موجودة (مثل comments لو مش معرّفة)
        return parent::getEloquentQuery()
            ->with(['project','department','employee','logs']);
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
            'index'     => Pages\ListTasks::route('/'),
            'view'      => Pages\ViewTask::route('/{record}'),
            'active'    => Pages\ActiveTasks::route('/active'),
            'completed' => Pages\CompletedTasks::route('/completed'),
        ];
    }
}
