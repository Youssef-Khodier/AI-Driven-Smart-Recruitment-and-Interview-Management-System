<?php

namespace Database\Seeders;

use App\Enums\AssessmentQuestionType;
use App\Enums\AssessmentType;
use App\Enums\JobRequisitionStatus;
use App\Models\Assessment;
use App\Models\Department;
use App\Models\JobRequisition;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssessmentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::firstOrCreate(['name' => 'Engineering']);
        $hr = User::where('email', env('FIRST_HR_ADMIN_EMAIL', 'hr.admin@example.com'))->first();

        if (! $hr) {
            return;
        }

        $job = JobRequisition::firstOrCreate([
            'title' => 'Demo Laravel Developer',
        ], [
            'department_id' => $department->department_id,
            'description' => 'Demo requisition for technical assessment walkthroughs.',
            'requirements' => 'Laravel, PHP, MySQL, problem solving',
            'status' => JobRequisitionStatus::OPEN,
            'created_by' => $hr->user_id,
            'opened_at' => now(),
        ]);

        $assessment = Assessment::firstOrCreate([
            'job_id' => $job->job_id,
            'title' => 'Demo Technical Assessment',
        ], [
            'description' => 'Simulated assessment for academic demo evidence.',
            'type' => AssessmentType::TECHNICAL,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        $questions = [
            [AssessmentQuestionType::MCQ, 'EASY', 'Which framework is used by SRIM?', ['Laravel', 'Django', 'Rails'], 'Laravel', 2],
            [AssessmentQuestionType::THEORY, 'MEDIUM', 'Explain why server-side validation matters in recruitment workflows.', null, 'validation privacy security', 4],
            [AssessmentQuestionType::CODING_TEXT, 'MEDIUM', 'Write pseudocode to count matching skills between a job and candidate.', null, 'loop skills count match', 4],
        ];

        foreach ($questions as [$type, $difficulty, $text, $options, $answer, $points]) {
            Question::firstOrCreate([
                'assessment_id' => $assessment->assessment_id,
                'question_text' => $text,
            ], [
                'type' => $type,
                'difficulty_level' => $difficulty,
                'options' => $options,
                'correct_answer' => $answer,
                'points' => $points,
                'is_active' => true,
            ]);
        }
    }
}
