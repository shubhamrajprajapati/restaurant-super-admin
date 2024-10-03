<?php

namespace App\Filament\Resources\RestaurantFTPDetailsResource\Pages;

use App\Filament\Resources\RestaurantFTPDetailsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRestaurantFTPDetails extends EditRecord
{
    protected static string $resource = RestaurantFTPDetailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by_user_id'] = auth()->id();

        return $data;
    }
}
