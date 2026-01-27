<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserAddressController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $addresses = UserAddress::with([
            'province:id,name',
            'regency:id,name',
            'district:id,name',
            'village:id,name',
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone'          => ['required', 'string', 'max:20'],

            'province_id' => ['required', 'exists:provinces,id'],
            'regency_id'  => [
                'required',
                'exists:regencies,id',
                Rule::exists('regencies', 'id')->where(
                    fn($q) => $q->where('province_id', $request->province_id)
                ),
            ],
            'district_id' => [
                'required',
                'exists:districts,id',
                Rule::exists('districts', 'id')->where(
                    fn($q) => $q->where('regency_id', $request->regency_id)
                ),
            ],
            'village_id' => [
                'required',
                'exists:villages,id',
                Rule::exists('villages', 'id')->where(
                    fn($q) => $q->where('district_id', $request->district_id)
                ),
            ],

            'address_detail' => ['required', 'string'],
            'postal_code'    => ['required', 'string', 'max:10'],
        ]);

        $address = UserAddress::create([
            'user_id'        => $user->id,
            'province_id'    => $validated['province_id'],
            'regency_id'     => $validated['regency_id'],
            'district_id'    => $validated['district_id'],
            'village_id'     => $validated['village_id'],
            'address_detail' => $validated['address_detail'],
            'postal_code'    => $validated['postal_code'],
            'recipient_name' => $validated['recipient_name'],
            'phone'          => $validated['phone'],
        ]);

        return response()->json([
            'message' => 'Alamat berhasil disimpan',
            'data'    => $address->load([
                'province',
                'regency',
                'district',
                'village',
            ]),
        ], 201);
    }
}
