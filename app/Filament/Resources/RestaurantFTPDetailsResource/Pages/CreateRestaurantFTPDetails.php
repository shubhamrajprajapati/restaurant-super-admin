<?php

namespace App\Filament\Resources\RestaurantFTPDetailsResource\Pages;

use App\Filament\Resources\RestaurantFTPDetailsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRestaurantFTPDetails extends CreateRecord
{
    protected static string $resource = RestaurantFTPDetailsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['updated_by_user_id'] = auth()->id();
        $data['created_by_user_id'] = auth()->id();

        return $data;
    }
}
