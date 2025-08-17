<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Schemas\Schema;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel = 'مهام التصنيع';
    protected static ?string $pluralModelLabel = 'مهام التصنيع';
    protected static ?string $modelLabel = 'مهمة تصنيع';
    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('department_id')
                ->relationship('department', 'dept_name')
                ->label('القسم')
                ->required(),

            Forms\Components\FileUpload::make('file_path')
                ->label('ملف المهمة')
                ->directory('projects/{record}/tasks')
                ->preserveFilenames()
                ->required(),

            Forms\Components\Select::make('assigned_to_employee_id')
                ->relationship('employee', 'name')
                ->label('الموظف المسؤول')
                ->required(),

            Forms\Components\DatePicker::make('due_date')
                ->label('تاريخ التسليم')
                ->required(),

            Forms\Components\Textarea::make('notes')
                ->label('ملاحظات'),

            Forms\Components\Select::make('status')
                ->label('الحالة')
                ->options([
                    'assigned'    => 'موزعة',
                    'in_progress' => 'قيد التنفيذ',
                    'completed'   => 'مكتملة',
                ])
                ->default('assigned')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('department.dept_name')->label('القسم'),
                TextColumn::make('employee.employee_name')->label('المسؤول'),
                TextColumn::make('due_date')->label('تاريخ التسليم')->date(),
                TextColumn::make('status')->label('الحالة')->badge(),
                TextColumn::make('notes')->label('ملاحظات')->limit(50),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة مهمة جديدة'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
