<?php

namespace App\Filament\Resources\RestaurantSSHDetailsResource\Pages;

use App\Filament\Resources\RestaurantSSHDetailsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRestaurantSSHDetails extends ListRecords
{
    protected static string $resource = RestaurantSSHDetailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
