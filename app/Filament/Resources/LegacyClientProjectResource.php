<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LegacyClientProjectResource\Pages;
use App\Filament\Resources\LegacyClientProjectResource\RelationManagers\LegacyFilesRelationManager;
use App\Models\LegacyClientProject;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegacyClientProjectResource extends Resource
{
    protected static ?string $model = LegacyClientProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'العملاء';
    protected static ?string $navigationLabel = 'مشروعات العملاء القديمة';
    protected static ?string $pluralLabel = 'مشروعات العملاء القديمة';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('client_id')
                ->relationship('client', 'client_name')
                ->searchable()
                ->required()
                ->default(fn () => request('client_id'))
                ->disabled(fn () => filled(request('client_id')))
                ->label('العميل')
                ->dehydrated(),
            Forms\Components\TextInput::make('project_name')
                ->required()
                ->label('اسم المشروع'),

            Forms\Components\DatePicker::make('start_date')->label('تاريخ البداية'),
            Forms\Components\DatePicker::make('end_date')->label('تاريخ الانتهاء'),

            Forms\Components\Textarea::make('details')->rows(4)->label('تفاصيل أخرى'),

        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.client_name')->label('العميل')->searchable(),
                Tables\Columns\TextColumn::make('project_name')->label('المشروع')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('start_date')->date()->label('البداية')->sortable(),
                Tables\Columns\TextColumn::make('end_date')->date()->label('الانتهاء')->sortable(),
                Tables\Columns\TextColumn::make('details')->label('تفاصيل الخدمات '),
                Tables\Columns\TextColumn::make('files_count')
                    ->counts('files')
                    ->label('عدد الملفات')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('أضيفت')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client_id')
                    ->relationship('client','client_name')
                    ->label('العميل'),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Action::make('addLegacyProject')
                    ->label('إضافة مشروع قديم')
                    ->icon('heroicon-o-plus')
                    ->url(function () {
                        $clientId = request()->input('tableFilters.client_id.value')
                            ?? request('client_id'); // fallback لو جاء من رابط آخر
                        return \App\Filament\Resources\LegacyClientProjectResource::getUrl('create', [
                            'client_id' => $clientId,
                        ]);
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\LegacyClientProjectResource\RelationManagers\LegacyFilesRelationManager::class,
        ];
    }


    public function files():HasMany
    {
        return $this->hasMany(\App\Models\LegacyClientProjectFile::class, 'legacy_project_id');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLegacyClientProjects::route('/'),
            'create' => Pages\CreateLegacyClientProject::route('/create'),
            'edit'   => Pages\EditLegacyClientProject::route('/{record}/edit'),
            'view'   => Pages\ViewLegacyClientProject::route('/{record}'),
        ];
    }


}
