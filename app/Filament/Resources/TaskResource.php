<?php

namespace App\Filament\Resources;

use App\Models\ProductionTask;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Icon;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = ProductionTask::class;

    protected static ?string $navigationIcon = 'heroicon-m-clipboard-document-check';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['project', 'department', 'employee', 'logs']);
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
                    TextEntry::make('project.project_name')->label('المشروع'),
                    TextEntry::make('department.dept_name')->label('القسم'),
                    TextEntry::make('employee.employee_name')->label('الموظف المسؤول'),
                    TextEntry::make('status')->label('الحالة'),
                    TextEntry::make('due_date')->label('تاريخ التسليم')->date(),
                    TextEntry::make('assigned_at')->label('تاريخ الإسناد')->dateTime(),
                ])->columns(2),

            Section::make('الخط الزمني')
                ->schema([
                    RepeatableEntry::make('logs')
                        ->label('سجل العمليات')
                        ->schema([
                            TextEntry::make('happened_at')
                                ->label('التاريخ')
                                ->dateTime(),

                            TextEntry::make('type')
                                ->label('الحدث')
                                ->badge(),

                            TextEntry::make('data.status')
                                ->label('الحالة الجديدة')
                                ->placeholder('—'),

                            TextEntry::make('duration')
                                ->label('المدة في هذه الحالة')
                                ->formatStateUsing(fn($state, $record, $all) => function () use ($record, $all) {
                                    // جلب الحدث التالي
                                    $next = $all->firstWhere('id', $record->id + 1);
                                    if (!$next) {
                                        return now()->diffForHumans($record->happened_at, true);
                                    }
                                    return $record->happened_at->diffForHumans($next->happened_at, true);
                                }),
                        ])
                        ->columns(4),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => TaskResource\Pages\ListTasks::route('/'),
            'view' => TaskResource\Pages\ViewTask::route('/{record}'),
        ];
    }
}
