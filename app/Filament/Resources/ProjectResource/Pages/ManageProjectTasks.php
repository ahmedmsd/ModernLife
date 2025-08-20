<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use App\Enums\TaskStatus;
use App\Filament\Resources\TaskResource;

class ManageProjectTasks extends ManageRelatedRecords
{
    protected static string $resource = ProjectResource::class;
    protected static string $relationship = 'tasks';
    protected array $casts = ['status' => \App\Enums\TaskStatus::class];

    public function getTitle(): string
    {
        return 'مهام التصنيع للمشروع';
    }

    public static function canAccess(array $parameters = []): bool
    {
        return \Illuminate\Support\Facades\Auth::user()?->can('access_manage_project_tasks');
    }


    public function form(Form $form): Form
    {
        return $form->schema($this->getCreateFormSchema());
    }

    /** نموذج الإنشاء فقط (يحتوي حقل الحالة) */
    protected function getCreateFormSchema(): array
    {
        return [
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
                ->directory(fn() => "projects/{$this->getOwnerRecord()->id}/tasks")
                ->preserveFilenames()
                ->helperText('يمكنك رفع ملف جديد للمهمة أو الإبقاء على الملف المنشأ تلقائيًا.')
                ->nullable(),

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
                ->options(TaskStatus::options())
                ->default(TaskStatus::Pending->value)
                ->required()
                ->native(false)
                ->searchable(),
        ];
    }

    /** نموذج التعديل فقط (بدون حقل الحالة) */
    protected function getEditFormSchema(): array
    {
        return [
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
                ->directory(fn() => "projects/{$this->getOwnerRecord()->id}/tasks")
                ->preserveFilenames()
                ->helperText('يمكنك رفع ملف جديد للمهمة أو الإبقاء على الملف المنشأ تلقائيًا.')
                ->nullable(),

            Forms\Components\Select::make('assigned_to_employee_id')
                ->relationship('employee', 'employee_name')
                ->label('الموظف المسؤول')
                ->searchable()
                ->preload()
                ->nullable(),
            Forms\Components\Select::make('status')
                ->label('الحالة')
                ->options(TaskStatus::options())
                ->default(TaskStatus::Assigned->value)
                ->required()
                ->native(false)
                ->searchable(),
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

        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('department.dept_name')
                    ->label('القسم')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('employee.employee_name')
                    ->label('الموظف المسؤول')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('assigned_budget')
                    ->label('الميزانية المتوقعة')
                    ->money('SAR')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('تاريخ التسليم المتوقع')
                    ->date()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->icon(fn($record) => $record->status instanceof TaskStatus
                        ? $record->status->icon()
                        : TaskStatus::from($record->status)->icon())
                    ->color(fn($record) => $record->status instanceof TaskStatus
                        ? $record->status->color()
                        : TaskStatus::from($record->status)->color())
                    ->formatStateUsing(fn($state) => $state instanceof TaskStatus
                        ? $state->getLabel()
                        : TaskStatus::from($state)->getLabel()),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(TaskStatus::options()),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة مهمة جديدة')
                    ->modalHeading('إضافة مهمة تصنيع')
                    ->modalSubmitActionLabel('حفظ المهمة')
                    ->modalCancelActionLabel('إلغاء')
                    ->form($this->getCreateFormSchema()),
            ])
            ->actions([
                Tables\Actions\Action::make('viewTask')
                    ->label('عرض')
                    ->icon('heroicon-m-eye')
                    ->url(fn($record) => route('filament.admin.resources.tasks.view', $record)),

                Tables\Actions\EditAction::make()
                    ->modalHeading('تعديل مهمة تصنيع')
                    ->form($this->getEditFormSchema()),

                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('لا توجد مهام تصنيع')
            ->emptyStateDescription('قم بإضافة مهمة تصنيع جديدة للبدء.')
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
