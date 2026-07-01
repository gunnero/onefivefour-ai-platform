<?php

namespace App\Filament\Resources\Organizations\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrganizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('legal_name'),
                TextInput::make('status')
                    ->required(),
                TextInput::make('timezone')
                    ->required(),
                TextInput::make('locale')
                    ->required(),
                TextInput::make('primary_domain'),
                Textarea::make('summary')
                    ->columnSpanFull(),
                Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
