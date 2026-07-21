<?php

namespace App\Http\Controllers\Api\Province;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
<<<<<<< HEAD

use App\Http\Requests\Api\Province\StoreProvinceRequest;
use App\Http\Requests\Api\Province\UpdateProvinceRequest;
use App\Models\Province;
=======
>>>>>>> 4f773a29f9f06f0ee19adec429b00e34e57959cb
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Services\ProvinceServices;

class ProvinceController extends Controller
{
    public function __construct(protected ProvinceServices $provinceService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $provinces = $this->provinceService->list($request->all());

        return ApiResponse::success($provinces, 'Provinces retrieved successfully');
    }
}
