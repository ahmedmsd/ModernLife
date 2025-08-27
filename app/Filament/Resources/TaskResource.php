<?php

namespace App\Filament\Resources;

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
    protected static ?string $navigationIcon = 'heroicon-m-clipboard-document-check';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['project','department','employee','logs','comments']);
    }

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\TaskResource\RelationManagers\CommentsRelationManager::class,
        ];
    }


    public static function infolist(Infolist $infolist): Infolist
    {
        // تبسيط: عرض بيانات أساسية + الخط الزمني الخام (اختياري)
        return $infolist->schema([
            Section::make('بيانات المهمة')
                ->schema([
                    TextEntry::make('id')->label('رقم المهمة'),
                    TextEntry::make('project.project_name')->label('المشروع')->placeholder('—'),
                    TextEntry::make('department.dept_name')->label('القسم')->placeholder('—'),
                    TextEntry::make('employee.employee_name')->label('الموظف المسؤول')->placeholder('—'),
                    TextEntry::make('estimated_cost')->label('الميزانية المتوقعة ')->placeholder('—'),
                    TextEntry::make('status')->label('الحالة')->placeholder('—'),
                    TextEntry::make('due_date')->label('تاريخ التسليم')->date()->placeholder('—'),
                    TextEntry::make('assigned_at')->label('تاريخ الإسناد')->dateTime()->placeholder('—'),
                ])->columns(2),

            Section::make('الخط الزمني')
                ->schema([
                    RepeatableEntry::make('logs')
                        ->label('سجل العمليات')
                        ->schema([
                            TextEntry::make('happened_at')->label('التاريخ')->dateTime(),
                            TextEntry::make('type')->label('الحدث')->badge(),
                            TextEntry::make('data.status')->label('الحالة الجديدة')->placeholder('—'),
                        ])->columns(4),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => TaskResource\Pages\ListTasks::route('/'),
            'view'  => TaskResource\Pages\ViewTask::route('/{record}'),
        ];
    }
}
