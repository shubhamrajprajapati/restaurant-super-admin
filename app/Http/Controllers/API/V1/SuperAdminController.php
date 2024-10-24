<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function installationStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;

        // Find the restaurant by ID
        $restaurant = Restaurant::find($id);

        if ($restaurant) {
            // Update the 'verified' status that will pretend as restaunt is installed or not
            $result = $restaurant->update(['verified' => $status]);

            // Optionally, return a response or message
            return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Restaurant not found.'], 404);
        }

    }
}
