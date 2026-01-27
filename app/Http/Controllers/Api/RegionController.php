<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RegionController extends Controller
{
    public function provinces()
    {
        return Cache::rememberForever('regions_provinces', function () {
            return response()->json(
                Province::select('id', 'name')
                    ->orderBy('name')
                    ->get()
            );
        });
    }

    public function regencies(Request $request)
    {
        $request->validate([
            'province_id' => 'required'
        ]);

        $cacheKey = 'regions_regencies_' . $request->province_id;

        return Cache::rememberForever($cacheKey, function () use ($request) {
            return response()->json(
                Regency::select('id', 'name')
                    ->where('province_id', $request->province_id)
                    ->orderBy('name')
                    ->get()
            );
        });
    }

    public function districts(Request $request)
    {
        $request->validate([
            'regency_id' => 'required'
        ]);

        $cacheKey = 'regions_districts_' . $request->regency_id;

        return Cache::rememberForever($cacheKey, function () use ($request) {
            return response()->json(
                District::select('id', 'name')
                    ->where('regency_id', $request->regency_id)
                    ->orderBy('name')
                    ->get()
            );
        });
    }

    public function villages(Request $request)
    {
        $request->validate([
            'district_id' => 'required'
        ]);

        $cacheKey = 'regions_villages_' . $request->district_id;

        return Cache::rememberForever($cacheKey, function () use ($request) {
            return response()->json(
                Village::select('id', 'name')
                    ->where('district_id', $request->district_id)
                    ->orderBy('name')
                    ->get()
            );
        });
    }
}
