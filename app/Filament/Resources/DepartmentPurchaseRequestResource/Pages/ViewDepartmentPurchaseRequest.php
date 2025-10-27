<?php

namespace App\Filament\Resources\DepartmentPurchaseRequestResource\Pages;

use App\Filament\Resources\DepartmentPurchaseRequestResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use App\Services\DprWorkflow;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class ViewDepartmentPurchaseRequest extends ViewRecord
{
    protected static string $resource = DepartmentPurchaseRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submit_to_factory')
                ->label('إرسال لاعتماد المصنع')
                ->color('info')
                ->visible(fn () => $this->record->status === 'draft')
                ->action(function () {
                    app(DprWorkflow::class)->setStatus($this->record, 'submitted_to_factory');
                    $this->refreshFormData(['status']);
                }),

            Action::make('factory_approve')
                ->label('اعتماد المصنع')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['submitted_to_factory'], true))
                ->action(function () {
                    $this->record->factory_approved_by = auth()->id();
                    $this->record->save();
                    app(DprWorkflow::class)->setStatus($this->record, 'factory_approved');
                }),

            Action::make('factory_reject')
                ->label('رفض المصنع')
                ->color('danger')
                ->visible(fn () => in_array($this->record->status, ['submitted_to_factory'], true))
                ->form([
                    \Filament\Forms\Components\Textarea::make('note')
                        ->label('سبب الرفض')
                        ->required()
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    app(DprWorkflow::class)->setStatus($this->record, 'factory_rejected', $data['note']);
                }),

            Action::make('send_to_purchasing')
                ->label('تحويل للمشتريات')
                ->color('warning')
                ->visible(fn () => $this->record->status === 'factory_approved')
                ->action(function () {
                    app(DprWorkflow::class)->setStatus($this->record, 'sent_to_purchasing');
                }),

            Action::make('mark_purchased')
                ->label('تم الشراء')
                ->color('info')
                ->visible(fn () => $this->record->status === 'sent_to_purchasing')
                ->action(function () {
                    $this->record->purchased_by = auth()->id();
                    $this->record->save();
                    app(DprWorkflow::class)->setStatus($this->record, 'purchased');
                }),

            Action::make('mark_delivered')
                ->label('تم التوريد')
                ->color('success')
                ->visible(fn () => $this->record->status === 'purchased')
                ->form([
                    \Filament\Forms\Components\Select::make('delivered_to')
                        ->relationship('requester', 'name')
                        ->required()
                        ->label('تم التسليم إلى'),
                    \Filament\Forms\Components\FileUpload::make('delivery_attachment')
                        ->label('مستند التوريد')
                        ->disk('public')
                        ->directory('dpr-delivery/' . now()->format('Y/m'))
                        ->openable()
                        ->downloadable(),
                ])
                ->action(function (array $data) {
                    $this->record->delivered_to = $data['delivered_to'];
                    $this->record->delivery_attachment = $data['delivery_attachment'] ?? null;
                    $this->record->save();
                    app(DprWorkflow::class)->setStatus($this->record, 'delivered');
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('ملخص الطلب')->schema([
                Grid::make(4)->schema([
                    TextEntry::make('request_number')->label('رقم الطلب')->badge(),
                    TextEntry::make('department.name')->label('القسم'),
                    TextEntry::make('priority')->label('الأولوية')->badge()
                        ->color(fn ($state) => ['low' => 'gray', 'medium' => 'warning', 'high' => 'danger'][$state] ?? 'gray'),
                    TextEntry::make('status')->label('الحالة')->badge()
                        ->color(fn ($s) => [
                            'draft' => 'gray',
                            'submitted_to_factory' => 'info',
                            'factory_approved' => 'success',
                            'factory_rejected' => 'danger',
                            'sent_to_purchasing' => 'info',
                            'purchased' => 'warning',
                            'delivered' => 'success',
                        ][$s] ?? 'gray'),
                ]),
                Grid::make(2)->schema([
                    TextEntry::make('title')->label('العنوان'),
                    TextEntry::make('requester.name')->label('منشئ الطلب'),
                ]),
                TextEntry::make('description')->label('التفاصيل')->prose()->columnSpanFull(),
            ])->columns(1),

            Section::make('السجل الزمني')->schema([
                RepeatableEntry::make('timeline')
                    ->label('الأنشطة')
                    ->state(function ($record) {
                        return $record->logs()
                            ->with('causer:id,name')
                            ->orderByDesc('created_at')
                            ->get()
                            ->map(function ($log) {
                                $map = [
                                    'submitted_to_factory' => ['label' => 'أُرسل لاعتماد المصنع', 'color' => 'info'],
                                    'factory_approved'     => ['label' => 'اعتماد مدير المصنع', 'color' => 'success'],
                                    'factory_rejected'     => ['label' => 'رفض مدير المصنع', 'color' => 'danger'],
                                    'sent_to_purchasing'   => ['label' => 'تحويل إلى المشتريات', 'color' => 'info'],
                                    'purchased'            => ['label' => 'تم الشراء', 'color' => 'warning'],
                                    'delivered'            => ['label' => 'تم التوريد', 'color' => 'success'],
                                    'updated'              => ['label' => 'تحديث', 'color' => 'gray'],
                                ];
                                $meta = $map[$log->action] ?? ['label' => $log->action, 'color' => 'gray'];

                                return [
                                    'created_at'   => optional($log->created_at)->format('Y-m-d H:i'),
                                    'action_label' => $meta['label'],
                                    'action_color' => $meta['color'],
                                    'causer_name'  => optional($log->causer)->name ?? '—',
                                    'note'         => $log->note,
                                ];
                            })
                            ->toArray();
                    })
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('action_label')
                                ->label('الحدث')
                                ->badge()
                                ->color(fn ($state, $record) => $record['action_color'] ?? 'gray'),
                            TextEntry::make('created_at')->label('التاريخ/الوقت'),
                            TextEntry::make('causer_name')->label('بواسطة'),
                            TextEntry::make('dummy')->hiddenLabel()->hidden(fn () => true),
                        ]),
                        TextEntry::make('note')
                            ->label('ملاحظة')
                            ->prose()
                            ->hidden(fn ($state) => blank($state)),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ])->collapsible()->collapsed(false),
        ])->columns(1);
    }
}
