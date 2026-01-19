<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function afterCreate(): void
    {
        $password = session()->pull('employee_password_' . $this->record->id);
        
        if ($password) {
            Notification::make()
                ->success()
                ->title('Pegawai berhasil dibuat!')
                ->body("Password untuk {$this->record->nama_lengkap}: **{$password}**")
                ->persistent()
                ->send();
        }
    }
}
