<?php

// app/Filament/Resources/DepartmentPurchaseRequestResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentPurchaseRequestResource\Pages\CreateDepartmentPurchaseRequest;
use App\Filament\Resources\DepartmentPurchaseRequestResource\Pages\EditDepartmentPurchaseRequest;
use App\Filament\Resources\DepartmentPurchaseRequestResource\Pages\ListDepartmentPurchaseRequests;
use App\Filament\Resources\DepartmentPurchaseRequestResource\Pages\ViewDepartmentPurchaseRequest;
use App\Filament\Resources\DepartmentPurchaseRequestResource\RelationManagers\ItemsRelationManager;
use App\Models\DepartmentPurchaseRequest;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class DepartmentPurchaseRequestResource extends Resource
{
    protected static ?string $model = DepartmentPurchaseRequest::class;

    protected static ?string $navigationGroup = 'المشتريات';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'طلبات الأقسام';
    protected static ?string $pluralModelLabel = 'طلبات الأقسام الخارجية';

    public static function getPermissionPrefixes(): array
    {
        return ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات الطلب')->schema([
                Forms\Components\Select::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'dept_name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('title')
                    ->label('العنوان')
                    ->required()
                    ->maxLength(200),

                Forms\Components\Textarea::make('description')
                    ->label('التفاصيل')
                    ->rows(4),

                Forms\Components\Select::make('priority')
                    ->label('الأولوية')
                    ->options([
                        'low' => 'منخفض',
                        'medium' => 'متوسط',
                        'high' => 'عالي',
                    ])
                    ->default('medium')
                    ->required(),

                Forms\Components\FileUpload::make('attachment')
                    ->label('مرفق')
                    ->disk('public')
                    ->directory('dpr/' . now()->format('Y/m'))
                    ->openable()
                    ->downloadable(),
            ])->columns(2),
            Forms\Components\Section::make('الأصناف')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Forms\Components\TextInput::make('item_name')
                                ->label('الصنف')
                                ->required()
                                ->columnSpan(3),
                            Forms\Components\TextInput::make('quantity')
                                ->label('الكمية')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('unit_price')
                                ->label('السعر التقديري')
                                ->numeric()
                                ->default(0)
                                ->columnSpan(2),
                            Forms\Components\Textarea::make('notes')
                                ->label('ملاحظات')
                                ->rows(1)
                                ->columnSpan(6),
                        ])
                        ->columns(6)
                        ->defaultItems(1)
                        ->addActionLabel('إضافة صنف')
                        ->collapsible(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->label('رقم')
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->limit(40)
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('department.dept_name')
                    ->label('القسم'),

                Tables\Columns\TextColumn::make('priority')
                    ->label('أولوية')
                    ->badge()
                    ->color(fn (string $state) => [
                        'low' => 'gray',
                        'medium' => 'warning',
                        'high' => 'danger',
                    ][$state] ?? 'gray')
                    ->formatStateUsing(fn (string $state) => [
                        'low' => 'منخفض',
                        'medium' => 'متوسط',
                        'high' => 'عالي',
                    ][$state] ?? $state),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state) => [
                        'draft' => 'gray',
                        'submitted_to_factory' => 'info',
                        'factory_approved' => 'success',
                        'factory_rejected' => 'danger',
                        'sent_to_purchasing' => 'info',
                        'purchased' => 'warning',
                        'delivered' => 'success',
                    ][$state] ?? 'gray')
                    ->formatStateUsing(fn (string $state) => [
                        'draft' => 'مسودة',
                        'submitted_to_factory' => 'بإنتظار اعتماد المصنع',
                        'factory_approved' => 'معتمد من المصنع',
                        'factory_rejected' => 'مرفوض من المصنع',
                        'sent_to_purchasing' => 'للمشتريات',
                        'purchased' => 'تم الشراء',
                        'delivered' => 'تم التوريد',
                    ][$state] ?? $state),

                Tables\Columns\TextColumn::make('total_estimated_cost')
                    ->label('التكلفة التقديرية')
                    ->money('sar')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('أُنشئ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'مسودة',
                    'submitted_to_factory' => 'بإنتظار اعتماد المصنع',
                    'factory_approved' => 'معتمد من المصنع',
                    'factory_rejected' => 'مرفوض من المصنع',
                    'sent_to_purchasing' => 'للمشتريات',
                    'purchased' => 'تم الشراء',
                    'delivered' => 'تم التوريد',
                ]),
                Tables\Filters\SelectFilter::make('priority')->options([
                    'low' => 'منخفض',
                    'medium' => 'متوسط',
                    'high' => 'عالي',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (\App\Models\DepartmentPurchaseRequest $record) => $record->status === 'draft'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false),
            ])
            ->recordUrl(fn (DepartmentPurchaseRequest $record) => static::getUrl('view', ['record' => $record]));
    }

    public static function getRelations(): array
    {
        return [
            // ItemsRelationManager::class, // Removed to avoid duplication with the Repeater form
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDepartmentPurchaseRequests::route('/'),
            'create' => CreateDepartmentPurchaseRequest::route('/create'),
            'view'   => ViewDepartmentPurchaseRequest::route('/{record}'),
            'edit'   => EditDepartmentPurchaseRequest::route('/{record}/edit'),
        ];
    }
}
