<?php

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('organization_id')
                    ->relationship('organization', 'name')
                    ->required(),
                Select::make('site_id')
                    ->relationship('site', 'name'),
                Select::make('department_id')
                    ->relationship('department', 'name'),
                Select::make('employee_id')
                    ->relationship('employee', 'id'),
                Select::make('assignment_id')
                    ->relationship('assignment', 'title'),
                Select::make('audit_log_id')
                    ->relationship('auditLog', 'id'),
                TextInput::make('activity_type')
                    ->required(),
                TextInput::make('status')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('body')
                    ->columnSpanFull(),
                Textarea::make('metadata')
                    ->columnSpanFull(),
                DateTimePicker::make('occurred_at')
                    ->required(),
            ]);
    }
}
