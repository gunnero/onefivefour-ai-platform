<?php

namespace App\Filament\Resources\Capabilities\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CapabilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('capability_key')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('category'),
                TextInput::make('status')
                    ->required(),
                Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
