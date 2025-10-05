<?php

namespace App\Filament\Pages\Purchasing;

use App\Models\MaterialRequest;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class MaterialsRequestsDone extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'طلبات الخامات المُنجزة';
    protected static ?string $title           = 'طلبات الخامات (المُنجزة)';
    protected static ?string $slug            = 'purchasing/materials-requests-done';
    protected static ?string $navigationGroup = 'المشتريات';
    protected static ?int    $navigationSort  = 10;

    protected static string $view = 'filament.pages.purchasing.materials-requests-done';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['purchasing_manager','admin','super-admin']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) MaterialRequest::query()
            ->whereIn('status', ['fulfilled'])
            ->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('طلبات خامات المنجزة')
            ->query(
                MaterialRequest::query()
                    ->whereIn('status', ['fulfilled'])
                    ->with([
                        'task.project.productionRequest',
                        'department',
                        'requestedBy',
                    ])
            )
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),

                TextColumn::make('task.id')
                    ->label('المهمة')
                    ->sortable(),

                TextColumn::make('department.dept_name')
                    ->label('القسم')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('task.project.project_name')
                    ->label('المشروع')
                    ->searchable(),

                TextColumn::make('requester')
                    ->label('مقدّم الطلب')
                    ->state(fn (MaterialRequest $record) =>
                        ($record->requestedBy?->name)
                        ?? ($record->task?->employee?->employee_name)
                        ?? '—'
                    )
                    ->searchable(),

                TextColumn::make('note')
                    ->label('المطلوبات')
                    ->wrap()
                    ->limit(120),

                TextColumn::make('requested_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('expected_delivery_at')
                    ->label('موعد التوريد (متوقّع)')
                    ->dateTime('Y-m-d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'requested' => 'بانتظار اعتماد المشتريات',
                        'approved'  => 'بانتظار التوريد',
                        'fulfilled' => 'تم التوريد',
                        'cancelled' => 'ملغى',
                        default     => '—',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'requested' => 'warning',
                        'approved'  => 'info',
                        'fulfilled' => 'success',
                        'cancelled' => 'gray',
                        default     => 'secondary',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'dept_name'),

                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'requested' => 'بانتظار اعتماد المشتريات',
                        'approved'  => 'بانتظار التوريد',
                        'fulfilled'  => 'تم التوريد ',
                    ]),
            ])

            ->actions([

                Action::make('viewDetails')
                    ->label('عرض ')
                    ->icon('heroicon-o-eye')
                    ->url(fn (MaterialRequest $record) => ViewMaterialRequest::getUrl(['record' => $record])),
            ])
            ->emptyStateHeading('لا توجد طلبات خامات مُنجزة');
    }

    protected function notifyRole(string $roleName, string $title, string $body): void
    {
        try {
            $role = Role::findByName($roleName);
            foreach ($role->users as $user) {
                Notification::make()
                    ->title($title)
                    ->body($body)
                    ->sendToDatabase($user);
            }
        } catch (\Throwable $e) {
        }
    }
}
