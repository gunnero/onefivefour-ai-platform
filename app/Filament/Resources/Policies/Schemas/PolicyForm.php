<?php

namespace App\Filament\Resources\Policies\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PolicyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('organization_id')
                    ->relationship('organization', 'name')
                    ->required(),
                TextInput::make('policy_key')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('category')
                    ->required(),
                TextInput::make('status')
                    ->required(),
                Textarea::make('body')
                    ->required()
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
