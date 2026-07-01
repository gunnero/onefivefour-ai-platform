<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('organization_id')
                    ->relationship('organization', 'name')
                    ->required(),
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->required(),
                TextInput::make('manager_employee_id')
                    ->numeric(),
                TextInput::make('employee_code')
                    ->required(),
                TextInput::make('full_name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('role_title')
                    ->required(),
                TextInput::make('employment_status')
                    ->required(),
                TextInput::make('avatar_url')
                    ->url(),
                Textarea::make('bio')
                    ->columnSpanFull(),
                Textarea::make('job_description')
                    ->columnSpanFull(),
                Textarea::make('mission')
                    ->columnSpanFull(),
                Textarea::make('responsibilities')
                    ->columnSpanFull(),
                Textarea::make('languages')
                    ->columnSpanFull(),
                Textarea::make('communication_style')
                    ->columnSpanFull(),
                Textarea::make('personality_profile')
                    ->columnSpanFull(),
                TextInput::make('approval_authority_level'),
                Textarea::make('metadata')
                    ->columnSpanFull(),
                DateTimePicker::make('hired_at'),
                DateTimePicker::make('paused_at'),
                DateTimePicker::make('retired_at'),
                DateTimePicker::make('archived_at'),
            ]);
    }
}
