<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\ProductionTask;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Support\Carbon;

class CompletedTasks extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = TaskResource::class;
    protected static ?string $title   = 'المهام المنجزة';
    protected static string $view     = 'filament.pages.blank';

//    public static function canAccess(array $parameters = []): bool
//    {
//        return auth()->check();
//    }

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    private function statusAr(?string $s): string
    {
        return match ($s) {
            'completed' => 'مكتملة',
            'closed'    => 'مغلقة',
            default     => ($s ?: '—'),
        };
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('كل المهام المنجزة')
            ->query(
                ProductionTask::query()
                    ->whereIn('status', ['completed','closed'])
                    ->with([
                        'project:id,project_name',
                        'department:dept_id,dept_name',
                        'employee:employee_id,employee_name',
                    ])
                    ->orderByDesc('closed_at')
                    ->orderByDesc('updated_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('department.dept_name')->label('القسم')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('employee.employee_name')->label('المسؤول')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn (?string $s) => $this->statusAr($s))
                    ->color(fn (?string $s) => match ($s) { 'completed'=>'success','closed'=>'gray', default=>'secondary' })
                    ->sortable(),
                Tables\Columns\TextColumn::make('actual_start_at')->label('بدأ فعلي')->dateTime('Y-m-d')->toggleable(),
                Tables\Columns\TextColumn::make('actual_end_at')->label('انتهى فعلي')->dateTime('Y-m-d')->toggleable(),
                Tables\Columns\TextColumn::make('closed_at')->label('تاريخ الإقفال')->dateTime('Y-m-d H:i')->sortable(),
                Tables\Columns\TextColumn::make('duration')->label('المدة')->state(function (ProductionTask $r) {
                    $start = $r->created_at instanceof Carbon ? $r->created_at : ($r->actual_start_at ?? $r->created_at);
                    $end   = $r->closed_at   instanceof Carbon ? $r->closed_at   : ($r->actual_end_at   ?? $r->closed_at);
                    if (!$start || !$end) return '—';
                    $sec = max(0, $start->diffInSeconds($end));
                    return Carbon::now()->subSeconds($sec)->diffForHumans(null, true);
                })->sortable(query: fn($q,$dir) => $q->orderBy('closed_at',$dir)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')->label('القسم')
                    ->relationship('department','dept_name'),
                Tables\Filters\SelectFilter::make('assigned_to_employee_id')->label('المسؤول')
                    ->relationship('employee','employee_name'),
                Tables\Filters\Filter::make('date_range')->label('نطاق تاريخ الإقفال')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('من')->native(false),
                        \Filament\Forms\Components\DatePicker::make('to')->label('إلى')->native(false),
                    ])
                    ->query(function ($q, array $d) {
                        if (!empty($d['from'])) $q->whereDate('closed_at', '>=', $d['from']);
                        if (!empty($d['to']))   $q->whereDate('closed_at', '<=', $d['to']);
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')->label('عرض')->icon('heroicon-o-eye')
                    ->url(fn(ProductionTask $r) => \App\Filament\Resources\TaskResource::getUrl('view', ['record'=>$r]))
                    ->openUrlInNewTab(),
            ])
            ->paginated([25, 50, 100]);
    }
}
