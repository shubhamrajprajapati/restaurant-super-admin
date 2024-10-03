<?php

namespace App\Filament\Resources\RestaurantSSHDetailsResource\Pages;

use App\Filament\Resources\RestaurantSSHDetailsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRestaurantSSHDetails extends CreateRecord
{
    protected static string $resource = RestaurantSSHDetailsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['updated_by_user_id'] = auth()->id();
        $data['created_by_user_id'] = auth()->id();

        return $data;
    }
}
