<?php

// app/Filament/Resources/ProductionRequestResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\ProductionRequestResource\Pages;
use App\Models\ProductionRequest;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\{TextInput, Textarea, FileUpload, Select, Repeater};
use App\Enums\ProductionRequestStatus;
use Filament\Tables\Columns\TextColumn;

class ProductionRequestResource extends Resource
{
    protected static ?string $model = ProductionRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'طلبات التصنيع';
    protected static ?string $navigationLabel = 'طلبات التصنيع';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $label = 'إدارة الطلبات';
    protected static ?string $pluralLabel = ' الطلبات';
    protected static ?string $modelLabel = 'طلب تصنيع';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('project_name')->label('اسم المشروع')->required()->columnSpanFull(),
            Textarea::make('project_description')->label('وصف المشروع')->columnSpanFull(),
            Select::make('client_id')
                ->label('العميل')
                ->options(\App\Models\Client::pluck('client_name', 'client_id'))
                ->searchable()
                ->preload()
                ->required(),

            Select::make('showroom_id')
                ->label('المعرض')
                ->options(\App\Models\Showroom::pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            FileUpload::make('agreement_file')->label('ملف الاتفاقية')->disk('public')->directory('agreements')->openable()->downloadable(),
            Repeater::make('files')->label('ملفات التصنيع للأقسام')->relationship('files')
                ->schema([
                    Select::make('department_id')
                        ->label('القسم')
                        ->options(
                            \App\Models\Department::where('dept_type', '5')->pluck('dept_name', 'dept_id')
                        )
                        ->searchable()
                        ->required(),
                    FileUpload::make('file_path')->label('ملف القسم')->required()->disk('public')->directory('production_files')->openable()->downloadable(),
                ])->addActionLabel('إضافة ملف قسم')
                ->columnSpanFull()

        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('project_name')->label('اسم المشروع')->searchable(),
            Tables\Columns\TextColumn::make('client.client_name')->label('العميل'),
            Tables\Columns\TextColumn::make('showroom.name')->label('المعرض'),
            Tables\Columns\TextColumn::make('creator.name')->label('أنشئ بواسطة'),
            TextColumn::make('status')
                ->label('الحالة')
                ->formatStateUsing(
                    fn($state) =>
                    $state instanceof ProductionRequestStatus
                        ? $state->label()
                        : ProductionRequestStatus::tryFrom($state)?->label() ?? 'غير معروف'
                ),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('عرض الخط الزمني')
                ->icon('heroicon-o-clock')
                ->label('الخط الزمني')
                ->url(fn($record) => ProductionRequestResource::getUrl('view', ['record' => $record]))
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionRequests::route('/'),
            'create' => Pages\CreateProductionRequest::route('/create'),
            'edit' => Pages\EditProductionRequest::route('/{record}/edit'),
            'view' => Pages\ViewProductionTimeline::route('/{record}/timeline'),

        ];
    }
}
