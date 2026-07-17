<?php

namespace Database\Seeders;

use App\Models\Cadidate;
use App\Models\AssessmentForm;
use App\Models\SelectCampaing;
use App\Models\Province;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TestDataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Skip if data already exists
        if (\App\Models\AssessmentForm::count() > 0) {
            $this->command->info('Test data already exists, skipping...');
            return;
        }
        // Create a test province if not exists
        $province = Province::firstOrCreate(
            ['name' => 'Phnom Penh'],
            ['name' => 'Phnom Penh']
        );

        // Create a test school if not exists
        $school = School::firstOrCreate(
            ['name' => 'Sample High School'],
            ['name' => 'Sample High School', 'province_id' => $province->id]
        );

        // Create a test campaign if not exists
        $campaign = SelectCampaing::firstOrCreate(
            ['name' => 'Test Campaign 2026'],
            [
                'name' => 'Test Campaign 2026',
                'year' => 2026,
                'condidate_total' => 100,
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'status' => 'Active',
            ]
        );

        // Create test assessment form
        $form = AssessmentForm::firstOrCreate(
            ['campaign_id' => $campaign->id],
            [
                'campaign_id' => $campaign->id,
                'name' => 'Interest Assessment 2026',
                'schema' => [
                    'fields' => [
                        [
                            'key' => 'why_this_programme',
                            'label' => 'Why this programme?',
                            'type' => 'text',
                            'rules' => ['required' => true, 'max' => 500],
                            'weight' => 2,
                        ],
                        [
                            'key' => 'commitment_level',
                            'label' => 'Commitment level',
                            'type' => 'scale_1_5',
                            'rules' => ['required' => true, 'min' => 1, 'max' => 5],
                            'weight' => 3,
                        ],
                        [
                            'key' => 'available_weekends',
                            'label' => 'Available on weekends?',
                            'type' => 'single_choice',
                            'options' => ['Yes', 'No'],
                            'rules' => ['required' => true],
                            'weight' => 1,
                        ],
                        [
                            'key' => 'new_short_text',
                            'label' => 'New short text question',
                            'type' => 'text',
                            'rules' => ['required' => false, 'max' => 255],
                            'weight' => 1,
                        ],
                        [
                            'key' => 'scale_question_1',
                            'label' => 'New scale question',
                            'type' => 'scale_1_5',
                            'rules' => ['required' => true, 'min' => 1, 'max' => 5],
                            'weight' => 1,
                        ],
                        [
                            'key' => 'scale_question_2',
                            'label' => 'New scale question',
                            'type' => 'scale_1_5',
                            'rules' => ['required' => true, 'min' => 1, 'max' => 5],
                            'weight' => 1,
                        ],
                        [
                            'key' => 'scale_question_3',
                            'label' => 'New scale question',
                            'type' => 'scale_1_5',
                            'rules' => ['required' => true, 'min' => 1, 'max' => 5],
                            'weight' => 1,
                        ],
                    ],
                ],
                'pass_threshold' => 60.00,
            ]
        );

        // Create test candidates
        Cadidate::firstOrCreate(
            ['email' => 'candidate1@example.com'],
            [
                'campaign_id' => $campaign->id,
                'province_id' => $province->id,
                'school_id' => $school->id,
                'first_name' => 'Sokha',
                'last_name' => 'Voeun',
                'gender' => 'Male',
                'dob' => '2005-01-15',
                'phone' => '0123456789',
                'email' => 'candidate1@example.com',
                'status' => 'Pending',
            ]
        );

        Cadidate::firstOrCreate(
            ['email' => 'candidate2@example.com'],
            [
                'campaign_id' => $campaign->id,
                'province_id' => $province->id,
                'school_id' => $school->id,
                'first_name' => 'Dara',
                'last_name' => 'Sok',
                'gender' => 'Female',
                'dob' => '2004-06-20',
                'phone' => '0123456790',
                'email' => 'candidate2@example.com',
                'status' => 'Pending',
            ]
        );

        $this->command->info('Test data seeded successfully!');
        $this->command->info('Form ID: ' . $form->id);
        $this->command->info('Candidate IDs: 1, 2');
    }
}
