<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionRequestResource\Pages;
use App\Filament\Resources\ProductionRequestResource\Pages\CreateProductionRequest;
use App\Filament\Resources\ProductionRequestResource\Pages\EditProductionRequest;
use App\Filament\Resources\ProductionRequestResource\Pages\ListProductionRequests;
use App\Filament\Resources\ProductionRequestResource\Pages\ReviewProductionRequest;
use App\Filament\Resources\ProductionRequestResource\Pages\ViewProductionTimeline;
use App\Models\ProductionRequest;
use App\Support\Tenancy\RoleScope;
use App\Support\Tenancy\ShowroomFilter;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class ProductionRequestResource extends Resource
{
    protected static ?string $model = ProductionRequest::class;

    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'طلبات التصنيع';
    protected static ?string $navigationLabel = 'طلبات التصنيع';

    protected static ?string $recordTitleAttribute = 'project_name';
    protected static ?string $label       = 'إدارة الطلبات';
    protected static ?string $pluralLabel = ' الطلبات';
    protected static ?string $modelLabel  = 'طلب تصنيع';
    protected static bool $shouldRegisterNavigation = false;
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = parent::getEloquentQuery()
            ->with(['showroom','project','client'])
            ->latest('id');

        $user = auth()->user();
        if (! $user) {
            return $q->whereRaw('1 = 0');
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin','super-admin','owner'])) {
            return $q;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('sales')) {
            return $q->where('production_requests.created_by', $user->id);
        }

        if ($user->hasRole('showroom_manager')) {
            $employeeId = $user->employee?->getKey();

            if (! $employeeId) {
                return $q->whereRaw('1 = 0');
            }

            $showroomIds = \App\Models\Showroom::query()
                ->where('manager_id', $employeeId)
                ->pluck('id');

            if ($showroomIds->isEmpty()) {
                return $q->whereRaw('1 = 0');
            }

            return $q->where(function ($qq) use ($showroomIds, $user) {
                $qq->whereIn('production_requests.showroom_id', $showroomIds)
                    ->orWhere('production_requests.created_by', $user->id);
            });
        }

        return $q;
    }


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            // العميل
            Select::make('client_id')
                ->label('العميل')
                ->options(\App\Models\Client::pluck('client_name', 'client_id'))
                ->searchable()
                ->preload()
                ->required(),

            // اسم المشروع
            TextInput::make('project_name')
                ->label('اسم المشروع')
                ->required(),

            // نوع الطلب
            Select::make('request_type')
                ->label('نوع الطلب')
                ->options(function () {
                    $user = auth()->user();
                    $opts = [];

                    if ($user?->hasAnyRole(['sales', 'showroom_manager', 'admin', 'super-admin'])) {
                        $opts['direct'] = 'مباشر (من المبيعات للمصنع)';
                    }
                    if ($user?->hasAnyRole(['sales','showroom_manager', 'admin', 'super-admin'])) {
                        $opts['indirect'] = 'غير مباشر (عن طريق المعرض)';
                    }

                    return $opts ?: ['' => 'لا تملك صلاحية اختيار نوع الطلب'];
                })
                ->default(fn () => request('request_type', 'direct'))
                ->required()
                ->live(),

            Select::make('showroom_id')
                ->label('المعرض')
                ->options(\App\Models\Showroom::pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(fn (Get $get): bool => $get('request_type') === 'indirect')
                ->hidden(fn (Get $get): bool => $get('request_type') === 'direct'),

            Section::make('المرفقات')
                ->description('أرفق ملف الاتفاقية (PDF)، ثم أضف ملفات التصنيع حسب الأقسام المعنية.')
                ->schema([
                    Grid::make(12)->schema([

                        // ملف الاتفاقية
                        FileUpload::make('agreement_file')
                            ->label('ملف الاتفاقية (PDF)')
                            ->helperText('صيغة PDF فقط — حتى 20MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(20_480) // بالكيلوبايت
                            ->disk('public')
                            ->visibility('public')
                            ->directory('agreements/' . now()->format('Y/m'))
                            ->openable()
                            ->moveFiles()
                            ->downloadable()
                            ->columnSpan(['xl' => 4, 'lg' => 5, 'md' => 12]),

                        FileUpload::make('additional_work_file')
                            ->label('ملف الأعمال الإضافية (PDF)')
                            ->helperText('صيغة PDF فقط — حتى 20MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(20_480)
                            ->disk('public')
                            ->visibility('public')
                            ->directory('additional_work/' . now()->format('Y/m'))
                            ->openable()
                            ->moveFiles()
                            ->downloadable()
                            ->columnSpan(['xl' => 4, 'lg' => 5, 'md' => 12]),

                        // ملفات الأقسام
                        Repeater::make('files')
                            ->label('ملفات التصنيع للأقسام')
                            ->relationship('files')
                            ->addActionLabel('إضافة ملف قسم')
                            ->defaultItems(1)
                            ->columns(12) // شبكة العناصر داخل التكرار
                            ->reorderable(false)
                            ->cloneable(false)
                            // اعرض هذا العمود أعرض من عمود الاتفاقية
                            ->columnSpan(['xl' => 8, 'lg' => 7, 'md' => 12])
                            ->schema([
                                Select::make('department_id')
                                    ->label('القسم')
                                    ->options(
                                        \App\Models\Department::where('dept_type', '5')->pluck('dept_name', 'dept_id')
                                    )
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(4),

                                FileUpload::make('file_path')
                                    ->label('ملف القسم')
                                    ->helperText('يدعم الصور وملفات PDF — حتى 30MB')
                                    ->acceptedFileTypes(['application/pdf','image/*'])
                                    ->maxSize(30_720)
                                    ->disk('public')
                                    ->visibility('public')
                                    ->directory('production_files/' . now()->format('Y/m'))
                                    ->openable()
                                    ->downloadable()
                                    ->required()
                                    ->moveFiles()
                                    ->columnSpan(5),

                                // يظهر فقط في الطلب المباشر
                                TextInput::make('estimated_cost')
                                    ->label('التكلفة التقديرية')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('SAR')
                                    ->visible(fn (Get $get) => $get('../../request_type') === 'direct')
                                    ->required(fn (Get $get) => $get('../../request_type') === 'direct')
                                    ->dehydrated(fn (Get $get) => $get('../../request_type') === 'direct')
                                    ->default(null)
                                    ->columnSpan(3),
                            ]),
                    ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('project_name')
                    ->label('اسم المشروع')
                    ->searchable(),

                TextColumn::make('client.client_name')
                    ->label('العميل'),

                TextColumn::make('showroom.name')
                    ->label('المعرض')
                    ->formatStateUsing(fn ($state) => $state ?: 'غير مرتبط'),

                TextColumn::make('creator.name')
                    ->label('أنشئ بواسطة'),

                TextColumn::make('current_phase')
                    ->label('المرحلة')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'showroom_review'            => 'info',
                        'factory_intake'             => 'warning',
                        'department_assignment'      => 'gray',
                        'purchasing'                 => 'warning',
                        'manufacturing'              => 'primary',
                        'quality_after_manufacture'  => 'success',
                        'installation'               => 'purple',
                        'quality_after_installation' => 'success',
                        'closed'                     => 'gray',
                        default                      => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => $state ? static::phaseLabel((string) $state) : '—'),

                TextColumn::make('phase_status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending'        => 'secondary',
                        'received'       => 'primary',
                        'under_review'   => 'warning',
                        'approved'       => 'success',
                        'rejected'       => 'danger',
                        'in_progress'    => 'info',
                        'materials_prep' => 'purple',
                        'materials_done' => 'success',
                        'on_hold'        => 'warning',
                        'completed'      => 'success',
                        'cancelled'      => 'gray',
                        default          => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => $state ? static::statusLabel((string) $state) : '—'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Action::make('review')
                    ->label('مراجعة ')
                    ->icon('heroicon-o-check-circle')
                    ->url(fn ($record) => ProductionRequestResource::getUrl('review', ['record' => $record])),
                Action::make('view')
                    ->label('تفاصيل ')
                    ->icon('heroicon-o-check-circle')
                    ->url(fn ($record) => ProductionRequestResource::getUrl('view', ['record' => $record])),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected static function phaseLabel(string $phase): string
    {
        return match ($phase) {
            'sales_intake'               => 'استلام المبيعات',
            'showroom_review'            => 'مراجعة المعرض',
            'factory_intake'             => 'استلام المصنع',
            'department_assignment'      => 'إسناد الأقسام',
            'purchasing'                 => 'المشتريات',
            'manufacturing'              => 'التصنيع',
            'quality_after_manufacture'  => 'جودة ما بعد التصنيع',
            'installation'               => 'التركيب',
            'quality_after_installation' => 'جودة ما بعد التركيب',
            'closed'                     => 'مغلق',
            default                      => $phase,
        };
    }

    protected static function statusLabel(string $status): string
    {
        return match ($status) {
            'pending'        => 'قيد الانتظار',
            'received'       => 'تم الاستلام',
            'under_review'   => 'قيد المراجعة',
            'approved'       => 'معتمد',
            'rejected'       => 'مرفوض',
            'in_progress'    => 'قيد التنفيذ',
            'materials_prep' => 'تحضير الخامات',
            'materials_done' => 'تم توفير الخامات',
            'on_hold'        => 'معلق',
            'completed'      => 'مكتمل',
            'cancelled'      => 'ملغي',
            default          => $status,
        };
    }


    public static function getPages(): array
    {
        return [
            'index'  => ListProductionRequests::route('/'),
            'create' => CreateProductionRequest::route('/create'),
            'edit'   => EditProductionRequest::route('/{record}/edit'),
            'view'   => ViewProductionTimeline::route('/{record}/timeline'),
            'review' => ReviewProductionRequest::route('/{record}/review'),
        ];
    }
}
