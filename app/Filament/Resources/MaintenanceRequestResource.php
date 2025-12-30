<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceRequestResource\Pages\CreateMaintenanceRequest;
use App\Filament\Resources\MaintenanceRequestResource\Pages\EditMaintenanceRequest;
use App\Filament\Resources\MaintenanceRequestResource\Pages\ListMaintenanceRequests;
use App\Filament\Resources\MaintenanceRequestResource\Pages\ListMaintenanceRequestsDone;
use App\Filament\Resources\MaintenanceRequestResource\Pages\ViewMaintenanceRequest;
use App\Filament\Resources\MaintenanceRequestResource\RelationManagers\CommentsRelationManager;
use App\Models\Client;
use App\Models\MaintenanceRequest;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class MaintenanceRequestResource extends Resource
{
    protected static ?string $model = MaintenanceRequest::class;

    protected static ?string $navigationIcon   = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel  = 'طلبات الصيانة';
    protected static ?string $navigationGroup  = 'إدارة ما بعد البيع';
    protected static ?string $pluralModelLabel = 'طلبات الصيانة';
    protected static ?string $modelLabel       = 'طلب صيانة';
    protected static ?int    $navigationSort   = 50;
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = static::getPermissionScopedQuery();

        if (request()->routeIs('filament.admin.resources.maintenance-requests.completed')) {
            $q->completed();
        } elseif (request()->routeIs('filament.admin.resources.maintenance-requests.index')) {
            $q->active();
        }

        return $q;
    }

    public static function getPermissionScopedQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = parent::getEloquentQuery()
            ->latest('id');

        $user = auth()->user();
        if (! $user) {
            return $q->whereRaw('1 = 0');
        }

        // Admin-like roles see everything
        if ($user->hasAnyRole(['admin', 'super-admin', 'factory_manager', 'owner'])) {
            return $q;
        }

        // Sales see their own
        if ($user->hasRole('sales')) {
            return $q->where('maintenance_requests.created_by', $user->id);
        }

        // Showroom managers see their showrooms
        if ($user->hasRole('showroom_manager')) {
            $showroomIds = \App\Models\Showroom::where('manager_id', $user->id)->pluck('id');
            return $q->whereIn('maintenance_requests.showroom_id', $showroomIds);
        }

        return $q;
    }

    public static function getActiveCount(): int
    {
        return static::getPermissionScopedQuery()->active()->count();
    }

    public static function getCompletedCount(): int
    {
        return static::getPermissionScopedQuery()->completed()->count();
    }

    public static function canCreate(): bool
    {
        $u = Auth::user();
        return $u && $u->hasAnyRole(['sales','showroom_manager','admin','super-admin']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات الطلب')
                ->columns(2)
                ->schema([
//                    Forms\Components\Select::make('project_id')
//                        ->label('المشروع')
//                        ->options(fn () => Project::query()
//                            ->select('id', 'project_name')
//                            ->orderBy('project_name')
//                            ->get()
//                            ->mapWithKeys(fn ($p) => [(string) $p->id => (string) $p->project_name])
//                            ->all()
//                        )
//                        ->searchable()
//                        ->preload()
////                        ->required()
//                        ->reactive()
//                        ->afterStateUpdated(function ($state, callable $set) {
//                            $project = $state
//                                ? Project::with('client:client_id,client_name')->find($state)
//                                : null;
//                            $set('client_id', $project?->client?->client_id ?: null);
//                        }),
                    Select::make('showroom_id')
                        ->label('المعرض')
                        ->options(\App\Models\Showroom::pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->preload(),
                    Forms\Components\Select::make('client_id')
                        ->label('العميل')
                        ->options(fn () => Client::query()
                            ->select('client_id', 'client_name')
                            ->orderBy('client_name')
                            ->get()
                            ->filter(fn ($c) => filled($c->client_id) && filled($c->client_name))
                            ->mapWithKeys(fn ($c) => [(string) $c->client_id => (string) $c->client_name])
                            ->all() // ← array
                        )
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\DatePicker::make('request_date')
                        ->label('تاريخ الطلب')->default(now())->native(false)->required(),

                    Forms\Components\Textarea::make('details')
                        ->label('وصف المشكلة')->rows(5)->columnSpanFull(),

                    Forms\Components\FileUpload::make('images')
                        ->label('صور/فيديو المشكلة')
                        ->disk('public')
                        ->directory(fn () => 'maintenance-requests/' . now()->format('Y/m'))
                        ->visibility('public')
                        ->multiple()
                        ->acceptedFileTypes([
                            'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml',
                            'video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', // mov
                        ])
                        ->maxSize(102_400) // ≈ 100 MB لكل ملف
                        ->helperText('يدعم الصور والفيديو حتى 100MB. الصيغ: jpg, png, webp, gif, svg, mp4, webm, ogg, mov')
                        ->openable()
                        ->downloadable()
                        ->dehydrateStateUsing(fn ($state) => array_values((array) $state))
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

//                Tables\Columns\TextColumn::make('project.project_name')
//                    ->label('المشروع')
//                    ->searchable(),
                Tables\Columns\TextColumn::make('showroom.name')
                    ->label('المعرض')
                    ->searchable(),
                Tables\Columns\TextColumn::make('createdByUser.name')
                    ->label('بواسطة')
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.client_name')
                    ->label('العميل')
                    ->description(fn (MaintenanceRequest $record) => $record->client?->phone)
                    ->searchable(),
                Tables\Columns\TextColumn::make('request_date')
                    ->label('تاريخ الطلب')
                    ->date('Y-m-d'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'new'         => 'طلب جديد',
                        'in_progress' => 'قيد الصيانة',
                        'approved'    => 'معتمد',
                        'rejected'    => 'مرفوض',
                        'completed'   => 'مكتمل',
                        'cancelled'   => 'ملغي',
                        default       => '—',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'new'         => 'warning',
                        'in_progress' => 'info',
                        'approved'    => 'success',
                        'rejected'    => 'danger',
                        'completed'   => 'success',
                        'cancelled'   => 'gray',
                        default       => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('current_owner_role')
                    ->label('المالك الحالي')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'factory_manager' => 'مدير المصنع',
                        default           => $state ?: '—',
                    }),

                Tables\Columns\TextColumn::make('comments_count')
                    ->counts('comments')
                    ->label('تعليقات')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('أُنشئ')
                    ->since(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn(MaintenanceRequest $r)=>
                        Auth::user()?->hasAnyRole(['admin','super-admin']) || $r->status==='new'
                    ),
            ]);
    }

    public static function getRelations(): array
    {
        return [ CommentsRelationManager::class ];
    }

    public static function getPages(): array
    {
        return [
            'index'     => ListMaintenanceRequests::route('/'),
            'completed' => ListMaintenanceRequestsDone::route('/completed'),
            'create'    => CreateMaintenanceRequest::route('/create'),
            'view'      => ViewMaintenanceRequest::route('/{record}'),
            'edit'      => EditMaintenanceRequest::route('/{record}/edit'),
        ];
    }

}
