<?php

namespace App\Filament\Resources\Capabilities\Pages;

use App\Filament\Resources\Capabilities\CapabilityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCapability extends EditRecord
{
    protected static string $resource = CapabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
