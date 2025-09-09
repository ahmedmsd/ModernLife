<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\{TextInput, Select, Textarea, Toggle};
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Tables\Columns\BadgeColumn;
class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'إدارة العملاء';
    protected static ?string $navigationLabel = 'العملاء';
    protected static ?string $recordTitleAttribute = 'client_name';
    protected static ?string $label = 'إدارة العملاء';
    protected static ?string $pluralLabel = 'إدارة العملاء';
    protected static ?string $modelLabel = 'عميل';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('client_name')
                ->label('اسم العميل')
                ->required()
                ->maxLength(100),

            Select::make('client_type')
                ->label('نوع العميل')
                ->required()
                ->options([
                    'individual' => 'فرد',
                    'company' => 'شركة',
                ]),

            TextInput::make('tax_number')
                ->label('الرقم الضريبي')
                ->maxLength(50),

            TextInput::make('commercial_registration')
                ->label('السجل التجاري')
                ->maxLength(50),

            TextInput::make('email')
                ->label('البريد الإلكتروني')
                ->email()
                ->maxLength(100),

            TextInput::make('phone')
                ->label('رقم الجوال')
                ->required()
                ->maxLength(20),

            TextInput::make('secondary_phone')
                ->label('جوال إضافي')
                ->maxLength(20),

            Textarea::make('address')
                ->label('العنوان'),

            Select::make('city_id')
                ->label('المدينة')
                ->relationship('city', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Toggle::make('is_active')
                ->label('نشط')
                ->default(true),

            TextInput::make('credit_limit')
                ->label('الحد الائتماني')
                ->numeric()
                ->default(0.00),

            TextInput::make('payment_terms')
                ->label('مدة السداد (أيام)')
                ->numeric()
                ->default(30),

            Textarea::make('notes')
                ->label('ملاحظات'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('client_name')
                ->label('اسم العميل')
                ->searchable()
                ->sortable(),

            TextColumn::make('client_type')
                ->label('نوع العميل')
                ->sortable(),

            TextColumn::make('email')
                ->label('البريد الإلكتروني')
                ->sortable(),
            TextColumn::make('city.name')
                ->label('المدينة')
                ->sortable(),
            TextColumn::make('phone')
                ->label('رقم الجوال'),

            IconColumn::make('is_active')
                ->label('نشط')
                ->boolean(),
            TextColumn::make('legacy_projects_count')
                ->counts('legacyProjects')
                ->label('مشروعات قديمة')
                ->badge()
                ->url(fn ($record) => LegacyClientProjectResource::getUrl('index', [
                    'tableFilters' => [
                        'client_id' => ['value' => $record->client_id],
                    ],
                    'client_id' => $record->client_id,
                ]))
                ->openUrlInNewTab(false) // غيّرها true لو تحب في تبويب جديد
                ->extraAttributes(['class' => 'text-primary-600 hover:underline'])
                ->sortable(),
            TextColumn::make('contacts_count')
                ->label('عدد جهات الاتصال')
                ->counts('contacts')
                ->sortable()
                ->url(fn($record) => route('filament.admin.resources.clients.edit', $record) . '#relationManagerComponent=contacts')
                ->color('primary')
                ->icon('heroicon-o-users'),

//            TextColumn::make('created_at')
//                ->label('تاريخ الإضافة')
//                ->dateTime(),

        ])->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('تعديل العميل'),

                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\ClientResource\RelationManagers\ClientContactsRelationManager::class,
        ];
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withCount('contacts');
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
