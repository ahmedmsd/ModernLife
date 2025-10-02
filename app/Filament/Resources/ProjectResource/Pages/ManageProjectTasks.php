<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Enums\PhaseStatus;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\TaskResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;

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

    public function form(Form $form): Form
    {
        return $form->schema($this->getCreateFormSchema());
    }

    /** نموذج الإنشاء */
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
                ->directory(fn () => "projects/{$this->getOwnerRecord()->id}/tasks")
                ->preserveFilenames()
                ->helperText('يمكنك رفع ملف جديد للمهمة أو الإبقاء على الملف المنشأ تلقائيًا.')
                ->nullable(),

            Forms\Components\Select::make('assigned_to_employee_id')
                ->relationship('employee', 'employee_name')
                ->label('الموظف المسؤول')
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\TextInput::make('estimated_cost')
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
                ->options(self::statusOptions())
                ->default(PhaseStatus::Pending->value)
                ->required()
                ->native(false)
                ->searchable(),
        ];
    }

    /** نموذج التعديل */
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
                ->directory(fn () => "projects/{$this->getOwnerRecord()->id}/tasks")
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
                ->options(self::statusOptions())
                ->required()
                ->native(false)
                ->searchable(),

            Forms\Components\TextInput::make('estimated_cost')
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

                Tables\Columns\TextColumn::make('estimated_cost')
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
                    ->icon(fn ($state) => self::statusIcon($state))
                    ->color(fn ($state) => self::statusColor($state))
                    ->formatStateUsing(fn ($state) => self::statusLabel($state)),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(self::statusOptions())
                    ->multiple(),
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
                    ->url(fn ($record) => TaskResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),

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

    /** خيارات الحالات [value => label] */
    protected static function statusOptions(): array
    {
        $out = [];
        foreach (PhaseStatus::cases() as $case) {
            $out[$case->value] = $case->label();
        }
        return $out;
    }

    /** إرجاع Enum من أي قيمة واردة (Enum أو string) */
    protected static function coerce(?string $state): ?PhaseStatus
    {
        if ($state instanceof PhaseStatus) {
            return $state;
        }
        return $state !== null ? PhaseStatus::tryFrom((string) $state) : null;
    }

    /** وسم عربي للحالة */
    protected static function statusLabel($state): string
    {
        $e = $state instanceof PhaseStatus ? $state : PhaseStatus::tryFrom((string) $state);
        return $e?->label() ?? (string) $state;
    }

    /** لون الشارة */
    protected static function statusColor($state): string
    {
        $e = $state instanceof PhaseStatus ? $state : PhaseStatus::tryFrom((string) $state);

        return match ($e) {
            PhaseStatus::Pending        => 'gray',
            PhaseStatus::Received       => 'primary',
            PhaseStatus::UnderReview    => 'warning',
            PhaseStatus::Approved       => 'success',
            PhaseStatus::Rejected       => 'danger',
            PhaseStatus::InProgress     => 'primary',
            PhaseStatus::MaterialsWait  => 'warning',
            PhaseStatus::MaterialsPrep  => 'info',
            PhaseStatus::MaterialsDone  => 'success',
            PhaseStatus::OnHold         => 'gray',
            PhaseStatus::Completed      => 'success',
            PhaseStatus::Cancelled      => 'danger',
            default                     => 'secondary',
        };
    }

    /** أيقونة لكل حالة (Heroicons) */
    protected static function statusIcon($state): ?string
    {
        $e = $state instanceof PhaseStatus ? $state : PhaseStatus::tryFrom((string) $state);

        return match ($e) {
            PhaseStatus::Pending        => 'heroicon-o-ellipsis-horizontal-circle',
            PhaseStatus::Received       => 'heroicon-o-inbox-arrow-down',
            PhaseStatus::UnderReview    => 'heroicon-o-eye',
            PhaseStatus::Approved       => 'heroicon-o-check-circle',
            PhaseStatus::Rejected       => 'heroicon-o-x-circle',
            PhaseStatus::InProgress     => 'heroicon-o-play',
            PhaseStatus::MaterialsWait  => 'heroicon-o-clock',
            PhaseStatus::MaterialsPrep  => 'heroicon-o-wrench-screwdriver',
            PhaseStatus::MaterialsDone  => 'heroicon-o-check-badge',
            PhaseStatus::OnHold         => 'heroicon-o-pause-circle',
            PhaseStatus::Completed      => 'heroicon-o-check-badge',
            PhaseStatus::Cancelled      => 'heroicon-o-no-symbol',
            default                     => null,
        };
    }
}
