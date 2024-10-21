<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    public function index($identifier)
    {
        // Check if the identifier is a valid UUID
        if (Str::isUuid($identifier)) {
            // Fetch by UUID
            $restaurant = Restaurant::findOrFail($identifier);
        } elseif (preg_match('/^https?:\/\/.+/', $identifier)) {
            // Strip the trailing slash if it exists
            $identifier = rtrim($identifier, '/');
            $restaurant = Restaurant::whereDomain($identifier)->firstOrFail();
        } else {
            abort(404); // Invalid identifier format
        }

        return response()->json($restaurant);
    }

}
