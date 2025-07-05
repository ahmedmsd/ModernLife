<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function resolveRecord(int | string $key): \Illuminate\Database\Eloquent\Model
    {
        return static::getModel()::with('user')->findOrFail($key);
    }

    public function mount($record = null): void
    {
        parent::mount($record);
        $this->form->fill(array_merge(
            $this->record->toArray(),
            [
                'user' => [
                    'email' => $this->record->user?->email,
                    'password' => null,
                ],
            ]
        ));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['user']['email'])) {
            $this->record->user->email = $data['user']['email'];
        }

        if (!empty($data['user']['password'])) {
            $this->record->user->password = bcrypt($data['user']['password']);
        }

        $this->record->user->save();

        unset($data['user']);

        return $data;
    }
    protected function getRedirectUrl(): string
    {
        // Redirect to the index page instead of the edit page
        return EmployeeResource::getUrl('index');
    }
}
