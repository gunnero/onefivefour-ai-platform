<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AuditLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('organization_id')
                    ->relationship('organization', 'name')
                    ->required(),
                TextInput::make('actor_type'),
                TextInput::make('actor_id')
                    ->numeric(),
                TextInput::make('auditable_type')
                    ->required(),
                TextInput::make('auditable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('event_type')
                    ->required(),
                TextInput::make('action')
                    ->required(),
                Textarea::make('summary')
                    ->columnSpanFull(),
                Textarea::make('before_state')
                    ->columnSpanFull(),
                Textarea::make('after_state')
                    ->columnSpanFull(),
                Textarea::make('reason')
                    ->columnSpanFull(),
                Textarea::make('metadata')
                    ->columnSpanFull(),
                DateTimePicker::make('occurred_at')
                    ->required(),
            ]);
    }
}
