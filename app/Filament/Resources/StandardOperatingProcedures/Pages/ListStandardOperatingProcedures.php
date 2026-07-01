<?php

namespace App\Filament\Resources\StandardOperatingProcedures\Pages;

use App\Filament\Resources\StandardOperatingProcedures\StandardOperatingProcedureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStandardOperatingProcedures extends ListRecords
{
    protected static string $resource = StandardOperatingProcedureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
