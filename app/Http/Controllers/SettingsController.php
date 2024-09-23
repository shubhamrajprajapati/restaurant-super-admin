<?php

namespace App\Http\Controllers;

use App\Models\ChildRestaurant;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(ChildRestaurant $childRestaurant)
    {
        $settings = Setting::where('child_restaurant_id', $childRestaurant->id)->get();

        return response()->json($settings);
    }

    public function update(ChildRestaurant $childRestaurant, Request $request)
    {
        foreach ($request->all() as $key => $value) {
            $setting = Setting::firstOrCreate([
                'child_restaurant_id' => $childRestaurant->id,
                'key' => $key,
            ]);

            $setting->value = $value;
            $setting->save();
        }

        return response()->json(['message' => 'Settings updated successfully!']);
    }
}
