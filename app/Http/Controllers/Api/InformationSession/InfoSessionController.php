<?php

namespace App\Http\Controllers\Api\InformationSession;

use App\Http\Controllers\Controller;
use App\Http\Requests\InfoSession\StoreInfoSessionRequest;
use App\Http\Requests\InfoSession\UpdateInfoSessionRequest;
use App\Models\InfoSession;
use App\Models\InterestStudent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InfoSessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = InfoSession::with(['province', 'school']);

        if ($request->campaign_id)  $query->where('campaign_id', $request->campaign_id);
        if ($request->province_id)  $query->where('province_id', $request->province_id);
        if ($request->school_id)    $query->where('school_id', $request->school_id);
        if ($request->partner_type) $query->where('partner_type', $request->partner_type);
        if ($request->date_from)    $query->whereDate('date', '>=', $request->date_from);
        if ($request->date_to)      $query->whereDate('date', '<=', $request->date_to);

        $paginator = $query->paginate($request->input('page_size', 20));

        return response()->json([
            'data'      => $paginator->items(),
            'total'     => $paginator->total(),
            'page'      => $paginator->currentPage(),
            'page_size' => $paginator->perPage(),
        ]);
    }

    public function store(StoreInfoSessionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $conflict = InfoSession::where('school_id', $data['school_id'])
            ->whereDate('date', $data['date'])
            ->where('time', $data['time'])
            ->exists();

        if ($conflict) {
            return response()->json([
                'error' => [
                    'code'    => 'SCHEDULE_CONFLICT',
                    'message' => 'Another session is already scheduled at this school on this date.',
                ],
            ], 409);
        }

        $students = $data['interested_students'] ?? [];
        unset($data['interested_students'], $data['total_attendees']);

        $session = InfoSession::create($data);

        foreach ($students as $s) {
            $session->participants()->create(['full_name' => $s['name'], 'phone' => $s['contact'] ?? null]);
        }

        return response()->json($session->load(['province', 'school']), 201);
    }

    public function show(int $id): JsonResponse
    {
        $session = InfoSession::with(['province', 'district', 'commune', 'village', 'school'])->findOrFail($id);
        return response()->json($session);
    }

    public function update(UpdateInfoSessionRequest $request, int $id): JsonResponse
    {
        $data    = $request->validated();
        $session = InfoSession::findOrFail($id);

        if (isset($data['school_id'], $data['date'], $data['time'])) {
            $conflict = InfoSession::where('school_id', $data['school_id'])
                ->whereDate('date', $data['date'])
                ->where('time', $data['time'])
                ->where('id', '!=', $id)
                ->exists();

            if ($conflict) {
                return response()->json([
                    'error' => [
                        'code'    => 'SCHEDULE_CONFLICT',
                        'message' => 'Another session is already scheduled at this school on this date.',
                    ],
                ], 409);
            }
        }

        $session->update($data);
        return response()->json($session);
    }

    public function destroy(int $id): JsonResponse
    {
        InfoSession::findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    public function updateAttendance(Request $request, int $id): JsonResponse
    {
        $request->validate(['attendance_count' => 'required|integer|min:0']);
        $session = InfoSession::findOrFail($id);
        $session->update(['attendance_count' => $request->attendance_count]);
        return response()->json($session);
    }

    public function storeParticipant(Request $request, int $id): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:150', 'contact' => 'nullable|string|max:100']);
        $session     = InfoSession::findOrFail($id);
        $participant = $session->participants()->create(['full_name' => $request->name, 'phone' => $request->contact]);
        return response()->json($participant, 201);
    }

    public function listParticipants(int $id): JsonResponse
    {
        $session = InfoSession::with('participants')->findOrFail($id);
        return response()->json(['data' => $session->participants]);
    }

    public function convertToCandidate(int $participantId): JsonResponse
    {
        InterestStudent::findOrFail($participantId);
        return response()->json(['message' => 'Added as candidate', 'candidate_id' => null]);
    }
}
