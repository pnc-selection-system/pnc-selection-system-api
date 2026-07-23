<?php

namespace App\Http\Controllers\Api\InfoSession;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InfoSssion\StoreInfoSessionRequest;
use App\Http\Requests\Api\InfoSssion\UpdateInfoSessionRequest;
use App\Services\InfoSessionServices;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class InfoSessionController extends Controller
{
    public function __construct(
        protected InfoSessionServices $service
    ){}

    public function index(Request $request)
    {
        $sessions = $this->service->list($request->only([
            'campaign_id', 'village_id', 'per_page', 'partner_type', 'province',
            'start_date', 'end_date', 'campaign_year',
        ]));
        return ApiResponse::success($sessions, 'Info sessions retrieved successfully.');
    }

    public function show(int $id)
    {
        $session = $this->service->find($id);
        return ApiResponse::success($session, 'Info session retrieved successfully.');
    }

    public function store(StoreInfoSessionRequest $request)
    {
        $session = $this->service->store($request->validated());
        return ApiResponse::created($session, 'Information Session created successfully.');
    }

    public function update(UpdateInfoSessionRequest $request, int $id)
    {
        $session = $this->service->update($id, $request->validated());
        return ApiResponse::success($session, 'Information Session updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);
        return ApiResponse::success(null, 'Information Session deleted successfully.');
    }
}
