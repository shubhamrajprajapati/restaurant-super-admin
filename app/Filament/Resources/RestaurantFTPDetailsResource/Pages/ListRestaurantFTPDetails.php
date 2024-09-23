<?php

namespace App\Filament\Resources\RestaurantFTPDetailsResource\Pages;

use App\Filament\Resources\RestaurantFTPDetailsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRestaurantFTPDetails extends ListRecords
{
    protected static string $resource = RestaurantFTPDetailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
