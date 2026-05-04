<?php

namespace App\Http\Controllers\Candidate;

use App\Enums\ApplicationStatus;
use App\Enums\AssessmentAttemptStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\SaveAssessmentAnswerRequest;
use App\Http\Requests\Candidate\SubmitAssessmentRequest;
use App\Models\Application;
use App\Models\Assessment;
use App\Models\AssessmentIntegrityEvent;
use App\Models\CandidateAssessment;
use App\Models\CandidateAssessmentQuestion;
use App\Models\Submission;
use App\Support\SimulatedAssessmentScorer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AssessmentController extends Controller
{
    public function start(Application $application, Assessment $assessment): RedirectResponse
    {
        $candidate = request()->user()->candidate;

        if (! $candidate || $application->candidate_id !== $candidate->candidate_id || $application->job_id !== $assessment->job_id) {
            abort(403);
        }

        if ($application->status !== ApplicationStatus::ASSESSMENT) {
            return redirect()->route('candidate.applications.show', $application)
                ->withErrors(['assessment' => 'This application is not in the assessment stage.']);
        }

        if (! $assessment->is_active) {
            return redirect()->route('candidate.applications.show', $application)
                ->withErrors(['assessment' => 'This assessment is not available.']);
        }

        $attempt = CandidateAssessment::where('candidate_id', $candidate->candidate_id)
            ->where('assessment_id', $assessment->assessment_id)
            ->first();

        if ($attempt) {
            return $attempt->status === AssessmentAttemptStatus::IN_PROGRESS
                ? redirect()->route('candidate.assessments.show', $attempt)
                : redirect()->route('candidate.assessments.result', $attempt)
                    ->withErrors(['assessment' => 'You already have an assessment attempt for this test.']);
        }

        $attempt = DB::transaction(function () use ($application, $assessment, $candidate): CandidateAssessment {
            $now = now();
            $attempt = CandidateAssessment::create([
                'application_id' => $application->application_id,
                'candidate_id' => $candidate->candidate_id,
                'assessment_id' => $assessment->assessment_id,
                'start_time' => $now,
                'expires_at' => $now->copy()->addMinutes($assessment->duration_minutes),
                'status' => AssessmentAttemptStatus::IN_PROGRESS,
            ]);

            $assessment->activeQuestions()->inRandomOrder()->get()->values()->each(function ($question, int $index) use ($attempt): void {
                CandidateAssessmentQuestion::create([
                    'ca_id' => $attempt->ca_id,
                    'question_id' => $question->question_id,
                    'display_order' => $index + 1,
                    'question_type' => $question->type,
                    'question_text' => $question->question_text,
                    'options' => $question->options,
                    'correct_answer' => $question->correct_answer,
                    'points' => $question->points,
                ]);
            });

            return $attempt;
        });

        return redirect()->route('candidate.assessments.show', $attempt)
            ->with('status', 'Assessment started. Your answers are saved as you submit each answer.');
    }

    public function show(CandidateAssessment $attempt, SimulatedAssessmentScorer $scorer): View|RedirectResponse
    {
        Gate::authorize('view', $attempt);

        if ($this->expireIfNeeded($attempt, $scorer)) {
            return redirect()->route('candidate.assessments.result', $attempt)
                ->withErrors(['assessment' => 'Assessment time expired.']);
        }

        return view('candidate.assessments.show', [
            'title' => 'Technical Assessment',
            'attempt' => $attempt->load(['assessment.jobRequisition', 'attemptQuestions.submission']),
        ]);
    }

    public function saveAnswer(SaveAssessmentAnswerRequest $request, CandidateAssessment $attempt, CandidateAssessmentQuestion $attemptQuestion, SimulatedAssessmentScorer $scorer): RedirectResponse
    {
        Gate::authorize('update', $attempt);

        if ($attemptQuestion->ca_id !== $attempt->ca_id) {
            abort(404);
        }

        if ($this->expireIfNeeded($attempt, $scorer)) {
            return redirect()->route('candidate.assessments.result', $attempt)
                ->withErrors(['assessment' => 'Assessment time expired. Late answer changes were not saved.']);
        }

        Submission::updateOrCreate([
            'ca_id' => $attempt->ca_id,
            'attempt_question_id' => $attemptQuestion->attempt_question_id,
        ], [
            'question_id' => $attemptQuestion->question_id,
            'answer_text' => $request->validated('answer_text'),
            'saved_at' => now(),
        ]);

        return redirect()->route('candidate.assessments.show', $attempt)
            ->with('status', 'Answer saved.');
    }

    public function submit(SubmitAssessmentRequest $request, CandidateAssessment $attempt, SimulatedAssessmentScorer $scorer): RedirectResponse
    {
        Gate::authorize('submit', $attempt);

        if ($this->expireIfNeeded($attempt, $scorer)) {
            return redirect()->route('candidate.assessments.result', $attempt)
                ->withErrors(['assessment' => 'Assessment time expired. Your simulated score used answers saved before the deadline.']);
        }

        $score = $scorer->score($attempt);
        $attempt->submissions()->update(['finalized_at' => now()]);
        $attempt->update([
            'status' => AssessmentAttemptStatus::SUBMITTED,
            'score' => $score,
            'end_time' => now(),
        ]);

        return redirect()->route('candidate.assessments.result', $attempt)
            ->with('status', 'Assessment submitted. Your score is simulated and advisory.');
    }

    public function recordFocusEvent(Request $request, CandidateAssessment $attempt, SimulatedAssessmentScorer $scorer): RedirectResponse
    {
        Gate::authorize('recordFocusEvent', $attempt);

        $validated = $request->validate([
            'event_type' => ['required', Rule::in(['FOCUS_LOST', 'FOCUS_RETURNED'])],
            'visible_state' => ['nullable', 'string', 'max:40'],
        ]);

        if ($this->expireIfNeeded($attempt, $scorer)) {
            return redirect()->route('candidate.assessments.result', $attempt)
                ->withErrors(['assessment' => 'Assessment time expired.']);
        }

        AssessmentIntegrityEvent::create([
            'ca_id' => $attempt->ca_id,
            'event_type' => $validated['event_type'],
            'occurred_at' => now(),
            'metadata' => ['visible_state' => $validated['visible_state'] ?? null],
        ]);

        return redirect()->route('candidate.assessments.show', $attempt)
            ->with('status', 'Simulated proctoring event recorded.');
    }

    public function result(CandidateAssessment $attempt, SimulatedAssessmentScorer $scorer): View
    {
        Gate::authorize('view', $attempt);
        $this->expireIfNeeded($attempt, $scorer);

        return view('candidate.assessments.result', [
            'title' => 'Assessment Result',
            'attempt' => $attempt->refresh()->load(['assessment.jobRequisition', 'attemptQuestions.submission', 'integrityEvents']),
        ]);
    }

    private function expireIfNeeded(CandidateAssessment $attempt, SimulatedAssessmentScorer $scorer): bool
    {
        if ($attempt->status !== AssessmentAttemptStatus::IN_PROGRESS || ! $attempt->expires_at || now()->lt($attempt->expires_at)) {
            return false;
        }

        $score = $scorer->score($attempt, $attempt->expires_at);
        $attempt->submissions()->where('saved_at', '<=', $attempt->expires_at)->update(['finalized_at' => now()]);
        $attempt->update([
            'status' => AssessmentAttemptStatus::EXPIRED,
            'score' => $score,
            'end_time' => $attempt->expires_at,
        ]);

        return true;
    }
}
