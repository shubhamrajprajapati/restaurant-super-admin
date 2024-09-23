<?php

namespace App\Filament\Resources\RestaurantSSHDetailsResource\Pages;

use App\Filament\Resources\RestaurantSSHDetailsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRestaurantSSHDetails extends EditRecord
{
    protected static string $resource = RestaurantSSHDetailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
