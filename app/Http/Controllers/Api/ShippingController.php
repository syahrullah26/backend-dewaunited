<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\KomerceShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShippingController extends Controller
{
    public function rates(Request $request, KomerceShippingService $service)
    {
        $validated = $request->validate([
            'origin' => 'required|string',
            'destination' => 'required|string',
            'weight' => 'required|integer|min:1',
            'courier' => 'required|string',
        ]);

        $cacheKey = 'shipping_rates_' . md5(json_encode($validated));

        return Cache::remember($cacheKey, 3600, function () use ($service, $validated) {
            return response()->json(
                $service->shippingRates($validated)
            );
        });
    }
}
