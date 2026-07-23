<?php

namespace App\Http\Controllers\Api\AssessmentResponse;

use App\Http\Controllers\Controller;
use App\Services\AssessmentResponseService;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class AssessmentResponseController extends Controller
{
    public function __construct(
        protected AssessmentResponseService $service
    ) {}

    public function index(Request $request)
    {
        $responses = $this->service->list($request->only(['candidate_id', 'assessment_form_id']));
        return ApiResponse::success($responses, 'Assessment responses retrieved successfully.');
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'candidate_id' => 'required|integer|exists:candidates,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer',
            'answers.*.answer' => 'required|string',
        ]);

        $response = $this->service->submit($data);
        return ApiResponse::created($response, 'Assessment response submitted successfully.');
    }
}
