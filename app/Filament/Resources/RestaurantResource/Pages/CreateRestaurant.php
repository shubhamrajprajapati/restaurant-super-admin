<?php

namespace App\Filament\Resources\RestaurantResource\Pages;

use App\Filament\Resources\RestaurantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRestaurant extends CreateRecord
{
    protected static string $resource = RestaurantResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['updated_by_user_id'] = auth()->id();
        $data['created_by_user_id'] = auth()->id();

        return $data;
    }
}
