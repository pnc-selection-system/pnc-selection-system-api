<?php

namespace App\Http\Controllers\Api\Commune;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Services\CommuneServices;

class CommuneController extends Controller
{
    public function __construct(protected CommuneServices $communeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $communes = $this->communeService->list($request->only(['district_id']));

        return ApiResponse::success($communes, 'Communes retrieved successfully');
    }
}
