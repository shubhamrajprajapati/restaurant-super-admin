<?php

namespace App\Services;

use App\Models\ChildRestaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InstallationService
{
    public function generateInstallationToken()
    {
        return Str::random(32);
    }

    public function createChildRestaurant(Request $request)
    {
        $childRestaurant = new ChildRestaurant();
        $childRestaurant->domain = $request->input('domain');
        $childRestaurant->installation_token = $this->generateInstallationToken();
        $childRestaurant->save();

        return $childRestaurant;
    }
}