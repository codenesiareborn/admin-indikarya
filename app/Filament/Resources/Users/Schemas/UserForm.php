<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                            ->minLength(8)
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->label('Password')
                            ->helperText('Minimum 8 characters. Leave blank to keep current password when editing.')
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                
                Section::make('Role & Permissions')
                    ->schema([
                        Forms\Components\Select::make('role_name')
                            ->label('Role')
                            ->options(function () {
                                $user = auth()->user();
                                
                                // Super Admin can assign any role
                                if ($user && $user->hasRole('super_admin')) {
                                    return [
                                        'super_admin' => 'Super Admin',
                                        'admin' => 'Admin',
                                        'manager' => 'Manager',
                                        'staff' => 'Staff',
                                    ];
                                }
                                
                                // Admin cannot assign Super Admin role
                                return [
                                    'admin' => 'Admin',
                                    'manager' => 'Manager',
                                    'staff' => 'Staff',
                                ];
                            })
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->helperText('Select the role for this user')
                            ->columnSpanFull()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Forms\Components\Select $component, $record) {
                                if ($record) {
                                    $component->state($record->roles->first()?->name);
                                }
                            }),
                    ]),
                
                Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive users cannot login to the system')
                            ->inline(false),
                    ]),
            ]);
    }
}
