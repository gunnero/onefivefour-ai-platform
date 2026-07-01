<?php

namespace App\Filament\Resources\StandardOperatingProcedures\Pages;

use App\Filament\Resources\StandardOperatingProcedures\StandardOperatingProcedureResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStandardOperatingProcedure extends EditRecord
{
    protected static string $resource = StandardOperatingProcedureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
