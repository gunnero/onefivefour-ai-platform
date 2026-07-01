<?php

namespace App\Filament\Resources\Assignments\Tables;

use App\Models\Assignment;
use App\Services\AssignmentLifecycleService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
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
                Action::make('accept')
                    ->label('Accept')
                    ->color('success')
                    ->visible(fn (Assignment $record): bool => app(AssignmentLifecycleService::class)->canAccept($record))
                    ->action(fn (Assignment $record): Assignment => app(AssignmentLifecycleService::class)->accept($record)),
                Action::make('start')
                    ->label('Start')
                    ->color('success')
                    ->visible(fn (Assignment $record): bool => app(AssignmentLifecycleService::class)->canStart($record))
                    ->action(fn (Assignment $record): Assignment => app(AssignmentLifecycleService::class)->start($record)),
                Action::make('block')
                    ->label('Block')
                    ->color('warning')
                    ->visible(fn (Assignment $record): bool => app(AssignmentLifecycleService::class)->canBlock($record))
                    ->schema([
                        Toggle::make('escalation_required')
                            ->label('Escalation Required'),
                    ])
                    ->action(fn (Assignment $record, array $data): Assignment => app(AssignmentLifecycleService::class)->block(
                        $record,
                        escalationRequired: (bool) ($data['escalation_required'] ?? false),
                    )),
                Action::make('resume')
                    ->label('Resume')
                    ->color('success')
                    ->visible(fn (Assignment $record): bool => app(AssignmentLifecycleService::class)->canResume($record))
                    ->action(fn (Assignment $record): Assignment => app(AssignmentLifecycleService::class)->resume($record)),
                Action::make('request_review')
                    ->label('Request Review')
                    ->color('info')
                    ->visible(fn (Assignment $record): bool => app(AssignmentLifecycleService::class)->canRequestReview($record))
                    ->action(fn (Assignment $record): Assignment => app(AssignmentLifecycleService::class)->requestReview($record)),
                Action::make('complete')
                    ->label('Complete')
                    ->color('success')
                    ->visible(fn (Assignment $record): bool => app(AssignmentLifecycleService::class)->canComplete($record))
                    ->schema([
                        Textarea::make('output_summary')
                            ->label('Output Summary')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->action(fn (Assignment $record, array $data): Assignment => app(AssignmentLifecycleService::class)->complete(
                        $record,
                        ['summary' => $data['output_summary']],
                    )),
                Action::make('fail')
                    ->label('Fail')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Assignment $record): bool => app(AssignmentLifecycleService::class)->canFail($record))
                    ->action(fn (Assignment $record): Assignment => app(AssignmentLifecycleService::class)->fail($record)),
                Action::make('cancel')
                    ->label('Cancel')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Assignment $record): bool => app(AssignmentLifecycleService::class)->canCancel($record))
                    ->action(fn (Assignment $record): Assignment => app(AssignmentLifecycleService::class)->cancel($record)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
