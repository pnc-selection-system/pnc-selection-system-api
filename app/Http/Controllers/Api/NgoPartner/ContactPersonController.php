<?php

namespace App\Http\Controllers\Api\NgoPartner;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\NgoPartner\StoreContactPersonRequest;
use App\Http\Requests\Api\NgoPartner\UpdateContactPersonRequest;
use App\Models\NgoContactPersion;
use App\Models\NgoPartner;
use Illuminate\Http\JsonResponse;
use Services\NgoPartnerServices;

class ContactPersonController extends Controller
{
    public function __construct(protected NgoPartnerServices $ngoPartnerService) {}

    public function index(NgoPartner $ngoPartner): JsonResponse
    {
        $contactPersons = $this->ngoPartnerService->listContactPersons($ngoPartner->id, request()->all());

        return ApiResponse::success($contactPersons, 'Contact persons retrieved successfully');
    }

    public function store(StoreContactPersonRequest $request, NgoPartner $ngoPartner): JsonResponse
    {
        $contactPerson = $this->ngoPartnerService->createContactPerson($ngoPartner->id, $request->validated());

        return ApiResponse::created($contactPerson, 'Contact person created successfully');
    }

    public function show(NgoPartner $ngoPartner, NgoContactPersion $contactPerson): JsonResponse
    {
        return ApiResponse::success(
            $this->ngoPartnerService->findContactPerson($contactPerson),
            'Contact person retrieved successfully'
        );
    }

    public function update(UpdateContactPersonRequest $request, NgoPartner $ngoPartner, NgoContactPersion $contactPerson): JsonResponse
    {
        $contactPerson = $this->ngoPartnerService->updateContactPerson($contactPerson, $request->validated());

        return ApiResponse::success($contactPerson, 'Contact person updated successfully');
    }

    public function destroy(NgoPartner $ngoPartner, NgoContactPersion $contactPerson): JsonResponse
    {
        $this->ngoPartnerService->deleteContactPerson($contactPerson);

        return ApiResponse::ok('Contact person deleted successfully');
    }
}
