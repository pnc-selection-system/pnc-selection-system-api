<?php

namespace App\Http\Controllers\Api\HomeInvestigation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\HomeInvestigation\SaveDraftRequest;
use App\Http\Requests\Api\HomeInvestigation\SubmitInvestigationRequest;
use App\Http\Requests\Api\HomeInvestigation\StoreLegacyInvestigationRequest;
use App\Http\Requests\Api\HomeInvestigation\UpdateLegacyInvestigationRequest;
use App\Http\Requests\Api\HomeInvestigation\RejectInvestigationRequest;
use App\Models\Candidate;
use App\Models\HomeInvestigation;
use App\Models\HomeInvestigationFile;
use App\Models\InvestigationHistory;
use App\Models\Role;
use App\Models\SelectCampaing;
use App\Models\User;
use App\Enums\InvestigationStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HomeInvestigationController extends Controller
{
    /**
     * 3.1 Page Metadata
     * GET /home-investigation/meta
     */
    public function meta(): JsonResponse
    {
        return response()->json([
            'breadcrumb' => ['Evaluation', 'Home Investigation'],
            'title' => 'Home Investigation',
            'roles' => [
                ['role' => 'Investigator', 'scope' => 'own only'],
                ['role' => 'Manager', 'scope' => 'all'],
            ],
            'reqRange' => ['FR-HI-1', 'FR-HI-6'],
        ]);
    }

    /**
     * 3.2 Candidates List
     * GET /home-investigation/candidates
     *
     * Queries the candidates table directly and left-joins home_investigations
     * so that ALL candidates appear — even those without an investigation record yet.
     *
     * Performance notes:
     * - Uses simplePaginate() to avoid expensive COUNT(*) queries
     * - Searches on individual name columns instead of CONCAT in WHERE
     * - Concatenates candidate name in PHP instead of DB::raw
     */
    public function candidates(Request $request): JsonResponse
    {
        $user = Auth::user();
        $userRole = $user->role_id ?? null;
        $userName = $user->name ?? '';

        $query = Candidate::query()
            ->leftJoin('home_investigations', 'candidates.id', '=', 'home_investigations.candidate_id')
            ->leftJoin('selection_campaigns', 'candidates.campaign_id', '=', 'selection_campaigns.id')
            // Only show candidates who passed the interest assessment
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('assessment_responses')
                  ->whereColumn('assessment_responses.candidate_id', 'candidates.id')
                  ->where('assessment_responses.passed', true);
            })
            ->select([
                'candidates.id',
                'candidates.first_name',
                'candidates.last_name',
                'selection_campaigns.name AS campaign',
                'home_investigations.assigned_investigator',
                'home_investigations.visit_date',
                'home_investigations.status',
                'candidates.gender',
                'candidates.phone',
                DB::raw("COALESCE(home_investigations.current_address, '') AS current_address"),
            ]);

        // Authorization: Investigator sees only own candidates (those assigned to them)
        // or candidates without any investigation yet (so they can claim them).
        if ($this->isInvestigator($userRole)) {
            $query->where(function ($q) use ($userName) {
                $q->where('home_investigations.assigned_investigator', $userName)
                  ->orWhereNull('home_investigations.id');
            });
        }

        // Filters — search on individual columns to avoid expensive CONCAT in WHERE
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('candidates.id', 'like', "%{$search}%")
                  ->orWhere('candidates.first_name', 'like', "%{$search}%")
                  ->orWhere('candidates.last_name', 'like', "%{$search}%");
            });
        }

        if ($campaign = $request->get('campaign')) {
            $query->where('selection_campaigns.name', $campaign);
        }

        if ($investigator = $request->get('investigator')) {
            $query->where('home_investigations.assigned_investigator', $investigator);
        }

        if ($status = $request->get('status')) {
            // When filtering by 'Assigned', also include candidates without any investigation
            // (NULL status from LEFT JOIN) since they display as 'Assigned' in the UI
            if ($status === InvestigationStatus::Assigned->value) {
                $query->where(function ($q) use ($status) {
                    $q->where('home_investigations.status', $status)
                      ->orWhereNull('home_investigations.status');
                });
            } else {
                $query->where('home_investigations.status', $status);
            }
        }

        if ($dateFrom = $request->get('dateFrom')) {
            $query->whereDate('home_investigations.visit_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('dateTo')) {
            $query->whereDate('home_investigations.visit_date', '<=', $dateTo);
        }

        // Default sort: push NULL visit_dates (no investigation) to the end
        $query->orderByRaw('home_investigations.visit_date IS NULL, home_investigations.visit_date ASC, candidates.id ASC');

        $perPage = (int) $request->get('perPage', 20);
        // Use simplePaginate to skip expensive COUNT query — only loads "perPage + 1" rows
        $paginator = $query->simplePaginate($perPage);

        $data = collect($paginator->items())->map(function ($item) {
            return [
                'candidateId' => (string) $item->id,
                'candidateName' => trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? '')),
                'campaign' => $item->campaign ?? '',
                'assignedInvestigator' => $item->assigned_investigator ?? '',
                'visitDate' => $item->visit_date ? date('d/m/Y', strtotime($item->visit_date)) : null,
                'status' => $item->status ?? 'Assigned',
                'gender' => $item->gender ?? '',
                'phoneNumber' => $item->phone ?? '',
                'currentAddress' => $item->current_address ?? '',
            ];
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'page' => $paginator->currentPage(),
                'perPage' => $paginator->perPage(),
                'hasMorePages' => $paginator->hasMorePages(),
            ],
        ]);
    }

    /**
     * 3.3 Investigation Form Data
     * GET /home-investigation/candidates/{candidateId}
     *
     * If no investigation record exists yet, returns default form data
     * populated from the candidate record so the user can start filling it in.
     */
    public function showByCandidate(string $candidateId): JsonResponse
    {
        $investigation = HomeInvestigation::where('candidate_id', $candidateId)->first();

        if (!$investigation) {
            // No investigation yet — return default data from the candidates table
            $candidate = Candidate::with('campaign')->find($candidateId);
            if (!$candidate) {
                return response()->json(['error' => 'Candidate not found'], 404);
            }

            return response()->json([
                'candidateId' => (string) $candidateId,
                'candidateName' => trim(($candidate->first_name ?? '') . ' ' . ($candidate->last_name ?? '')),
                'campaign' => $candidate->campaign?->name ?? '',
                'gender' => $candidate->gender ?? '',
                'phoneNumber' => $candidate->phone ?? '',
                'currentAddress' => '',
                'assignedInvestigator' => '',
                'currentStatus' => InvestigationStatus::Assigned->value,
                'visitDate' => null,
                'location' => null,
                'gpsCoordinates' => null,
                'peopleMet' => null,
                'observations' => null,
                'findings' => null,
                'recommendation' => null,
                'reason' => null,
            ]);
        }

        $this->authorizeView($investigation);

        return response()->json($this->formatInvestigationDetail($investigation));
    }

    /**
     * 3.4 Save Draft
     * PUT /home-investigation/candidates/{candidateId}/draft
     *
     * Creates a new investigation record if none exists yet,
     * otherwise updates the existing draft.
     */
    public function saveDraft(SaveDraftRequest $request, string $candidateId): JsonResponse
    {
        $investigation = HomeInvestigation::where('candidate_id', $candidateId)->first();

        if (!$investigation) {
            // No investigation yet — create one with candidate data + draft fields
            $candidate = Candidate::with('campaign')->find($candidateId);
            if (!$candidate) {
                return response()->json(['error' => 'Candidate not found'], 404);
            }

            $data = $this->mapFormFields($request->validated());
            $data['candidate_id'] = (int) $candidateId;
            $data['candidate_name'] = trim(($candidate->first_name ?? '') . ' ' . ($candidate->last_name ?? ''));
            $data['campaign'] = $candidate->campaign?->name ?? '';
            $data['campaign_id'] = $candidate->campaign_id;
            $data['gender'] = $candidate->gender;
            $data['phone_number'] = $candidate->phone;
            $data['status'] = InvestigationStatus::InProgress->value;
            $data['assigned_investigator'] = Auth::user()->name ?? '';

            $investigation = HomeInvestigation::create($data);

            $this->logHistory($investigation->id, 'Created');

            return response()->json($this->formatInvestigationDetail($investigation));
        }

        $this->authorizeView($investigation);

        // Prevent editing if already submitted
        if ($investigation->status === InvestigationStatus::Submitted->value) {
            return response()->json(['error' => 'Cannot edit a submitted investigation'], 400);
        }

        $data = $this->mapFormFields($request->validated());

        // Update status to In Progress if currently Assigned
        if ($investigation->status === InvestigationStatus::Assigned->value) {
            $data['status'] = InvestigationStatus::InProgress->value;
        }

        $investigation->update($data);

        return response()->json($this->formatInvestigationDetail($investigation));
    }

    /**
     * 3.5 Submit Investigation
     * PUT /home-investigation/candidates/{candidateId}/submit
     *
     * Creates a new investigation record if none exists yet and immediately submits it.
     */
    public function submit(SubmitInvestigationRequest $request, string $candidateId): JsonResponse
    {
        $investigation = HomeInvestigation::where('candidate_id', $candidateId)->first();

        if (!$investigation) {
            // No investigation yet — create one with candidate data + submitted fields
            $candidate = Candidate::with('campaign')->find($candidateId);
            if (!$candidate) {
                return response()->json(['error' => 'Candidate not found'], 404);
            }

            $data = $this->mapFormFields($request->validated());
            $data['candidate_id'] = (int) $candidateId;
            $data['candidate_name'] = trim(($candidate->first_name ?? '') . ' ' . ($candidate->last_name ?? ''));
            $data['campaign'] = $candidate->campaign?->name ?? '';
            $data['campaign_id'] = $candidate->campaign_id;
            $data['gender'] = $candidate->gender;
            $data['phone_number'] = $candidate->phone;
            $data['status'] = InvestigationStatus::Submitted->value;
            $data['submitted_at'] = now();
            $data['assigned_investigator'] = Auth::user()->name ?? '';

            $investigation = HomeInvestigation::create($data);

            // Update candidate status to 'Investigated' so they appear in voting page
            Candidate::where('id', $candidateId)->update(['status' => 'Investigated']);

            $this->logHistory($investigation->id, 'Created');
            $this->logHistory($investigation->id, 'Submitted');

            return response()->json($this->formatInvestigationDetail($investigation));
        }

        $this->authorizeView($investigation);

        // Prevent resubmission
        if ($investigation->status === InvestigationStatus::Submitted->value) {
            return response()->json(['error' => 'Investigation is already submitted'], 400);
        }

        $data = $this->mapFormFields($request->validated());
        $data['status'] = InvestigationStatus::Submitted->value;
        $data['submitted_at'] = now();

        $investigation->update($data);

        // Update candidate status to 'Investigated' so they appear in voting page
        Candidate::where('id', $candidateId)->update(['status' => 'Investigated']);

        // Log to history
        $this->logHistory($investigation->id, 'Submitted');

        return response()->json($this->formatInvestigationDetail($investigation));
    }

    /**
     * 3.6 Selection List: Campaigns
     * GET /home-investigation/campaigns
     */
    public function campaigns(): JsonResponse
    {
        $campaigns = SelectCampaing::whereNotNull('name')
            ->orderBy('name')
            ->pluck('name');

        return response()->json(['data' => $campaigns]);
    }

    /**
     * 3.6 Selection List: Investigators
     * GET /home-investigation/investigators
     */
    public function investigators(): JsonResponse
    {
        // Get users with investigator-type roles (Officer, Staff, etc.)
        $investigatorRoleNames = ['Investigator', 'Staff', 'Officer'];
        $roleIds = Role::whereIn('name', $investigatorRoleNames)->pluck('id');

        $investigators = User::whereIn('role_id', $roleIds)
            ->whereNotNull('name')
            ->orderBy('name')
            ->pluck('name');

        return response()->json(['data' => $investigators]);
    }

    /**
     * 3.6 Selection List: Statuses
     * GET /home-investigation/statuses
     */
    public function statuses(): JsonResponse
    {
        return response()->json([
            'data' => [
                InvestigationStatus::Assigned->value,
                InvestigationStatus::InProgress->value,
                InvestigationStatus::Submitted->value,
            ],
        ]);
    }

    // =========================================================================
    // 3.7 Attachments
    // =========================================================================

    /**
     * 3.7.1 List Attachments
     * GET /home-investigation/candidates/{candidateId}/attachments
     */
    public function listAttachments(string $candidateId): JsonResponse
    {
        $investigation = HomeInvestigation::where('candidate_id', $candidateId)->first();

        if (!$investigation) {
            return response()->json(['data' => []]);
        }

        $attachments = HomeInvestigationFile::where('home_investigation_id', $investigation->id)
            ->get()
            ->map(function ($file) {
                return [
                    'id' => (string) $file->id,
                    'name' => $file->file_name,
                    'type' => $file->file_type,
                    'size' => (int) $file->file_size,
                    'uploadDate' => $file->created_at ? $file->created_at->toIso8601String() : null,
                    'url' => url('uploads/' . $file->file_path),
                ];
            });

        return response()->json(['data' => $attachments]);
    }

    /**
     * 3.7.2 Upload Attachment
     * POST /home-investigation/candidates/{candidateId}/attachments
     *
     * Auto-creates a draft investigation if none exists yet.
     */
    public function uploadAttachment(Request $request, string $candidateId): JsonResponse
    {
        $investigation = HomeInvestigation::where('candidate_id', $candidateId)->first();

        if (!$investigation) {
            // Auto-create a draft investigation
            $candidate = Candidate::with('campaign')->find($candidateId);
            if (!$candidate) {
                return response()->json(['error' => 'Candidate not found'], 404);
            }

            $investigation = HomeInvestigation::create([
                'candidate_id' => (int) $candidateId,
                'candidate_name' => trim(($candidate->first_name ?? '') . ' ' . ($candidate->last_name ?? '')),
                'campaign' => $candidate->campaign?->name ?? '',
                'campaign_id' => $candidate->campaign_id,
                'gender' => $candidate->gender,
                'phone_number' => $candidate->phone,
                'status' => InvestigationStatus::InProgress->value,
                'assigned_investigator' => Auth::user()->name ?? '',
            ]);

            $this->logHistory($investigation->id, 'Created');
        }

        // Validate file
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240', // 10 MB
                'mimes:jpeg,png,gif,webp,pdf,docx',
            ],
        ]);

        $file = $request->file('file');
        $mimeType = $file->getMimeType();

        // Determine file type category
        if (str_starts_with($mimeType, 'image/')) {
            $fileType = 'image';
        } elseif ($mimeType === 'application/pdf') {
            $fileType = 'pdf';
        } elseif ($mimeType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            $fileType = 'docx';
        } else {
            $fileType = 'other';
        }

        // Store file
        $filePath = $file->store('home-investigation-files', 'public');

        $attachment = HomeInvestigationFile::create([
            'home_investigation_id' => $investigation->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_size' => $file->getSize(),
            'mime_type' => $mimeType,
        ]);

        return response()->json([
            'id' => (string) $attachment->id,
            'name' => $attachment->file_name,
            'type' => $attachment->file_type,
            'size' => (int) $attachment->file_size,
            'uploadDate' => $attachment->created_at->toIso8601String(),
            'url' => url('uploads/' . $attachment->file_path),
        ], 201);
    }

    /**
     * 3.7.3 Delete Attachment
     * DELETE /home-investigation/candidates/{candidateId}/attachments/{attachmentId}
     */
    public function deleteAttachment(string $candidateId, int $attachmentId): JsonResponse
    {
        $investigation = HomeInvestigation::where('candidate_id', $candidateId)->first();

        if (!$investigation) {
            return response()->json(['error' => 'Candidate not found'], 404);
        }

        $attachment = HomeInvestigationFile::where('id', $attachmentId)
            ->where('home_investigation_id', $investigation->id)
            ->first();

        if (!$attachment) {
            return response()->json(['error' => 'Attachment not found'], 404);
        }

        // Delete file from storage
        Storage::disk('public')->delete($attachment->file_path);

        // Delete record
        $attachment->delete();

        return response()->noContent();
    }

    // =========================================================================
    // 3.8 Legacy 5-Status Endpoints
    // =========================================================================

    /**
     * 3.8.1 List Investigations
     * GET /home-investigation/investigations
     */
    public function index(Request $request): JsonResponse
    {
        $query = HomeInvestigation::query();

        // Filters
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('campaign', 'like', "%{$search}%")
                  ->orWhere('assigned_investigator', 'like', "%{$search}%");
            });
        }

        if ($campaign = $request->get('campaign')) {
            $query->where('campaign', $campaign);
        }

        if ($investigatorId = $request->get('investigatorId')) {
            $query->where('investigator_id', $investigatorId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->get('dateFrom')) {
            $query->whereDate('visit_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('dateTo')) {
            $query->whereDate('visit_date', '<=', $dateTo);
        }

        // Default sort: most recent first
        $query->orderBy('updated_at', 'desc');

        $perPage = (int) $request->get('perPage', 10);
        $paginator = $query->paginate($perPage);

        $data = collect($paginator->items())->map(function ($item) {
            return [
                'id' => (string) $item->id,
                'candidateId' => $item->candidate_id,
                'candidateName' => $item->candidate_name,
                'candidatePhoto' => null,
                'gender' => $item->gender,
                'school' => null,
                'campaign' => $item->campaign,
                'investigatorId' => (string) $item->investigator_id,
                'investigatorName' => $item->assigned_investigator,
                'scheduledDate' => $item->visit_date ? $item->visit_date->format('Y-m-d') : null,
                'visitDate' => $item->visit_date ? $item->visit_date->format('Y-m-d') : null,
                'status' => $item->status,
                'recommendation' => $item->recommendation,
                'summary' => $item->summary,
                'notes' => $item->notes,
                'createdAt' => $item->created_at ? $item->created_at->toIso8601String() : null,
                'updatedAt' => $item->updated_at ? $item->updated_at->toIso8601String() : null,
                'submittedAt' => $item->submitted_at ? $item->submitted_at->toIso8601String() : null,
                'approvedAt' => $item->approved_at ? $item->approved_at->toIso8601String() : null,
                'rejectedAt' => $item->rejected_at ? $item->rejected_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'page' => $paginator->currentPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
                'totalPages' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * 3.8.2 Get Single Investigation
     * GET /home-investigation/investigations/{investigation}
     */
    public function show(int $id): JsonResponse
    {
        $investigation = HomeInvestigation::find($id);

        if (!$investigation) {
            return response()->json(['error' => 'Investigation not found'], 404);
        }

        return response()->json($this->formatLegacyInvestigation($investigation));
    }

    /**
     * 3.8.3 Create Investigation
     * POST /home-investigation/investigations
     */
    public function store(StoreLegacyInvestigationRequest $request): JsonResponse
    {
        $this->authorizeManagerOnly();

        $data = $request->validated();

        $investigation = HomeInvestigation::create([
            'candidate_name' => $data['candidateName'],
            'campaign' => $data['campaign'],
            'visit_date' => $data['scheduledDate'] ?? null,
            'investigator_id' => $data['investigatorId'] ?? null,
            'assigned_investigator' => $data['investigatorName'] ?? null,
            'status' => 'Pending',
        ]);

        // Log history
        $this->logHistory($investigation->id, 'Created');

        return response()->json(
            $this->formatLegacyInvestigation($investigation),
            201
        );
    }

    /**
     * 3.8.4 Update Investigation
     * PUT /home-investigation/investigations/{investigation}
     */
    public function update(UpdateLegacyInvestigationRequest $request, int $id): JsonResponse
    {
        $this->authorizeManagerOnly();

        $investigation = HomeInvestigation::find($id);

        if (!$investigation) {
            return response()->json(['error' => 'Investigation not found'], 404);
        }

        $data = [];
        $validated = $request->validated();

        if (isset($validated['candidateName'])) {
            $data['candidate_name'] = $validated['candidateName'];
        }
        if (isset($validated['campaign'])) {
            $data['campaign'] = $validated['campaign'];
        }
        if (array_key_exists('scheduledDate', $validated)) {
            $data['visit_date'] = $validated['scheduledDate'];
        }
        if (array_key_exists('investigatorId', $validated)) {
            $data['investigator_id'] = $validated['investigatorId'];
        }
        if (array_key_exists('investigatorName', $validated)) {
            $data['assigned_investigator'] = $validated['investigatorName'];
        }
        if (isset($validated['visitDate'])) {
            $data['visit_date'] = $validated['visitDate'];
        }
        if (isset($validated['recommendation'])) {
            $data['recommendation'] = $validated['recommendation'];
        }
        if (isset($validated['notes'])) {
            $data['notes'] = $validated['notes'];
        }
        if (isset($validated['summary'])) {
            $data['summary'] = $validated['summary'];
        }

        $investigation->update($data);

        return response()->json($this->formatLegacyInvestigation($investigation));
    }

    /**
     * 3.8.5 Delete Investigation
     * DELETE /home-investigation/investigations/{investigation}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorizeManagerOnly();

        $investigation = HomeInvestigation::find($id);

        if (!$investigation) {
            return response()->json(['error' => 'Investigation not found'], 404);
        }

        // Delete associated files from storage and remove records
        foreach ($investigation->files as $file) {
            Storage::disk('public')->delete($file->file_path);
            $file->delete(); // Hard delete the file record (data is being removed)
        }

        $investigation->delete(); // Soft delete

        return response()->noContent();
    }

    /**
     * 3.8.6 Save Draft (5-status)
     * PUT /home-investigation/investigations/{id}/draft
     */
    public function saveDraftLegacy(Request $request, int $id): JsonResponse
    {
        $investigation = HomeInvestigation::find($id);

        if (!$investigation) {
            return response()->json(['error' => 'Investigation not found'], 404);
        }

        $data = [];

        if ($request->has('visitDate')) {
            $data['visit_date'] = $request->input('visitDate');
        }
        if ($request->has('recommendation')) {
            $data['recommendation'] = $request->input('recommendation');
        }
        if ($request->has('notes')) {
            $data['notes'] = $request->input('notes');
        }
        if ($request->has('observations')) {
            $data['observations'] = $request->input('observations');
        }
        if ($request->has('findings')) {
            $data['findings'] = $request->input('findings');
        }

        // Update status only if it's Pending
        if ($investigation->status === 'Pending') {
            $data['status'] = 'In Progress';
        }

        $investigation->update($data);

        return response()->json($this->formatLegacyInvestigation($investigation));
    }

    /**
     * 3.8.7 Submit for Review (5-status)
     * PUT /home-investigation/investigations/{id}/submit
     */
    public function submitLegacy(int $id): JsonResponse
    {
        $investigation = HomeInvestigation::find($id);

        if (!$investigation) {
            return response()->json(['error' => 'Investigation not found'], 404);
        }

        $investigation->update([
            'status' => 'Submitted',
            'submitted_at' => now(),
        ]);

        $this->logHistory($investigation->id, 'Submitted');

        return response()->json($this->formatLegacyInvestigation($investigation));
    }

    /**
     * 3.8.8 Approve Investigation
     * PUT /home-investigation/investigations/{id}/approve
     */
    public function approveInvestigation(int $id): JsonResponse
    {
        $this->authorizeManagerOnly();

        $investigation = HomeInvestigation::find($id);

        if (!$investigation) {
            return response()->json(['error' => 'Investigation not found'], 404);
        }

        if ($investigation->status !== 'Submitted') {
            return response()->json([
                'error' => 'Only submitted investigations can be approved',
            ], 400);
        }

        $investigation->update([
            'status' => 'Approved',
            'approved_at' => now(),
        ]);

        $this->logHistory($investigation->id, 'Approved');

        return response()->json($this->formatLegacyInvestigation($investigation));
    }

    /**
     * 3.8.9 Reject Investigation
     * PUT /home-investigation/investigations/{id}/reject
     */
    public function rejectInvestigation(RejectInvestigationRequest $request, int $id): JsonResponse
    {
        $this->authorizeManagerOnly();

        $investigation = HomeInvestigation::find($id);

        if (!$investigation) {
            return response()->json(['error' => 'Investigation not found'], 404);
        }

        if ($investigation->status !== 'Submitted') {
            return response()->json([
                'error' => 'Only submitted investigations can be rejected',
            ], 400);
        }

        $investigation->update([
            'status' => 'Rejected',
            'rejected_at' => now(),
            'rejection_reason' => $request->input('reason'),
        ]);

        $this->logHistory($investigation->id, 'Rejected', $request->input('reason'));

        return response()->json($this->formatLegacyInvestigation($investigation));
    }

    /**
     * 3.8.10 Get Investigation History
     * GET /home-investigation/investigations/{id}/history
     */
    public function getInvestigationHistory(int $id): JsonResponse
    {
        $investigation = HomeInvestigation::find($id);

        if (!$investigation) {
            return response()->json(['error' => 'Investigation not found'], 404);
        }

        $history = InvestigationHistory::where('investigation_id', $investigation->id)
            ->orderBy('timestamp', 'desc')
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => (string) $entry->id,
                    'investigationId' => (string) $entry->investigation_id,
                    'action' => $entry->action,
                    'userId' => $entry->user_id,
                    'userName' => $entry->user_name,
                    'timestamp' => $entry->timestamp->toIso8601String(),
                    'notes' => $entry->notes,
                ];
            });

        return response()->json(['data' => $history]);
    }

    // =========================================================================
    // 3.8.11 - 3.8.13 Dashboard Endpoints
    // =========================================================================

    /**
     * 3.8.11 Dashboard Stats
     * GET /home-investigation/dashboard/stats
     */
    public function dashboardStats(): JsonResponse
    {
        $stats = [
            'pending' => HomeInvestigation::where('status', 'Pending')->count(),
            'inProgress' => HomeInvestigation::where('status', 'In Progress')->count(),
            'submitted' => HomeInvestigation::where('status', 'Submitted')->count(),
            'approved' => HomeInvestigation::where('status', 'Approved')->count(),
            'rejected' => HomeInvestigation::where('status', 'Rejected')->count(),
            'total' => HomeInvestigation::count(),
        ];

        return response()->json($stats);
    }

    /**
     * 3.8.12 Investigator Workload
     * GET /home-investigation/dashboard/workload
     */
    public function dashboardWorkload(): JsonResponse
    {
        $workload = HomeInvestigation::select(
            'investigator_id',
            'assigned_investigator',
            DB::raw("COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending"),
            DB::raw("COUNT(CASE WHEN status = 'In Progress' THEN 1 END) as inProgress"),
            DB::raw("COUNT(CASE WHEN status = 'Submitted' THEN 1 END) as submitted"),
            DB::raw('COUNT(*) as total')
        )
            ->whereNotNull('assigned_investigator')
            ->groupBy('investigator_id', 'assigned_investigator')
            ->get()
            ->map(function ($item) {
                return [
                    'investigatorId' => (string) $item->investigator_id,
                    'investigatorName' => $item->assigned_investigator,
                    'pending' => (int) $item->pending,
                    'inProgress' => (int) $item->inProgress,
                    'submitted' => (int) $item->submitted,
                    'total' => (int) $item->total,
                ];
            });

        return response()->json(['data' => $workload]);
    }

    /**
     * 3.8.13 Chart Data
     * GET /home-investigation/dashboard/chart
     */
    public function dashboardChart(): JsonResponse
    {
        // Portable month extraction: fetch records and group by month in PHP
        $records = HomeInvestigation::select('created_at')
            ->whereYear('created_at', now()->year)
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at ? $item->created_at->format('M') : 'Unknown';
            })
            ->map(function ($items, $month) {
                return [
                    'month' => $month,
                    'count' => $items->count(),
                ];
            })
            ->values();

        return response()->json(['data' => $records]);
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Check if the authenticated user has an Investigator role.
     */
    private function isInvestigator(?int $roleId): bool
    {
        $investigatorRoles = ['Investigator', 'Staff'];
        $role = $this->getUserRole($roleId);
        return $role && in_array($role->name ?? '', $investigatorRoles);
    }

    /**
     * Check if the authenticated user has a Manager or Admin role.
     */
    private function isManager(?int $roleId): bool
    {
        $managerRoles = ['Manager', 'Admin'];
        $role = $this->getUserRole($roleId);
        return $role && in_array($role->name ?? '', $managerRoles);
    }

    /**
     * Cache the user role lookup to avoid repeated DB queries.
     */
    private ?\App\Models\Role $cachedRole = null;

    private function getUserRole(?int $roleId): ?\App\Models\Role
    {
        if ($this->cachedRole === null && $roleId) {
            $this->cachedRole = \App\Models\Role::find($roleId);
        }
        return $this->cachedRole;
    }

    /**
     * Authorize that the user can view/edit this investigation.
     */
    private function authorizeView(HomeInvestigation $investigation): void
    {
        $user = Auth::user();
        $userRole = $user->role_id ?? null;

        // Manager/Admin can view all
        if ($this->isManager($userRole)) {
            return;
        }

        // Investigator can only view own
        if ($investigation->assigned_investigator !== $user->name) {
            abort(403, 'You are not authorized to access this investigation');
        }
    }

    /**
     * Authorize that only managers/admins can perform this action.
     */
    private function authorizeManagerOnly(): void
    {
        $user = Auth::user();
        $userRole = $user->role_id ?? null;

        if (!$this->isManager($userRole)) {
            abort(403, 'Only managers can perform this action');
        }
    }

    /**
     * Map camelCase form fields to snake_case database columns.
     */
    private function mapFormFields(array $validated): array
    {
        $mapping = [
            'visitDate' => 'visit_date',
            'gpsCoordinates' => 'gps_coordinates',
            'peopleMet' => 'people_met',
            'observations' => 'observations',
            'findings' => 'findings',
            'recommendation' => 'recommendation',
            'reason' => 'reason',
            'location' => 'location',
        ];

        $data = [];
        foreach ($mapping as $camel => $snake) {
            if (array_key_exists($camel, $validated)) {
                $data[$snake] = $validated[$camel];
            }
        }

        return $data;
    }

    /**
     * Format investigation detail for 3-status API response (camelCase).
     */
    private function formatInvestigationDetail(HomeInvestigation $investigation): array
    {
        return [
            'candidateId' => (string) $investigation->candidate_id,
            'candidateName' => $investigation->candidate_name,
            'campaign' => $investigation->campaign,
            'gender' => $investigation->gender,
            'phoneNumber' => $investigation->phone_number,
            'currentAddress' => $investigation->current_address,
            'assignedInvestigator' => $investigation->assigned_investigator,
            'currentStatus' => $investigation->status,
            'visitDate' => $investigation->visit_date ? $investigation->visit_date->format('Y-m-d') : null,
            'location' => $investigation->location,
            'gpsCoordinates' => $investigation->gps_coordinates,
            'peopleMet' => $investigation->people_met,
            'observations' => $investigation->observations,
            'findings' => $investigation->findings,
            'recommendation' => $investigation->recommendation,
            'reason' => $investigation->reason,
        ];
    }

    /**
     * Format investigation for 5-status legacy API response.
     */
    private function formatLegacyInvestigation(HomeInvestigation $investigation): array
    {
        return [
            'id' => (string) $investigation->id,
            'candidateId' => $investigation->candidate_id,
            'candidateName' => $investigation->candidate_name,
            'candidatePhoto' => null,
            'gender' => $investigation->gender,
            'school' => null,
            'campaign' => $investigation->campaign,
            'investigatorId' => (string) $investigation->investigator_id,
            'investigatorName' => $investigation->assigned_investigator,
            'scheduledDate' => $investigation->visit_date ? $investigation->visit_date->format('Y-m-d') : null,
            'visitDate' => $investigation->visit_date ? $investigation->visit_date->format('Y-m-d') : null,
            'status' => $investigation->status,
            'recommendation' => $investigation->recommendation,
            'summary' => $investigation->summary,
            'notes' => $investigation->notes,
            'createdAt' => $investigation->created_at ? $investigation->created_at->toIso8601String() : null,
            'updatedAt' => $investigation->updated_at ? $investigation->updated_at->toIso8601String() : null,
            'submittedAt' => $investigation->submitted_at ? $investigation->submitted_at->toIso8601String() : null,
            'approvedAt' => $investigation->approved_at ? $investigation->approved_at->toIso8601String() : null,
            'rejectedAt' => $investigation->rejected_at ? $investigation->rejected_at->toIso8601String() : null,
        ];
    }

    /**
     * Log an action to the investigation_history table.
     */
    private function logHistory(int $investigationId, string $action, ?string $notes = null): void
    {
        $user = Auth::user();

        InvestigationHistory::create([
            'investigation_id' => $investigationId,
            'action' => $action,
            'user_id' => (string) ($user->id ?? 'system'),
            'user_name' => $user->name ?? 'System',
            'notes' => $notes,
            'timestamp' => now(),
        ]);
    }
}
