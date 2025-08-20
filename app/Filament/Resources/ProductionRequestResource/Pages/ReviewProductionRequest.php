<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Enums\ProductionRequestStatus;
use App\Filament\Resources\ProductionRequestResource;
use App\Models\ProductionRequest;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewProductionRequest extends Page
{
    protected static string $resource = ProductionRequestResource::class;
    protected static string $view = 'filament.resources.production-request-resource.pages.review-production-request';
    protected static ?string $title = 'مراجعة الطلب';

    public ProductionRequest $record;

    public static function canAccess(array $parameters = []): bool
    {
        return Auth::user()?->can('access_review_production_request') ?? false;
    }

    public function mount(ProductionRequest $record): void
    {
        $this->record = $record->load(['client', 'showroom', 'files.department']);
    }

    public function getHeaderActions(): array
    {
        // أظهر أزرار الاعتماد/الرفض فقط عندما تكون الحالة Submitted
        if ((string) $this->record->status !== ProductionRequestStatus::SUBMITTED->value) {
            return [];
        }

        return [
            Action::make('approve')
                ->label('اعتماد الطلب')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    DB::transaction(function () {
                        // 1) تغيـير الحالة إلى APPROVED
                        $this->record->update([
                            'status' => ProductionRequestStatus::APPROVED->value,
                        ]);

                        // 2) بعد الحفظ، الـ Observer سيتكفّل بإنشاء المشروع ونسخ الملفات
                        // نحدّث الريكورد محليًا ليشمل المشروع الذي أنشأه الـ Observer
                        $this->record->refresh();
                    });

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
                    DB::transaction(function () use ($data) {
                        // تغيير الحالة إلى REJECTED
                        $this->record->update([
                            'status' => ProductionRequestStatus::REJECTED->value,
                        ]);

                        // سجل سبب الرفض
                        $this->record->logs()->create([
                            'user_id' => Auth::id(),
                            'action' => ProductionRequestStatus::REJECTED->value,
                            'note' => $data['note'],
                            'action_at' => now(),
                        ]);
                    });

                    Notification::make()
                        ->danger()
                        ->title('تم رفض الطلب')
                        ->send();
                }),
        ];
    }
}
