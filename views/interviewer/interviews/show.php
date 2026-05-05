<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-3xl font-bold text-primary"><?= e($title) ?></h1>
        <a href="<?= e(url('interviewer.interviews.index')) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Interviews
        </a>
    </div>

    <?php if ($briefing['assignment']['role_in_panel'] === \App\Enums\InterviewAssignmentRole::OBSERVER->value): ?>
        <div class="bg-info-bg border border-info/20 text-info px-4 py-3 rounded-lg flex items-center gap-3">
            <span class="material-symbols-outlined">visibility</span>
            <p class="text-sm font-medium">Observer access - training only</p>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Context -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
                <h2 class="text-xl font-semibold text-primary mb-4 border-b border-border-base pb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary">person</span> Candidate
                </h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-text-muted">Name</span>
                        <span class="font-medium text-primary"><?= e($briefing['candidate_name']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-muted">Title</span>
                        <span class="font-medium text-primary"><?= e($briefing['current_title'] ?? 'N/A') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-muted">Experience</span>
                        <span class="font-medium text-primary"><?= e($briefing['years_experience']) ?> years</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-muted">Location</span>
                        <span class="font-medium text-primary"><?= e($briefing['location'] ?? 'N/A') ?></span>
                    </div>
                    <div class="pt-3 mt-3 border-t border-border-base">
                        <?php if (empty($briefing['resume_url'])): ?>
                            <p class="text-text-muted italic text-center">Resume is missing</p>
                        <?php else: ?>
                            <a href="<?= e($briefing['resume_url']) ?>" target="_blank" class="flex items-center justify-center gap-2 w-full py-2 bg-surface-container-low hover:bg-surface-container-highest text-primary rounded-md transition-colors font-medium border border-border-base">
                                <span class="material-symbols-outlined text-[18px]">description</span> View Resume
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
                <h2 class="text-xl font-semibold text-primary mb-4 border-b border-border-base pb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary">work</span> Job Context
                </h2>
                <div class="space-y-4 text-sm">
                    <div>
                        <span class="text-text-muted block mb-1">Title</span>
                        <span class="font-medium text-primary block"><?= e($briefing['job_title']) ?></span>
                    </div>
                    <div>
                        <span class="text-text-muted block mb-1 flex items-center gap-1">Application Status & Match</span>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"><?= e($briefing['application_status']) ?></span>
                            <span class="font-medium text-primary"><?= e($briefing['match_score']) ?>% Match</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-text-muted block mb-1">Requirements</span>
                        <div class="bg-surface-container-low p-3 rounded text-primary text-xs max-h-48 overflow-y-auto">
                            <?= nl2br(e($briefing['requirements'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Assessment & Feedback -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
                <h2 class="text-xl font-semibold text-primary mb-4 border-b border-border-base pb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary">quiz</span> Assessment Summary
                </h2>
                
                <?php if (empty($briefing['assessment_attempt'])): ?>
                    <div class="p-6 text-center text-text-muted bg-surface-container-lowest rounded-lg border border-dashed border-border-base">
                        <span class="material-symbols-outlined text-[32px] mb-2 opacity-50">pending_actions</span>
                        <p>No completed assessment found for this candidate.</p>
                    </div>
                <?php else: ?>
                    <div class="flex flex-wrap gap-6 mb-6 bg-surface-container-low p-4 rounded-lg">
                        <div>
                            <span class="text-text-muted text-xs uppercase tracking-wider block mb-1">Assessment</span>
                            <span class="font-medium text-primary"><?= e($briefing['assessment_attempt']['assessment_title']) ?></span>
                        </div>
                        <div>
                            <span class="text-text-muted text-xs uppercase tracking-wider block mb-1">Status</span>
                            <span class="font-medium text-primary"><?= e($briefing['assessment_attempt']['status']) ?></span>
                        </div>
                        <div>
                            <span class="text-text-muted text-xs uppercase tracking-wider block mb-1">Score</span>
                            <span class="font-bold text-lg text-primary"><?= e($briefing['assessment_attempt']['score'] ?? 'Pending') ?></span>
                        </div>
                    </div>
                    
                    <h3 class="font-medium text-primary mb-3">Submitted Answers</h3>
                    <?php if (empty($briefing['submissions_summary'])): ?>
                        <p class="text-text-muted italic text-sm">No submitted answers available.</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($briefing['submissions_summary'] as $index => $sub): ?>
                                <div class="border border-border-base rounded-lg overflow-hidden">
                                    <div class="bg-surface-container-low px-4 py-3 border-b border-border-base">
                                        <p class="text-sm font-medium text-primary"><span class="text-text-muted mr-2">Q<?= $index + 1 ?>.</span> <?= e($sub['question_text']) ?></p>
                                    </div>
                                    <div class="p-4 bg-white">
                                        <pre class="text-sm text-text-muted whitespace-pre-wrap font-mono bg-gray-50 p-3 rounded border border-gray-100"><?= e($sub['answer_text'] ?? 'No answer provided') ?></pre>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php 
            $alreadySubmitted = \App\Repositories\InterviewFeedbackRepository::alreadySubmitted($briefing['interview_id'], $actor['user_id']);
            if ((new \App\Policies\InterviewFeedbackPolicy())->create($actor, $briefing, $briefing['assignment'], $alreadySubmitted)): 
            ?>
                <div class="bg-card-surface rounded-xl shadow-ambient border border-secondary/30 p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-secondary/5 rounded-bl-full -mr-8 -mt-8 pointer-events-none"></div>
                    <div class="relative z-10 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-primary mb-1">Official Feedback Required</h2>
                            <p class="text-text-muted text-sm">Please submit your evaluation based on the interview performance.</p>
                        </div>
                        <a href="<?= e(url('interviewer.interviews.feedback.create', [$briefing['interview_id']])) ?>" class="bg-secondary-container hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors font-medium shadow-md flex items-center gap-2 whitespace-nowrap">
                            <span class="material-symbols-outlined text-[20px]">rate_review</span> Submit Feedback
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
