<?php

namespace App\Http\Controllers;

use App\Models\ChildRestaurant;
use App\Services\InstallationService;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function manageSettings(ChildRestaurant $childRestaurant)
    {
        $settingService = new SettingService;
        $settings = $settingService->getSettings($childRestaurant);

        return view('super-admin.settings', compact('settings'));
    }

    public function overrideSettings(ChildRestaurant $childRestaurant, Request $request)
    {
        $settingService = new SettingService;
        $settingService->overrideSettings($childRestaurant, $request->all());

        return redirect()->back()->with('success', 'Settings overridden successfully!');
    }

    public function installChildRestaurant(Request $request)
    {
        $installationService = new InstallationService;
        $childRestaurant = $installationService->createChildRestaurant($request);

        // Verify the installation token
        $installationToken = $request->input('installation_token');
        if ($installationToken !== $childRestaurant->installation_token) {
            return response()->json(['error' => 'Invalid installation token'], 401);
        }

        return response()->json(['installation_token' => $childRestaurant->installation_token]);
    }
}
