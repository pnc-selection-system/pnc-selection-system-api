<?php

namespace App\Http\Controllers\Api\InfoSession;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InfoSssion\StoreInfoSessionRequest;
use App\Services\InfoSessionServices;
use App\Helpers\ApiResponse;

class InfoSessionController extends Controller
{
    public function __construct(
        protected InfoSessionServices $service
    ){}

    public function store(StoreInfoSessionRequest $request)
    {
        $session = $this->service->store($request->validated());
        return ApiResponse::created($session, 'Information Session created successfully.');
    }
}
