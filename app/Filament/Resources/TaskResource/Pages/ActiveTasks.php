<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\ProductionTask;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;

class ActiveTasks extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = TaskResource::class;
    protected static ?string $title   = 'عرض المهام الجارية';
    protected static string $view     = 'filament.pages.blank';

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->check();
    }

    public function table(Table $table): Table
    {
        $statusAr = fn (?string $state) => [
            'pending' => 'قيد الإنشاء',
            'assigned' => 'مُسندة',
            'acknowledged' => 'تأكيد الاستلام',
            'in_progress' => 'قيد التنفيذ',
            'blocked' => 'متوقفة',
            'under_review' => 'قيد المراجعة',
            'rework' => 'إعادة عمل',
            'completed' => 'مكتملة',
            'closed' => 'مغلقة',
            'cancelled' => 'ملغاة',
            'draft' => 'مسودة',
        ][$state] ?? ($state ?? '—');

        $statusColor = fn (?string $state) => match ($state) {
            'pending', 'draft'         => 'gray',
            'assigned', 'acknowledged' => 'warning',
            'in_progress'              => 'info',
            'blocked', 'rework'        => 'purple',
            'under_review'             => 'cyan',
            'completed', 'closed'      => 'success',
            'cancelled'                => 'danger',
            default                    => 'secondary',
        };

        return $table
            ->heading('كل المهام الجارية')
            ->query(
                ProductionTask::query()
                    ->whereNotIn('status', ['completed','closed','cancelled'])
                    ->with(['project:id,project_name','department:dept_id,dept_name','employee:employee_id,employee_name'])
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('department.dept_name')->label('القسم')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('employee.employee_name')->label('المسؤول')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')->badge()
                    ->formatStateUsing($statusAr)
                    ->color($statusColor),
                Tables\Columns\TextColumn::make('due_date')->label('تاريخ التسليم')->date()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('أُنشئت')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'=>'قيد الإنشاء','assigned'=>'مُسندة','acknowledged'=>'تأكيد الاستلام',
                        'in_progress'=>'قيد التنفيذ','blocked'=>'متوقفة','under_review'=>'قيد المراجعة','rework'=>'إعادة عمل',
                    ]),
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'dept_name'),
                Tables\Filters\SelectFilter::make('assigned_to_employee_id')
                    ->label('المسؤول')
                    ->relationship('employee', 'employee_name')
                    ->searchable(),
                Filter::make('period')
                    ->label('الفترة (تاريخ الإنشاء)')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('من')->native(false),
                        Forms\Components\DatePicker::make('to')->label('إلى')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from = $data['from'] ?? null;
                        $to   = $data['to'] ?? null;
                        return $query
                            ->when($from, fn (Builder $q) => $q->whereDate('created_at', '>=', $from))
                            ->when($to,   fn (Builder $q) => $q->whereDate('created_at', '<=', $to));
                    }),
            ])
            ->recordUrl(fn (ProductionTask $record) => TaskResource::getUrl('view', ['record' => $record]))
            ->actions([
                Tables\Actions\Action::make('view')->label('عرض')->icon('heroicon-o-eye')
                    ->url(fn (ProductionTask $record) => TaskResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated([25, 50, 100])
            ->emptyStateHeading('لا توجد مهام جارية حالياً');
    }
}
