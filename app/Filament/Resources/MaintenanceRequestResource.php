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
                        ->label('صور للمشكلة')->disk('public')
                        ->directory(fn()=> 'maintenance-requests/' . now()->format('Y/m'))
                        ->visibility('public')->image()->multiple()
                        ->maxSize(8192)->acceptedFileTypes(['image/*'])
                        ->openable()->downloadable()
                        ->dehydrateStateUsing(fn($state)=> array_values((array)$state))
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

                Tables\Actions\Action::make('add_note')
                    ->label('إضافة ملاحظة')->icon('heroicon-o-chat-bubble-left-right')->color('gray')
                    ->visible(fn()=> Auth::user()?->hasAnyRole(['factory_manager','admin','super-admin']) ?? false)
                    ->form([
                        Forms\Components\Textarea::make('note')->label('الملاحظة')->rows(4)->required()->maxLength(5000),
                    ])
                    ->action(function (MaintenanceRequest $record, array $data) {
                        $record->comments()->create([
                            'user_id'=>Auth::id(),
                            'note'=>(string)$data['note'],
                        ]);
                        Notification::make()->success()->title('تمت إضافة الملاحظة')->send();
                    }),

                Tables\Actions\Action::make('start')
                    ->label('بدء الصيانة')->icon('heroicon-o-play-circle')->color('primary')
                    ->visible(fn(MaintenanceRequest $r)=>
                        Auth::user()?->hasAnyRole(['factory_manager','admin','super-admin']) && $r->status==='new'
                    )
                    ->requiresConfirmation()
                    ->action(function (MaintenanceRequest $record) {
                        $record->update([
                            'status'=>'in_progress',
                            'current_owner_role'=>'factory_manager',
                            'current_owner_user_id'=>Auth::id(),
                            'received_by_owner_at'=>now(),
                        ]);
                        Notification::make()->success()->title('تم بدء الصيانة')->send();
                    }),

                Tables\Actions\Action::make('complete')
                    ->label('إغلاق الطلب (مكتمل)')->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn(MaintenanceRequest $r)=>
                        Auth::user()?->hasAnyRole(['factory_manager','admin','super-admin'])
                        && in_array($r->status,['new','in_progress'],true)
                    )
                    ->form([
                        Forms\Components\Textarea::make('note')->label('ملاحظة ختامية (اختياري)')->rows(3),
                        Forms\Components\FileUpload::make('closing_images')
                            ->label('صور بعد الصيانة (اختياري)')->disk('public')
                            ->directory(fn()=> 'maintenance-requests/' . now()->format('Y/m'))
                            ->visibility('public')->image()->multiple()
                            ->openable()->downloadable(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (MaintenanceRequest $record, array $data) {
                        $merged = array_values(array_unique(array_merge(
                            (array)($record->images ?? []),
                            (array)($data['closing_images'] ?? [])
                        )));
                        $record->update([
                            'status'=>'completed',
                            'images'=>$merged,
                            'closed_at'=>now(),
                        ]);
                        Notification::make()->success()->title('تم إغلاق طلب الصيانة')->send();
                    }),
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

    public static function afterCreate(MaintenanceRequest $record): void
    {
        $record->update([
            'current_owner_role'=>'factory_manager',
            'current_owner_user_id'=>null,
            'sent_to_owner_at'=>now(),
        ]);

        try {
            $role = Role::findByName('factory_manager');
            foreach ($role->users as $user) {
                Notification::make()
                    ->title('طلب صيانة جديد')
                    ->body("طلب #{$record->id} للمشروع #{$record->project_id}")
                    ->sendToDatabase($user);
            }
        } catch (\Throwable $e) {}
    }
}
