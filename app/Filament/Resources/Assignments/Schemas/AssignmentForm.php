<?php

namespace App\Filament\Resources\Assignments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AssignmentForm
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
                    ->relationship('department', 'name')
                    ->required(),
                Select::make('employee_id')
                    ->relationship('employee', 'id')
                    ->required(),
                Select::make('standard_operating_procedure_id')
                    ->relationship('standardOperatingProcedure', 'title'),
                TextInput::make('title')
                    ->required(),
                TextInput::make('assignment_type')
                    ->required(),
                TextInput::make('priority')
                    ->required(),
                TextInput::make('status')
                    ->required(),
                Textarea::make('briefing')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('expected_output')
                    ->columnSpanFull(),
                Textarea::make('input_payload')
                    ->columnSpanFull(),
                Textarea::make('output_payload')
                    ->columnSpanFull(),
                Textarea::make('required_capability_keys')
                    ->columnSpanFull(),
                TextInput::make('confidence_score')
                    ->numeric(),
                TextInput::make('quality_score')
                    ->numeric(),
                Toggle::make('escalation_required')
                    ->required(),
                Toggle::make('review_required')
                    ->required(),
                Textarea::make('review_path')
                    ->columnSpanFull(),
                DateTimePicker::make('due_at'),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('completed_at'),
            ]);
    }
}
