<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return \Illuminate\Support\Facades\Auth::user()?->can('access_view_project');
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('معلومات المشروع')
                ->schema([
                    Placeholder::make('project_name')
                        ->label('اسم المشروع')
                        ->content(fn($record) => $record->project_name),
                    Placeholder::make('client')
                        ->label('العميل')
                        ->content(fn($record) => $record->client->client_name),
                    Placeholder::make('start_date')
                        ->label('تاريخ البدء')
                        ->content(fn($record) => optional($record->start_date)?->format('Y-m-d')),
                    Placeholder::make('end_date')
                        ->label('تاريخ الانتهاء')
                        ->content(fn($record) => optional($record->end_date)?->format('Y-m-d')),
                    Placeholder::make('status')
                        ->label('الحالة')
                        ->content(fn($record) => $record->status),
                ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addTask')
                ->label('إدارة مهام المشروع ')
                ->icon('heroicon-o-plus')
                ->url(
                    fn() =>
                    ProjectResource::getUrl('manage-tasks', [
                        'record' => $this->record->getKey(),
                    ])
                ),

            Action::make('edit')
                ->label('تعديل المشروع')
                ->icon('heroicon-o-pencil')
                ->url(ProjectResource::getUrl('edit', [
                    'record' => $this->record->getKey(),
                ])),
        ];
    }
}
