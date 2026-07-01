<?php

namespace App\Filament\Resources\Policies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PoliciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('organization.name')
                    ->searchable(),
                TextColumn::make('policy_key')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('category')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('version')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('effective_from')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('effective_to')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
