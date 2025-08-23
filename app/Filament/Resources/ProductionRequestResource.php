<?php

// app/Filament/Resources/ProductionRequestResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\ProductionRequestResource\Pages;
use App\Models\ProductionRequest;

use Filament\Forms;
use Filament\Forms\Components\{Section, Grid, TextInput, Textarea, FileUpload, Select, Repeater};
use Filament\Forms\Get;
use Filament\Resources\Resource;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Collection;

class ProductionRequestResource extends Resource
{
    protected static ?string $model = ProductionRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'طلبات التصنيع';
    protected static ?string $navigationLabel = 'طلبات التصنيع';
    protected static ?string $recordTitleAttribute = 'project_name';
    protected static ?string $label = 'إدارة الطلبات';
    protected static ?string $pluralLabel = ' الطلبات';
    protected static ?string $modelLabel = 'طلب تصنيع';

    public static function getEloquentQuery(): Builder
    {
        return ProductionRequest::query()
            ->withoutGlobalScopes()
            ->latest('id');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([

            Select::make('client_id')
                ->label('العميل')
                ->options(\App\Models\Client::pluck('client_name', 'client_id'))
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('project_name')
                ->label('اسم المشروع')
                ->required(),

            Select::make('request_type')
                ->label('نوع الطلب')
                ->options(function () {
                    $user = auth()->user();
                    $opts = [];

                    // صلاحية طلب مباشر
                    if ($user?->hasAnyRole(['sales','factory_manager','admin','super-admin'])) {
                        $opts['direct'] = 'مباشر (من المبيعات للمصنع)';
                    }
                    // صلاحية طلب غير مباشر
                    if ($user?->hasAnyRole(['showroom_manager','admin','super-admin'])) {
                        $opts['indirect'] = 'غير مباشر (عن طريق المعرض)';
                    }

                    // احتياط: لو لم يكن لديه أي نوع، لا نعرض شيئًا
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
                ->required(fn (Get $get) => $get('request_type') === 'indirect')
                ->hidden(fn (Get $get) => $get('request_type') === 'direct'),



            Section::make('المرفقات')
                ->description('أرفق ملف الاتفاقية (PDF)، ثم أضف ملفات التصنيع حسب الأقسام المعنية.')
                ->schema([
                    Grid::make(12)->schema([

                        FileUpload::make('agreement_file')
                            ->label('ملف الاتفاقية (PDF)')
                            ->helperText('صيغة PDF فقط — حتى 20MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(20_480) // بالكيلوبايت
                            ->disk('public')
                            ->visibility('public')
                            ->directory('agreements/'.now()->format('Y/m'))
//                            ->preserveFilenames()
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
                            ->schema([
                                Select::make('department_id')
                                    ->label('القسم')
                                    ->options(\App\Models\Department::where('dept_type', '5')->pluck('dept_name', 'dept_id'))
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
                                    ->directory('production_files/'.now()->format('Y/m'))
//                                    ->preserveFilenames()
                                    ->openable()
                                    ->downloadable()
                                    ->required()
                                    ->moveFiles()
                                    ->columnSpan(8),
                            ])
                            ->columns(12)
                            ->columnSpan(['xl' => 8, 'lg' => 7, 'md' => 12]),
                    ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('project_name')->label('اسم المشروع')->searchable(),
                TextColumn::make('client.client_name')->label('العميل'),
                TextColumn::make('showroom.name')
                    ->label('المعرض')
                    ->formatStateUsing(fn($state) => $state ?: 'غير مرتبط'),
                TextColumn::make('creator.name')->label('أنشئ بواسطة'),

                TextColumn::make('current_phase')
                    ->label('المرحلة')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'showroom_review' => 'info',
                        'factory_intake' => 'warning',
                        'department_assignment' => 'gray',
                        'purchasing' => 'warning',
                        'manufacturing' => 'primary',
                        'quality_after_manufacture' => 'success',
                        'installation' => 'purple',
                        'quality_after_installation' => 'success',
                        'closed' => 'gray',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn($state) => $state ? __($state) : '—'),

                TextColumn::make('phase_status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'pending' => 'secondary',
                        'received' => 'primary',
                        'under_review' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'in_progress' => 'info',
                        'materials_prep' => 'purple',
                        'materials_done' => 'success',
                        'on_hold' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'gray',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn($state) => $state ?: '—'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('review')
                    ->label('مراجعة الطلب')
                    ->icon('heroicon-o-check-circle')
                    ->url(fn($record) => ProductionRequestResource::getUrl('review', ['record' => $record])),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProductionRequests::route('/'),
            'create' => Pages\CreateProductionRequest::route('/create'),
            'edit'   => Pages\EditProductionRequest::route('/{record}/edit'),
            'view'   => Pages\ViewProductionTimeline::route('/{record}/timeline'),
            'review' => Pages\ReviewProductionRequest::route('/{record}/review'),
        ];
    }
}
