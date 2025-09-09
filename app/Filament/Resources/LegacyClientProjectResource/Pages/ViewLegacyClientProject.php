<?php

namespace App\Filament\Resources\LegacyClientProjectResource\Pages;

use App\Filament\Resources\LegacyClientProjectResource;
use App\Filament\Resources\LegacyClientProjectResource\RelationManagers\LegacyFilesRelationManager;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewLegacyClientProject extends ViewRecord
{
    protected static string $resource = LegacyClientProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addFile')
                ->label('إضافة ملف')
                ->icon('heroicon-o-plus')
                ->modalHeading('إضافة ملف للمشروع')
                ->form([
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
//                        ->preserveFilenames()
                        ->acceptedFileTypes([
                            'image/*',
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $data['uploaded_by'] = auth()->id();

                    if (!empty($data['file_path']) && Storage::disk('public')->exists($data['file_path'])) {
                        $data['mime_type'] = Storage::disk('public')->mimeType($data['file_path']) ?? null;
                        $data['file_size'] = Storage::disk('public')->size($data['file_path']) ?? null;
                    }

                    $this->record->files()->create($data);

                    // إشعار نجاح
                    Notification::make()
                        ->title('تمت إضافة الملف بنجاح')
                        ->success()
                        ->seconds(3)
                        ->send();

                    $this->dispatch('refresh');
                }),
        ];
    }

    public function getRelations(): array
    {
        return [
            LegacyFilesRelationManager::class,
        ];
    }
}
