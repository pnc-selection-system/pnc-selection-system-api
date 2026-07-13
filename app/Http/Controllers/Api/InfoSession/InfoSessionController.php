<?php

namespace App\Http\Controllers\InfoSession;

use App\Http\Controllers\Controller;
use App\Http\Requests\InfoSession\StoreInfoSessionRequest;
use App\Http\Requests\InfoSession\UpdateInfoSessionRequest;
use App\Http\Resources\InfoSession\InfoSessionResource;
use App\Models\InterestStudent;
use App\Services\InfoSession\InfoSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InfoSessionController extends Controller
{
    public function __construct(protected InfoSessionService $service) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->service->list($request->only([
            'campaign_id', 'province_id', 'school_id', 'partner_type',
            'date_from', 'date_to', 'page', 'page_size',
        ]));

        return response()->json([
            'data'      => InfoSessionResource::collection($paginator->items()),
            'total'     => $paginator->total(),
            'page'      => $paginator->currentPage(),
            'page_size' => $paginator->perPage(),
        ]);
    }

    public function store(StoreInfoSessionRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($this->service->hasConflict($data['school_id'], $data['date'], $data['time'])) {
            return response()->json([
                'error' => [
                    'code'    => 'SCHEDULE_CONFLICT',
                    'message' => 'Another session is already scheduled at this school on this date.',
                ],
            ], 409);
        }

        $students = $data['interested_students'] ?? [];
        unset($data['interested_students'], $data['total_attendees']);

        $session = $this->service->create($data);

        foreach ($students as $student) {
            $session->participants()->create([
                'full_name' => $student['name'],
                'phone'     => $student['contact'] ?? null,
            ]);
        }

        return response()->json(new InfoSessionResource($session->load(['province', 'school'])), 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(new InfoSessionResource($this->service->findById($id)));
    }

    public function update(UpdateInfoSessionRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['school_id'], $data['date'], $data['time'])) {
            if ($this->service->hasConflict($data['school_id'], $data['date'], $data['time'], $id)) {
                return response()->json([
                    'error' => [
                        'code'    => 'SCHEDULE_CONFLICT',
                        'message' => 'Another session is already scheduled at this school on this date.',
                    ],
                ], 409);
            }
        }

        return response()->json(new InfoSessionResource($this->service->update($id, $data)));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json(null, 204);
    }

    public function updateAttendance(Request $request, int $id): JsonResponse
    {
        $request->validate(['attendance_count' => 'required|integer|min:0']);
        return response()->json(new InfoSessionResource(
            $this->service->updateAttendance($id, $request->attendance_count)
        ));
    }

    public function storeParticipant(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name'    => 'required|string|max:150',
            'contact' => 'nullable|string|max:100',
        ]);

        $session     = $this->service->findById($id);
        $participant = $session->participants()->create([
            'full_name' => $request->name,
            'phone'     => $request->contact,
        ]);

        return response()->json($participant, 201);
    }

    public function listParticipants(int $id): JsonResponse
    {
        $session = $this->service->findById($id);
        return response()->json(['data' => $session->participants]);
    }

    public function convertToCandidate(int $participantId): JsonResponse
    {
        $participant = InterestStudent::findOrFail($participantId);
        // Conversion logic placeholder — extend when Candidate model is ready
        return response()->json([
            'message'      => 'Added as candidate',
            'candidate_id' => null,
        ]);
    }
}
