<?php

namespace Services;

use App\Models\AssessmentForm;
use App\Models\AssessmentRespone;
use Repositories\AssessmentResponseRepository;

class AssessmentResponseServices
{
    public function __construct(protected AssessmentResponseRepository $assessmentResponseRepository) {}

    public function list(array $filters = [])
    {
        return $this->assessmentResponseRepository->list($filters);
    }

    public function create(array $data): AssessmentRespone
    {
        $form = AssessmentForm::findOrFail($data['form_id']);
        $data['total_score'] = $form->scoreResponse($data['answers']);

        return $this->assessmentResponseRepository->create($data);
    }

    public function find(AssessmentRespone $response): AssessmentRespone
    {
        return $this->assessmentResponseRepository->find($response);
    } 

    public function update(AssessmentRespone $response, array $data): AssessmentRespone
    {
        if (isset($data['answers'])) {
            $form = $response->form;
            $data['total_score'] = $form->scoreResponse($data['answers']);
        }

        return $this->assessmentResponseRepository->update($response, $data);
    }

    public function delete(AssessmentRespone $response): void
    {
        $this->assessmentResponseRepository->delete($response);
    }

    public function submit(int $formId, int $candidateId, array $answers): AssessmentRespone
    {
        $form = AssessmentForm::findOrFail($formId);

        $validation = $form->validateResponse($answers);

        if ($validation->fails()) {
            throw new \Illuminate\Validation\ValidationException($validation);
        }

        $totalScore = $form->scoreResponse($answers);

        $existing = $this->assessmentResponseRepository->findByFormAndCandidate($formId, $candidateId);

        if ($existing) {
            return $this->assessmentResponseRepository->update($existing, [
                'answers' => $answers,
                'total_score' => $totalScore,
            ]);
        }

        return $this->assessmentResponseRepository->create([
            'form_id' => $formId,
            'candidate_id' => $candidateId,
            'answers' => $answers,
            'total_score' => $totalScore,
        ]);
    }
}
