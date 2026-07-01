<?php

namespace App\Filament\Resources\Assignments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('organization.name')
                    ->searchable(),
                TextColumn::make('site.name')
                    ->searchable(),
                TextColumn::make('department.name')
                    ->searchable(),
                TextColumn::make('employee.id')
                    ->searchable(),
                TextColumn::make('standardOperatingProcedure.title')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('assignment_type')
                    ->searchable(),
                TextColumn::make('priority')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('confidence_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quality_score')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('escalation_required')
                    ->boolean(),
                IconColumn::make('review_required')
                    ->boolean(),
                TextColumn::make('due_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
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
