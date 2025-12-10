<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\ProductionTask;
use App\Models\User;
use App\Notifications\TaskSentForConfirmation;
use App\Services\Tasks\Workflow\AssignmentWorkflowService;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Forms;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ReturnedToFactory extends ListRecords
{
    protected static string $resource = TaskResource::class;

    public function getTitle(): string
    {
        return 'المهام المرفوضة (مطلوب تعديل)';
    }

    /**
     * Build the table for this page and modify its query using roles-based filtering.
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->actions($this->getTableActions())
            ->bulkActions($this->getTableBulkActions())
            ->modifyQueryUsing(function (Builder $query) {
                // عرض فقط المهام التي حالتها returned_to_factory
                $query->where('status', 'returned_to_factory');

                $user = auth()->user();
                if (! $user) {
                    $query->whereRaw('1 = 0');
                    return;
                }

                $superRoles = ['admin', 'super-admin', 'factory_manager'];
                if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($superRoles)) {
                    return;
                }

                if (method_exists($user, 'hasRole') && $user->hasRole('showroom_manager')) {
                    $query->whereHas('project.productionRequest.showroom', function (Builder $q) use ($user) {
                        $q->where('manager_id', $user->id);
                    });
                    return;
                }

                if (method_exists($user, 'hasRole') && $user->hasRole('sales')) {
                    $query->whereHas('project.productionRequest', function (Builder $q) use ($user) {
                        $q->where('created_by', $user->id);
                    });
                    return;
                }

                // خلاف ذلك: لا شيء
                $query->whereRaw('1 = 0');
            });
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
            Tables\Columns\TextColumn::make('project.project_name')->label('المشروع')->limit(40)->sortable(),
            Tables\Columns\TextColumn::make('project.productionRequest.showroom.name')->label('المعرض')->limit(30)->toggleable(),
            Tables\Columns\TextColumn::make('project.productionRequest.creator.name')->label('مقدم الطلب')->limit(24)->toggleable(),
            Tables\Columns\TextColumn::make('status')->label('الحالة')->badge(),
            Tables\Columns\TextColumn::make('notes')->label('ملاحظات')->limit(60)->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('sent_to_owner_at')->label('تم الإرسال بتاريخ')->dateTime()->toggleable(),
            Tables\Columns\TextColumn::make('updated_at')->label('آخر تحديث')->dateTime()->toggleable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\Filter::make('showroom')
                ->label('المعرض')
                ->form([
                    Forms\Components\Select::make('showroom_id')
                        ->label('المعرض')
                        ->options(\App\Models\Showroom::pluck('name', 'id'))
                        ->searchable(),
                ])
                ->query(function (Builder $query, array $data) {
                    if (! empty($data['showroom_id'])) {
                        $query->whereHas('project.productionRequest', function (Builder $q) use ($data) {
                            $q->where('showroom_id', $data['showroom_id']);
                        });
                    }
                }),

            Tables\Filters\SelectFilter::make('department_id')
                ->label('القسم')
                ->options(\App\Models\Department::where('dept_type', '5')->pluck('dept_name','dept_id')->toArray()),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('view')
                ->label('عرض')
                ->icon('heroicon-m-eye')
                ->url(fn (ProductionTask $record) => TaskResource::getUrl('view', ['record' => $record]))
                ->openUrlInNewTab(),

            Tables\Actions\EditAction::make('edit')
                ->modalHeading('تعديل مهمة (مرفوضة من القسم)')
                ->form($this->getEditFormSchema())
                ->after(function (ProductionTask $record, array $data): void {
                    // حفظ الحقول المعدلة
                    $record->fill(array_filter([
                        'estimated_cost' => $data['estimated_cost'] ?? $record->estimated_cost,
                        'due_date'       => $data['due_date'] ?? $record->due_date,
                        'notes'          => $data['notes'] ?? $record->notes,
                        'department_id'  => $data['department_id'] ?? $record->department_id,
                    ]))->save();

                    // Use AssignmentWorkflowService
                    try {
                        /** @var AssignmentWorkflowService $workflow */
                        $workflow = app(AssignmentWorkflowService::class);
                        $workflow->resubmitToDeptManager($record, 'إعادة إرسال الى مدير القسم بعد تعديل من قبل البائع/المعرض');
                    } catch (\Throwable $e) {
                         Log::error('AssignmentWorkflowService::resubmitToDeptManager failed: ' . $e->getMessage());
                         // Manual Fallback if service fails (though unlikely with simple code)
                         $dept = $record->department()->first();
                         $deptManagerId = $dept->manager_user_id ?? $dept->manager_id ?? null;
                         
                         $record->forceFill([
                            'current_owner_role'    => 'department_manager',
                            'current_owner_user_id' => $deptManagerId,
                            'sent_to_owner_at'      => now(),
                             'received_by_owner_at'  => null,
                         ])->save();
                    }

                    try {
                        $record->logs()->create([
                            'type'        => 'resubmitted_to_dept_after_return',
                            'data'        => [
                                'by_user' => auth()->id(),
                                'from'    => 'returned_to_factory_page',
                            ],
                            'note'        => 'تم تعديل المهمة وإعادة إرسالها لمدير القسم بواسطة مقدم الطلب/المعرض.',
                            'causer_id'   => auth()->id(),
                            'happened_at' => now(),
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('Failed to create task log: ' . $e->getMessage());
                    }

                    // إشعار داخلي لمدير القسم (database notification)
                    if ($deptManagerId) {
                        $user = User::find($deptManagerId);
                        if ($user) {
                            try {
                                $url = TaskResource::getUrl('view', ['record' => $record]);
                                $user->notify(new TaskSentForConfirmation($record, $url));
                            } catch (\Throwable $e) {
                                Log::warning('Failed to notify dept manager: ' . $e->getMessage());
                            }
                        }
                    }
                }),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            Tables\Actions\DeleteBulkAction::make(),
        ];
    }


    protected function getEditFormSchema(): array
    {
        return [
            Forms\Components\FileUpload::make('file_path')
                ->label('ملف المهمة')
                ->directory('production_files/' . now()->format('Y/m'))
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

            Forms\Components\Select::make('department_id')
                ->relationship('department', 'dept_name')
                ->label('القسم')
                ->options(\App\Models\Department::where('dept_type', '5')->pluck('dept_name', 'dept_id'))
                ->required(),
        ];
    }


    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();
        return $user && method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['showroom_manager','sales','super-admin','factory_manager']);
    }
}
