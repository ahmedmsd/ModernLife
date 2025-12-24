<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TaskResource;
use App\Models\Department;
use App\Models\ProductionTask;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class InProgressTasksSummary extends TableWidget
{
    protected static ?string $heading = 'ملخص المهام قيد التنفيذ';
    protected int|string|array $columnSpan = 'full';

    /**
     * Statuses to exclude (completed/closed tasks)
     */
    protected array $excludedStatuses = [
        'completed',
        'closed',
        'cancelled',
        'rejected',
    ];

    /**
     * Owner role labels in Arabic
     */
    protected array $ownerRoleLabels = [
        'factory_manager'     => 'مدير المصنع',
        'department_manager'  => 'مدير القسم',
        'employee'            => 'موظف',
        'purchasing_manager'  => 'مدير المشتريات',
        'quality_manager'     => 'مدير الجودة',
        'installation_manager'=> 'مدير التركيب',
    ];

    /**
     * Status labels in Arabic
     */
    protected array $statusLabels = [
        'pending'            => 'بالانتظار',
        'waiting_production' => 'في انتظار بدء التصنيع',
        'in_progress'        => 'جاري التنفيذ',
        'under_review'       => 'قيد المراجعة',
        'approved'           => 'معتمد',
        'rejected'           => 'مرفوض',
        'rework'             => 'إعادة عمل',
        'on_hold'            => 'موقوف مؤقتاً',
        'materials_wait'     => 'بانتظار الخامات',
        'materials_prep'     => 'تجهيز الخامات',
        'materials_done'     => 'الخامات جاهزة',
        'qa_approved'        => 'معتمد من الجودة',
        'received'           => 'تم الاستلام',
        'returned_to_factory'=> 'مرتجع للمصنع',
        'qa_ack_manufacturing' => 'قيد الفحص (تصنيع)',
        'qa_ack_installation'  => 'قيد الفحص (تركيب)',
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query($this->baseQuery())
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('project.project_name')
                    ->label('المشروع')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('department_label')
                    ->label('القسم')
                    ->state(function (ProductionTask $record) {
                        $dept = $record->department;
                        if (! $dept) {
                            return '—';
                        }
                        return $dept->dept_name
                            ?? $dept->name
                            ?? $dept->title
                            ?? ('قسم #' . ($dept->dept_id ?? $dept->id ?? '?'));
                    })
                    ->toggleable(),

                TextColumn::make('current_owner_role')
                    ->label('المالك الحالي')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $this->ownerRoleLabels[$state] ?? $state ?? '—')
                    ->color(fn (?string $state) => match ($state) {
                        'factory_manager'     => 'primary',
                        'department_manager'  => 'info',
                        'purchasing_manager'  => 'warning',
                        'quality_manager'     => 'success',
                        'installation_manager'=> 'danger',
                        default               => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $this->statusLabels[$state] ?? $state ?? '—')
                    ->color(fn (?string $state) => match ($state) {
                        'completed', 'closed'  => 'success',
                        'in_progress'          => 'primary',
                        'under_review'         => 'info',
                        'pending', 'waiting_production' => 'warning',
                        'rework', 'rejected'   => 'danger',
                        'on_hold'              => 'gray',
                        'materials_wait', 'materials_prep', 'materials_done' => 'warning',
                        default                => 'secondary',
                    })
                    ->sortable(),

                TextColumn::make('delay_days_calc')
                    ->label('أيام التأخير')
                    ->state(function ($record) {
                        $days = (int) $record->delay_days_calc;
                        if ($days === 0 && $record->delay_type === 'unknown') {
                            return '—';
                        }
                        return $days . ' يوم';
                    })
                    ->color(fn ($record) => match (true) {
                        ((int) $record->delay_days_calc) >= 7 => 'danger',
                        ((int) $record->delay_days_calc) >= 3 => 'warning',
                        default => 'gray',
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('delay_days_calc', $direction);
                    }),

                TextColumn::make('delay_type')
                    ->label('نوع التأخير')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'overdue'            => 'تجاوز الموعد',
                        'waiting_owner'      => 'انتظار المالك',
                        'waiting_assignment' => 'منذ الإسناد',
                        default              => '—',
                    })
                    ->color(fn ($state) => match ($state) {
                        'overdue'            => 'danger',
                        'waiting_owner'      => 'warning',
                        'waiting_assignment' => 'info',
                        default              => 'gray',
                    }),
                    // ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('due_date')
                    ->label('تاريخ التسليم')
                    ->date()
                    ->placeholder('—')
                    ->sortable(),
                    // ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('delay_days_calc', 'desc')
            ->filters([
                SelectFilter::make('department_id')
                    ->label('القسم')
                    ->options(function () {
                        return Department::query()
                            ->get()
                            ->mapWithKeys(function ($dept) {
                                $label = $dept->dept_name
                                    ?? $dept->name
                                    ?? $dept->title
                                    ?? ('قسم #' . ($dept->dept_id ?? $dept->id ?? '?'));
                                $key = $dept->dept_id ?? $dept->id;
                                return [$key => $label];
                            })
                            ->toArray();
                    }),

                SelectFilter::make('current_owner_role')
                    ->label('المالك الحالي')
                    ->options($this->ownerRoleLabels),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(array_filter($this->statusLabels, fn ($key) => 
                        ! in_array($key, $this->excludedStatuses), ARRAY_FILTER_USE_KEY
                    )),

                Filter::make('delayed_3_plus')
                    ->label('متأخرة ٣ أيام فأكثر')
                    ->toggle()
                    ->query(fn (Builder $query) => $query->having('delay_days_calc', '>=', 3)),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(' ')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ProductionTask $record) => TaskResource::getUrl('view', ['record' => $record])),
            ])
            ->paginationPageOptions([25, 50, 100]);
    }

    /**
     * Build the base query with delay calculations
     */
    protected function baseQuery(): Builder
    {
        $table = (new ProductionTask())->getTable();

        return ProductionTask::query()
            ->select("{$table}.*")
            ->with([
                'project',
                'department',
                'currentOwnerUser',
            ])
            ->addSelect([
                // Delay days calculation with priority: due_date > sent_to_owner_at > assigned_at
                DB::raw("
                    CASE
                        WHEN {$table}.due_date IS NOT NULL AND {$table}.due_date < CURDATE()
                            THEN DATEDIFF(CURDATE(), {$table}.due_date)
                        WHEN {$table}.sent_to_owner_at IS NOT NULL
                            THEN DATEDIFF(CURDATE(), DATE({$table}.sent_to_owner_at))
                        WHEN {$table}.assigned_at IS NOT NULL
                            THEN DATEDIFF(CURDATE(), DATE({$table}.assigned_at))
                        ELSE 0
                    END AS delay_days_calc
                "),
                // Delay type indicator
                DB::raw("
                    CASE
                        WHEN {$table}.due_date IS NOT NULL AND {$table}.due_date < CURDATE()
                            THEN 'overdue'
                        WHEN {$table}.sent_to_owner_at IS NOT NULL
                            THEN 'waiting_owner'
                        WHEN {$table}.assigned_at IS NOT NULL
                            THEN 'waiting_assignment'
                        ELSE 'unknown'
                    END AS delay_type
                "),
            ])
            ->whereNotIn("{$table}.status", $this->excludedStatuses);
    }

    /**
     * Get unique record key for table
     */
    public function getTableRecordKey(mixed $record): string
    {
        if (method_exists($record, 'getKey')) {
            $key = $record->getKey();
        } else {
            $keyName = method_exists($record, 'getKeyName') ? $record->getKeyName() : 'id';
            $key = $record->{$keyName} ?? null;
        }

        if ($key === null) {
            $key = $record->id ?? $record->uuid ?? spl_object_id($record);
        }

        return (string) $key;
    }
}
