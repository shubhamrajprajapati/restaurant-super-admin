<?php

namespace App\Services;

use App\Models\ChildRestaurant;
use App\Models\Setting;

class SettingService
{
    public function getSettings(ChildRestaurant $childRestaurant)
    {
        return Setting::where('child_restaurant_id', $childRestaurant->id)->get();
    }

    public function overrideSettings(ChildRestaurant $childRestaurant, array $settings): void
    {
        foreach ($settings as $key => $value) {
            $setting = Setting::firstOrCreate([
                'child_restaurant_id' => $childRestaurant->id,
                'key' => $key,
            ]);

            $setting->value = $value;
            $setting->save();
        }
    }
}
