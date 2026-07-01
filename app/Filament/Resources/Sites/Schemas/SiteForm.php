<?php

namespace App\Filament\Resources\Sites\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('organization_id')
                    ->relationship('organization', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('status')
                    ->required(),
                TextInput::make('site_type'),
                TextInput::make('primary_domain'),
                TextInput::make('default_locale'),
                TextInput::make('timezone'),
                Textarea::make('audience_notes')
                    ->columnSpanFull(),
                Textarea::make('editorial_context')
                    ->columnSpanFull(),
                Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
