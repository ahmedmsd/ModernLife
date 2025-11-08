<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceRequestResource\Pages;
use App\Filament\Resources\MaintenanceRequestResource\Pages\CreateMaintenanceRequest;
use App\Filament\Resources\MaintenanceRequestResource\Pages\EditMaintenanceRequest;
use App\Filament\Resources\MaintenanceRequestResource\Pages\ListMaintenanceRequests;
use App\Filament\Resources\MaintenanceRequestResource\Pages\ViewMaintenanceRequest;
use App\Filament\Resources\MaintenanceRequestResource\RelationManagers\CommentsRelationManager;
use App\Models\Client;
use App\Models\MaintenanceRequest;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
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

    protected static bool $shouldAuthorizeResource = false;
    public static function canAccess(): bool
    {
        return auth()->check()
            && auth()->user()->hasAnyRole(['sales','showroom_manager','factory_manager','admin','super-admin']);
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
                    Forms\Components\Select::make('project_id')
                        ->label('المشروع')
                        ->options(fn () => Project::query()
                            ->select('id', 'project_name')
                            ->orderBy('project_name')
                            ->get()
                            ->mapWithKeys(fn ($p) => [(string) $p->id => (string) $p->project_name])
                            ->all()
                        )
                        ->searchable()
                        ->preload()
//                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $project = $state
                                ? Project::with('client:client_id,client_name')->find($state)
                                : null;
                            $set('client_id', $project?->client?->client_id ?: null);
                        }),

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

                Tables\Columns\TextColumn::make('project.project_name')
                    ->label('المشروع')
                    ->searchable(),

                Tables\Columns\TextColumn::make('client.client_name')
                    ->label('العميل')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.phone')
                    ->label('رقم الجوال')
                    ->searchable(),
                Tables\Columns\TextColumn::make('request_date')
                    ->label('تاريخ الطلب')
                    ->date(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'new'         => 'طلب جديد',
                        'in_progress' => 'قيد الصيانة',
                        'completed'   => 'مكتمل',
                        'cancelled'   => 'ملغي',
                        default       => '—',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'new'         => 'warning',
                        'in_progress' => 'info',
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
            'index'  => ListMaintenanceRequests::route('/'),
            'create' => CreateMaintenanceRequest::route('/create'),
            'view'   => ViewMaintenanceRequest::route('/{record}'),
            'edit'   => EditMaintenanceRequest::route('/{record}/edit'),
        ];
    }

    public static function afterCreate(\App\Models\MaintenanceRequest $record): void
    {
        $record->update([
            'current_owner_role'    => 'factory_manager',
            'current_owner_user_id' => null,
            'sent_to_owner_at'      => now(),
        ]);

        app(\App\Services\MaintenanceNotifier::class)->notifyNewRequest($record);

        \Filament\Notifications\Notification::make()
            ->title('تم إنشاء طلب صيانة جديد وإبلاغ مدير المصنع')
            ->success()
            ->send();
    }
}
