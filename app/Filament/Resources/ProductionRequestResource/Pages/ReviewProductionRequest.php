<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Enums\ProductionRequestStatus;
use App\Filament\Resources\ProductionRequestResource;
use App\Models\ProductionRequest;
use App\Models\Project;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ReviewProductionRequest extends Page
{
    protected static string $resource = ProductionRequestResource::class;
    protected static string $view = 'filament.resources.production-request-resource.pages.review-production-request';
    protected static ?string $title = 'مراجعة الطلب';

    public ProductionRequest $record;

    public static function canAccess(array $parameters = []): bool
    {
        return \Illuminate\Support\Facades\Auth::user()?->can('access_review_production_request');
    }

    public function mount(ProductionRequest $record): void
    {
        $this->record = $record->load(['client', 'showroom', 'files.department']);
    }

    public function getHeaderActions(): array
    {
        if ($this->record->status !== ProductionRequestStatus::SUBMITTED->value) {
            return [];
        }

        return [
            Action::make('approve')
                ->label('اعتماد الطلب')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => ProductionRequestStatus::APPROVED->value,
                    ]);

                    $project = $this->record->project()->create([
                        'project_name' => $this->record->project_name,
                        'client_id' => $this->record->client_id,
                        'production_request_id' => $this->record->id,
                        'description' => $this->record->description,
                        'status' => 'in_progress',
                        'created_by' => Auth::id(),
                    ]);

                    foreach ($this->record->files as $file) {
                        $filePath = $file->file_path;
                        $fileName = basename($filePath);
                        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
                        $fileSize = Storage::disk('public')->exists($filePath)
                            ? Storage::disk('public')->size($filePath)
                            : 0;

                        $project->files()->create([
                            'department_id' => $file->department_id,
                            'file_name' => $fileName,
                            'file_path' => $filePath,
                            'file_type' => $fileType,
                            'file_size' => $fileSize,
                            'uploaded_by' => Auth::id(),
                            'upload_date' => now(),
                            'version' => 1,
                            'is_current' => true,
                        ]);
                    }

                    Notification::make()
                        ->success()
                        ->title('تم اعتماد الطلب وإنشاء المشروع بنجاح')
                        ->send();
                }),

            Action::make('reject')
                ->label('رفض الطلب')
                ->color('danger')
                ->form([
                    Textarea::make('note')
                        ->label('سبب الرفض')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => ProductionRequestStatus::REJECTED->value,
                    ]);

                    $this->record->logs()->create([
                        'user_id' => Auth::id(),
                        'action' => ProductionRequestStatus::REJECTED->value,
                        'note' => $data['note'],
                        'action_at' => now(),
                    ]);

                    Notification::make()
                        ->danger()
                        ->title('تم رفض الطلب')
                        ->send();
                }),
        ];
    }
}
