<?php

namespace App\Filament\Resources\StandardOperatingProcedures;

use App\Filament\Resources\StandardOperatingProcedures\Pages\CreateStandardOperatingProcedure;
use App\Filament\Resources\StandardOperatingProcedures\Pages\EditStandardOperatingProcedure;
use App\Filament\Resources\StandardOperatingProcedures\Pages\ListStandardOperatingProcedures;
use App\Filament\Resources\StandardOperatingProcedures\Schemas\StandardOperatingProcedureForm;
use App\Filament\Resources\StandardOperatingProcedures\Tables\StandardOperatingProceduresTable;
use App\Models\StandardOperatingProcedure;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StandardOperatingProcedureResource extends Resource
{
    protected static ?string $model = StandardOperatingProcedure::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StandardOperatingProcedureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StandardOperatingProceduresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStandardOperatingProcedures::route('/'),
            'create' => CreateStandardOperatingProcedure::route('/create'),
            'edit' => EditStandardOperatingProcedure::route('/{record}/edit'),
        ];
    }
}
