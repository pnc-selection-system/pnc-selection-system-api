<?php

namespace App\Http\Controllers\Api\Location;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use App\Models\District;
use App\Models\Province;
use App\Models\School;
use App\Models\Village;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function provinces(): JsonResponse
    {
        return response()->json(['data' => Province::orderBy('name')->get(['id', 'name'])]);
    }

    public function districts(Request $request): JsonResponse
    {
        $request->validate(['province_id' => 'required|exists:provinces,id']);
        return response()->json([
            'data' => District::where('province_id', $request->province_id)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function communes(Request $request): JsonResponse
    {
        $request->validate(['district_id' => 'required|exists:districts,id']);
        return response()->json([
            'data' => Commune::where('district_id', $request->district_id)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function villages(Request $request): JsonResponse
    {
        $request->validate(['commune_id' => 'required|exists:communes,id']);
        return response()->json([
            'data' => Village::where('commune_id', $request->commune_id)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function schools(Request $request): JsonResponse
    {
        $request->validate(['village_id' => 'required|exists:villages,id']);
        return response()->json([
            'data' => School::where('village_id', $request->village_id)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
