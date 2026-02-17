<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $user = $request->user();
        $user->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json(['message' => 'Location updated successfully']);
    }

    public function getIpLocation(Request $request)
    {
        try {
            // Use ipapi.co or similar service server-side
            // Note: In local dev, $_SERVER['REMOTE_ADDR'] might be 127.0.0.1 which has no location.
            // But we can try the external service directly if we want the server's location (less useful)
            // OR explicitly fetch the public IP of the request if possible.

            // For now, simple proxy:
            $response = \Illuminate\Support\Facades\Http::get('https://ipapi.co/json/');

            if ($response->successful()) {
                return $response->json();
            }

            return response()->json(['error' => 'Failed to fetch IP location'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
