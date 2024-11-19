<?php

namespace App\CentralLogics;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;

class Helpers
{
    public static function get_full_url($path, $data, $type, $placeholder = null)
    {
        $place_holders = [
            'default' => asset('assets/img/favicon/favicon.png'),
            'restaurant-logos' => asset('assets/img/logos/logo.png'),
            'restaurant-favicons' => asset('assets/img/favicon/favicon.png'),
        ];

        try {
            if ($data && $type == 's3' && Storage::disk('s3')->exists($path . '/' . $data)) {
                return Storage::disk('s3')->url($path . '/' . $data);
            }
        } catch (\Exception $e) {
        }

        // Validate the URL format
        if ($data && filter_var($data, FILTER_VALIDATE_URL)) {
            return $data;
        }

        if ($data && Storage::disk('public')->exists($path . '/' . $data)) {
            return asset('storage') . '/' . $path . '/' . $data;
        }

        if (Request::is('api/*')) {
            return null;
        }

        if (isset($placeholder) && array_key_exists($placeholder, $place_holders)) {
            return $place_holders[$placeholder];
        } elseif (array_key_exists($path, $place_holders)) {
            return $place_holders[$path];
        } else {
            return empty($placeholder) ? null : $place_holders['default'];
        }
    }
}
