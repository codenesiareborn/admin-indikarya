<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset_password')
                ->label('Reset Password')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    $password = Str::random(4) . rand(1000, 9999);
                    
                    if ($this->record->user) {
                        $this->record->user->update([
                            'password' => Hash::make($password),
                        ]);
                        
                        Notification::make()
                            ->success()
                            ->title('Password berhasil direset!')
                            ->body("Password baru untuk {$this->record->nama_lengkap}: **{$password}**")
                            ->persistent()
                            ->send();
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Gagal reset password')
                            ->body('User account tidak ditemukan.')
                            ->send();
                    }
                }),
            DeleteAction::make(),
        ];
    }
}
