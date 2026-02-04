<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Exports\EmployeeTemplateExport;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Imports\EmployeeImport;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    return Excel::download(new EmployeeTemplateExport(), 'template_pegawai.xlsx');
                }),
            
            Action::make('import')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->required()
                        ->maxSize(5120), // 5MB
                ])
                ->action(function (array $data) {
                    try {
                        $import = new EmployeeImport();
                        Excel::import($import, $data['file']);

                        $successCount = $import->getSuccessCount();
                        $failureCount = $import->getFailureCount();
                        $errors = $import->getErrors();

                        if ($failureCount > 0) {
                            $errorMessage = "Berhasil: {$successCount}, Gagal: {$failureCount}. ";
                            $errorMessage .= "Error: " . implode('; ', array_slice($errors, 0, 3));
                            if (count($errors) > 3) {
                                $errorMessage .= " (dan " . (count($errors) - 3) . " error lainnya)";
                            }

                            Notification::make()
                                ->title('Import Selesai dengan Error')
                                ->body($errorMessage)
                                ->warning()
                                ->duration(10000)
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Import Berhasil')
                                ->body("Berhasil mengimport {$successCount} pegawai. Password default: NIP pegawai")
                                ->success()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import Gagal')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            
            CreateAction::make(),
        ];
    }
}
