<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class ManageProjectTasks extends ManageRelatedRecords
{
    protected static string $resource     = ProjectResource::class;
    protected static string $relationship = 'tasks';

    public function getTitle(): string
    {
        return 'مهام التصنيع للمشروع';
    }

    public static function canAccess(array $parameters = []): bool
    {
        return \Illuminate\Support\Facades\Auth::user()?->can('access_manage_project_tasks');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('department_id')
                ->relationship('department', 'dept_name')
                ->label('القسم')
                ->options(
                    \App\Models\Department::where('dept_type', '5')->pluck('dept_name', 'dept_id')
                )
                ->searchable()
                ->required(),

            Forms\Components\FileUpload::make('file_path')
                ->label('ملف المهمة')
                ->directory(fn () => "projects/{$this->getOwnerRecord()->id}/tasks")
                ->preserveFilenames()
                ->helperText('يمكنك رفع ملف جديد للمهمة أو الإبقاء على الملف المنشأ تلقائيًا.')
                ->nullable(), // نجعلها اختيارية لأن المهام التلقائية قد تحتوي مسار ملف موجود مسبقًا

            Forms\Components\Select::make('assigned_to_employee_id')
                ->relationship('employee', 'employee_name')
                ->label('الموظف المسؤول')
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\TextInput::make('assigned_budget')
                ->label('الميزانية المتوقعة')
                ->numeric()
                ->nullable(),

            Forms\Components\DatePicker::make('due_date')
                ->label('تاريخ التسليم المتوقع')
                ->native(false)
                ->nullable(),

            Forms\Components\Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(3)
                ->nullable(),

            Forms\Components\Select::make('status')
                ->label('الحالة')
                ->options([
                    'draft'    => 'مسودة',
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
                Tables\Columns\TextColumn::make('department.dept_name')->label('القسم')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('employee.employee_name')->label('الموظف المسؤول')->toggleable(),
                Tables\Columns\TextColumn::make('assigned_budget')
                    ->label('الميزانية المتوقعة')
                    ->money('SAR')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('due_date')->label('تاريخ التسليم')->date()->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft'   => 'info',
                        'assigned'   => 'primary',
                        'completed'   => 'success',
                        'in_progress' => 'warning',
                        default       => 'gray',
                    }),
                Tables\Columns\TextColumn::make('notes')->label('ملاحظات')->limit(50)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة مهمة جديدة')
                    ->modalHeading('إضافة مهمة تصنيع')
                    ->modalSubmitActionLabel('حفظ المهمة')
                    ->modalCancelActionLabel('إلغاء'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('لا توجد مهام تصنيع')
            ->emptyStateDescription('قم بإضافة مهمة تصنيع جديدة للبدء.')
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
