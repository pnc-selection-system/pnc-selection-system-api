<?php

namespace App\Http\Controllers\Api\AssessmentForm;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AssessmentForm\StoreAssessmentFormRequest;
use App\Http\Requests\Api\AssessmentForm\UpdateAssessmentFormRequest;
use App\Models\AssessmentForm;
use App\Models\AssessmentQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Services\AssessmentFormServices;

class AssessmentFormController extends Controller
{
    public function __construct(protected AssessmentFormServices $assessmentFormService) {}

    public function index(): JsonResponse
    {
        $forms = $this->assessmentFormService->list(request()->all());

        return ApiResponse::success(
            $forms->map(fn ($form) => $this->formatForm($form)),
            'Assessment forms retrieved successfully'
        );
    }

    public function store(StoreAssessmentFormRequest $request): JsonResponse
    {
        $form = $this->assessmentFormService->create($request->validated());
        $this->syncQuestions($form);

        return ApiResponse::created($this->formatForm($form), 'Assessment form created successfully');
    }

    public function show(AssessmentForm $assessmentForm): JsonResponse
    {
        return ApiResponse::success(
            $this->formatForm($this->assessmentFormService->find($assessmentForm)),
            'Assessment form retrieved successfully'
        );
    }

    public function update(UpdateAssessmentFormRequest $request, AssessmentForm $assessmentForm): JsonResponse
    {
        $form = $this->assessmentFormService->update($assessmentForm, $request->validated());
        $this->syncQuestions($form);

        return ApiResponse::success($this->formatForm($form), 'Assessment form updated successfully');
    }

    public function questions(AssessmentForm $assessmentForm): JsonResponse
    {
        if ($assessmentForm->questions()->doesntExist()) {
            $this->syncQuestions($assessmentForm);
        }

        $questions = $assessmentForm->questions()->orderBy('order')->get()->map(fn ($q) => [
            'id'      => $q->id,
            'key'     => $q->key,
            'label'   => $q->label,
            'type'    => $q->type,
            'options' => $q->options,
            'rules'   => $q->rules,
            'weight'  => (float) $q->weight,
            'order'   => $q->order,
        ]);

        return ApiResponse::success($questions, 'Questions retrieved successfully');
    }

    public function addQuestion(Request $request, AssessmentForm $assessmentForm): JsonResponse
    {
        $request->validate([
            'label'     => 'required|string|max:255',
            'type'      => 'required|in:short_text,scale_1_5,single_choice,multi_choice,text,number,rating',
            'options'   => 'nullable|array',
            'options.*' => 'string',
            'rules'     => 'nullable|array',
            'weight'    => 'nullable|numeric|min:0|max:100',
        ]);

        // Generate a unique key from the label
        $baseKey  = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $request->label));
        $key      = $baseKey;
        $counter  = 1;
        while (AssessmentQuestion::where('form_id', $assessmentForm->id)->where('key', $key)->exists()) {
            $key = $baseKey . '_' . $counter++;
        }

        $order    = $assessmentForm->questions()->max('order') + 1;

        $question = AssessmentQuestion::create([
            'form_id' => $assessmentForm->id,
            'key'     => $key,
            'label'   => $request->label,
            'type'    => $request->type,
            'options' => $request->options,
            'rules'   => $request->rules ?? ['required' => false],
            'weight'  => $request->weight ?? 0,
            'order'   => $order,
        ]);

        // Sync back to form schema
        $this->syncSchemaFromQuestions($assessmentForm);

        return ApiResponse::created([
            'id'      => $question->id,
            'key'     => $question->key,
            'label'   => $question->label,
            'type'    => $question->type,
            'options' => $question->options,
            'rules'   => $question->rules,
            'weight'  => (float) $question->weight,
            'order'   => $question->order,
        ], 'Question added successfully');
    }

    public function removeQuestion(AssessmentForm $assessmentForm, AssessmentQuestion $question): JsonResponse
    {
        abort_if($question->form_id !== $assessmentForm->id, 404);

        $question->delete();
        $this->syncSchemaFromQuestions($assessmentForm);

        return ApiResponse::ok('Question removed successfully');
    }

    public function destroy(AssessmentForm $assessmentForm): JsonResponse
    {
        $this->assessmentFormService->delete($assessmentForm);

        return ApiResponse::ok('Assessment form deleted successfully');
    }

    private function formatForm(AssessmentForm $form): array
    {
        return [
            'id'             => $form->id,
            'campaign_id'    => $form->campaign_id,
            'name'           => $form->name,
            'pass_threshold' => (float) $form->pass_threshold,
            'fields'         => $form->fields(),
        ];
    }

    private function syncSchemaFromQuestions(AssessmentForm $form): void
    {
        $fields = $form->questions()->orderBy('order')->get()->map(fn ($q) => array_filter([
            'key'     => $q->key,
            'label'   => $q->label,
            'type'    => $q->type,
            'options' => $q->options,
            'rules'   => $q->rules ?? [],
            'weight'  => (float) $q->weight,
        ], fn ($v) => $v !== null))->values()->toArray();

        $form->update(['schema' => ['fields' => $fields]]);
    }

    private function syncQuestions(AssessmentForm $form): void
    {
        AssessmentQuestion::where('form_id', $form->id)->delete();

        foreach ($form->fields() as $index => $field) {
            AssessmentQuestion::create([
                'form_id' => $form->id,
                'key'     => $field['key'],
                'label'   => $field['label'],
                'type'    => $field['type'],
                'options' => $field['options'] ?? null,
                'rules'   => $field['rules'] ?? null,
                'weight'  => $field['weight'] ?? 0,
                'order'   => $index,
            ]);
        }
    }
}
