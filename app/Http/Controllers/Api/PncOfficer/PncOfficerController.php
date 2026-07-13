<?php

namespace App\Http\Controllers\Api\PncOfficer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PncOfficerController extends Controller
{
    public function show(string $officerId): JsonResponse
    {
        $officer = User::where('id', $officerId)->first(['id', 'name', 'role_id']);

        if (!$officer) {
            return response()->json(['message' => 'Officer ID not found'], 404);
        }

        return response()->json([
            'id'            => $officerId,
            'name'          => $officer->name,
            'department_id' => $officer->role_id,
        ]);
    }
}
