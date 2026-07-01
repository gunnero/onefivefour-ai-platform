<?php

namespace App\Filament\Resources\StandardOperatingProcedures\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StandardOperatingProcedureForm
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
                Select::make('site_id')
                    ->relationship('site', 'name'),
                TextInput::make('sop_key')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('status')
                    ->required(),
                Textarea::make('purpose')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('trigger_description')
                    ->columnSpanFull(),
                Textarea::make('inputs_schema')
                    ->columnSpanFull(),
                Textarea::make('steps')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('success_criteria')
                    ->columnSpanFull(),
                Textarea::make('quality_checks')
                    ->columnSpanFull(),
                Textarea::make('escalation_rules')
                    ->columnSpanFull(),
                Textarea::make('output_expectations')
                    ->columnSpanFull(),
                TextInput::make('version')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('effective_from'),
                DateTimePicker::make('effective_to'),
                Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
