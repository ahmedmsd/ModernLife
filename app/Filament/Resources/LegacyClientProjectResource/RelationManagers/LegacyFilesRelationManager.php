<?php

namespace App\Filament\Resources\LegacyClientProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LegacyFilesRelationManager extends RelationManager
{
    protected static string $relationship = 'files';
    protected static ?string $title = 'ملفات المشروع';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('category')
                ->label('النوع')
                ->options([
                    'image'     => 'صورة',
                    'agreement' => 'اتفاقية',
                    'offer'     => 'عرض',
                    'other'     => 'أخرى',
                ])
                ->required(),

            Forms\Components\TextInput::make('title')
                ->label('العنوان'),

            Forms\Components\Textarea::make('description')
                ->label('وصف')
                ->rows(3),

            Forms\Components\FileUpload::make('file_path')
                ->label('الملف')
                ->disk('public')
                ->directory('legacy-projects')
                ->preserveFilenames()
                ->acceptedFileTypes([
                    'image/*',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])
                ->required(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('preview')
                    ->label('معاينة')
                    ->getStateUsing(fn ($record) => route('legacy-files.show', ['file' => $record->id]))
                    ->extraImgAttributes(['class' => 'h-12 w-12 rounded object-cover'])
                    ->visible(fn ($record): bool => Str::startsWith($record->mime_type ?? '', 'image/')),

                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->limit(40),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('MIME')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('file_size')
                    ->label('الحجم (KB)')
                    ->formatStateUsing(fn (?int $state): ?string => $state ? number_format($state / 1024, 1) : null)
                    ->alignRight(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('أُضيفت')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('open_link')
                    ->label('فتح')
                    ->state('فتح')
                    ->url(fn ($record) => route('legacy-files.show', ['file' => $record->id]))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->visible(fn ($record): bool => Str::startsWith($record->mime_type ?? '', 'image/')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة ملف')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['uploaded_by'] = auth()->id();

                        if (!empty($data['file_path'])) {
                            $disk = 'public';
                            $path = $data['file_path'];

                            try { $data['mime_type'] = Storage::disk($disk)->mimeType($path); } catch (\Throwable $e) {}
                            try { $data['file_size'] = Storage::disk($disk)->size($path); } catch (\Throwable $e) {}
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (!empty($data['file_path'])) {
                            $disk = 'public';
                            $path = $data['file_path'];
                            try { $data['mime_type'] = Storage::disk($disk)->mimeType($path); } catch (\Throwable $e) {}
                            try { $data['file_size'] = Storage::disk($disk)->size($path); } catch (\Throwable $e) {}
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }


}
