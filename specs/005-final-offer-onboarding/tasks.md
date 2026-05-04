# Tasks: Final Offer Onboarding

**Input**: Design documents from `specs/005-final-offer-onboarding/`  
**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/web-workflows.md`, `route-map.md`, `quickstart.md`

**Tests**: Automated tests are not required by the user request. Include PHP syntax/check coverage in `scripts/check.php` where practical and produce documented manual demo evidence using `quickstart.md`.

**Organization**: Tasks are grouped by user story so each story can be implemented and tested independently after the shared foundation is complete.

## Phase 1: Setup (Shared Preparation)

**Purpose**: Confirm context and create directories/files needed by all implementation phases.

- [x] T001 Review `specs/005-final-offer-onboarding/spec.md`, `specs/005-final-offer-onboarding/plan.md`, `specs/005-final-offer-onboarding/data-model.md`, and `specs/005-final-offer-onboarding/contracts/web-workflows.md` before changing source files
- [x] T002 [P] Create HR view directories `views/hr/evaluations/`, `views/hr/offers/`, and `views/hr/onboarding/`
- [x] T003 [P] Create candidate offer view directory `views/candidate/offers/`
- [x] T004 [P] Create manual evidence notes file `specs/005-final-offer-onboarding/manual-evidence.md` with headings for final evaluation, offer, candidate response, onboarding, replacement offer, authorization, and audit evidence

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared schema, enums, repositories, and authorization pieces that every user story depends on.

**Critical**: Complete this phase before starting any user story phase.

- [x] T005 Update `database/schema.sql` drop order to drop `post_offer_audit_records`, `onboarding`, `offers`, and `final_evaluations` before application/interview tables
- [x] T006 Add `final_evaluations` table to `database/schema.sql` with `evaluation_id`, unique `application_id`, `aggregate_score`, `recommendation`, `status`, `decision_notes`, `partial_evidence_acknowledged`, `evaluated_by`, timestamps, foreign keys, and score range constraint
- [x] T007 Add `offers` table to `database/schema.sql` with `offer_id`, `application_id`, `offer_sequence`, nullable `replaces_offer_id`, `offer_type`, compensation fields, `status`, `expiry_date`, response timestamps, `created_by`, timestamps, foreign keys, non-negative amount constraints, and unique application sequence constraint
- [x] T008 Add `onboarding` table to `database/schema.sql` with `onboarding_id`, unique `offer_id`, `status`, `start_date`, `documents_completed`, `created_by`, timestamps, and foreign keys
- [x] T009 Add `post_offer_audit_records` table to `database/schema.sql` with `audit_id`, `application_id`, nullable `offer_id`, nullable `onboarding_id`, `actor_user_id`, `action`, `changed_fields`, `created_at`, foreign keys, and indexes
- [x] T010 [P] Create final evaluation enum files `app/Enums/FinalEvaluationRecommendation.php` and `app/Enums/FinalEvaluationStatus.php` with `values()` helpers matching `data-model.md`
- [x] T011 [P] Create offer enum files `app/Enums/OfferStatus.php` and `app/Enums/OfferType.php` with `values()` helpers matching `data-model.md`
- [x] T012 [P] Create onboarding and audit enum files `app/Enums/OnboardingStatus.php` and `app/Enums/PostOfferAuditAction.php` with `values()` helpers matching `data-model.md`
- [x] T013 Create `app/Repositories/PostOfferAuditRepository.php` with a `record(int $applicationId, ?int $offerId, ?int $onboardingId, int $actorUserId, string $action, array $changedFields): void` method that inserts one audit row
- [x] T014 Create `app/Repositories/FinalEvaluationRepository.php` with methods to fetch application evidence, calculate aggregate score, check existing final evaluation, save final evaluation, and write application status history when needed
- [x] T015 Create `app/Repositories/OfferRepository.php` with methods to find offers, check active draft/sent offers, check replacement eligibility, create draft offers, send offers, enforce expiry, accept offers, reject offers, and write application status history
- [x] T016 Create `app/Repositories/OnboardingRepository.php` with methods to list onboarding records, find accepted offers, check duplicate onboarding, create onboarding, update onboarding, and fetch onboarding detail
- [x] T017 [P] Create `app/Policies/FinalEvaluationPolicy.php` with HR-only `view()` and `create()` decisions using existing `App\Core\Auth` user role conventions
- [x] T018 [P] Create `app/Policies/OfferPolicy.php` with HR offer management decisions and candidate ownership decisions for sent offer view, accept, and reject
- [x] T019 [P] Create `app/Policies/OnboardingPolicy.php` with HR-only view, create, and update decisions for accepted-offer onboarding
- [x] T020 Update `scripts/check.php` to include syntax checks for every new controller, enum, policy, and repository file listed in `specs/005-final-offer-onboarding/plan.md`

**Checkpoint**: Schema, enum, repository, policy, audit, and check foundations exist; user story implementation can begin.

---

## Phase 3: User Story 1 - Record Final Evaluation (Priority: P1) MVP

**Goal**: HR Admin reviews assessment/interview evidence and records one final recommendation with aggregate score and partial-evidence handling.

**Independent Test**: Use an application with assessment or interview evidence, open final evaluation, save a recommendation with decision notes, and verify the saved decision plus application status for no-hire recommendations.

### Manual Demo Check for User Story 1

- [x] T021 [US1] Add User Story 1 manual demo checklist to `specs/005-final-offer-onboarding/manual-evidence.md` covering complete evidence, partial evidence acknowledgement, duplicate final evaluation blocking, and no-hire application rejection
- [x] T022 [US1] Peer review `specs/005-final-offer-onboarding/spec.md`, `specs/005-final-offer-onboarding/plan.md`, `specs/005-final-offer-onboarding/data-model.md`, and `specs/005-final-offer-onboarding/contracts/web-workflows.md` for US1 scope before editing source files

### Implementation for User Story 1

- [x] T023 [US1] Implement `HrFinalEvaluationController::show` in `app/Controllers/HrFinalEvaluationController.php` to load application, candidate, job, assessment evidence, interview feedback, missing evidence flags, aggregate score preview, and existing final evaluation
- [x] T024 [US1] Implement `HrFinalEvaluationController::store` in `app/Controllers/HrFinalEvaluationController.php` to validate recommendation, decision notes, evidence availability, partial evidence acknowledgement, and duplicate final evaluation prevention
- [x] T025 [US1] Update `app/Repositories/FinalEvaluationRepository.php` to normalize assessment scores and interview overall scores to 0-100, average assessment/interview evidence equally, and return partial evidence flags
- [x] T026 [US1] Update `app/Repositories/FinalEvaluationRepository.php` to set application status to `REJECTED` and insert `application_status_histories` when recommendation is `NO_HIRE` or `STRONG_NO_HIRE`
- [x] T027 [US1] Update `app/Repositories/PostOfferAuditRepository.php` usage from `HrFinalEvaluationController::store` to write `FINAL_EVALUATION_SAVE` audit records with recommendation, aggregate score, partial evidence flag, and application status changes
- [x] T028 [US1] Create `views/hr/evaluations/show.php` with evidence sections, missing evidence warning, aggregate score preview, final recommendation select, decision notes textarea, partial evidence acknowledgement checkbox, CSRF field, and saved evaluation read-only state
- [x] T029 [US1] Add final evaluation routes `GET /hr/applications/{id}/final-evaluation` and `POST /hr/applications/{id}/final-evaluation` to `routes/web.php`
- [x] T030 [US1] Add HR navigation link from each application row to final evaluation in `views/hr/applications/index.php`
- [x] T031 [US1] Run `composer test` and record User Story 1 results in `specs/005-final-offer-onboarding/manual-evidence.md`

**Checkpoint**: User Story 1 is independently functional and demoable as the MVP.

---

## Phase 4: User Story 2 - Create Offer Package (Priority: P2)

**Goal**: HR Admin creates a draft offer for hire recommendations, sends it with a future expiry, and is blocked from invalid or duplicate active offers.

**Independent Test**: Save a draft offer for a `Hire` or `Strong Hire` final evaluation, send it, and verify invalid compensation, invalid expiry, ineligible recommendation, and duplicate active offer cases are blocked.

### Manual Demo Check for User Story 2

- [x] T032 [US2] Add User Story 2 manual demo checklist to `specs/005-final-offer-onboarding/manual-evidence.md` covering draft creation, send, invalid compensation, past expiry, ineligible recommendation, and duplicate active offer blocking
- [x] T033 [US2] Peer review `specs/005-final-offer-onboarding/contracts/web-workflows.md` HR offer routes and `specs/005-final-offer-onboarding/data-model.md` Offer Package rules before editing source files

### Implementation for User Story 2

- [x] T034 [US2] Implement `HrOfferController::index` in `app/Controllers/HrOfferController.php` to list offers with candidate, job, status, expiry, response timestamps, and onboarding eligibility
- [x] T035 [US2] Implement `HrOfferController::create` in `app/Controllers/HrOfferController.php` to load offer-eligible application details, final evaluation, existing offer state, and replacement context
- [x] T036 [US2] Implement `HrOfferController::store` in `app/Controllers/HrOfferController.php` to validate offer type, non-negative compensation, expiry date, hire recommendation, no active offer, and one replacement limit
- [x] T037 [US2] Implement `HrOfferController::show` in `app/Controllers/HrOfferController.php` to enforce expiry before display and show package, status, response, replacement eligibility, and onboarding eligibility
- [x] T038 [US2] Implement `HrOfferController::send` in `app/Controllers/HrOfferController.php` to validate draft completeness, future expiry, no other active offer, set status `SENT`, set `sent_at`, and set application status `OFFER`
- [x] T039 [US2] Update `app/Repositories/OfferRepository.php` to create original offers with `offer_sequence = 1` and replacement offers with `offer_sequence = 2` and `replaces_offer_id` pointing to the rejected or expired offer
- [x] T040 [US2] Update `app/Repositories/PostOfferAuditRepository.php` usage from `HrOfferController.php` to write `OFFER_CREATE`, `OFFER_REPLACE`, and `OFFER_SEND` audit records with changed compensation, status, expiry, and application status fields
- [x] T041 [US2] Create `views/hr/offers/index.php` with offer table columns for candidate, job, sequence, status, expiry, sent time, response time, and onboarding eligibility
- [x] T042 [US2] Create `views/hr/offers/form.php` with offer type select, CTC input, bonus input, stock options input, expiry date/time input, CSRF field, and validation error rendering
- [x] T043 [US2] Create `views/hr/offers/show.php` with offer detail, status, send button for complete drafts, replacement context, candidate response timestamps, and onboarding link placeholder
- [x] T044 [US2] Add HR offer routes from `specs/005-final-offer-onboarding/route-map.md` to `routes/web.php`
- [x] T045 [US2] Add offer creation link from `views/hr/evaluations/show.php` when final recommendation is `STRONG_HIRE` or `HIRE` and no active offer exists
- [x] T046 [US2] Run `composer test` and record User Story 2 results in `specs/005-final-offer-onboarding/manual-evidence.md`

**Checkpoint**: User Story 2 is independently functional after US1 creates an offer-eligible final evaluation.

---

## Phase 5: User Story 3 - Track Offer Status and Expiry (Priority: P3)

**Goal**: Candidate views only their own sent offer, accepts or rejects it before expiry, and HR can see accepted, rejected, expired, and awaiting-response states.

**Independent Test**: Log in as the owning candidate, accept or reject an unexpired sent offer, verify HR sees the response, verify another candidate is denied, and verify expired offers cannot be accepted.

### Manual Demo Check for User Story 3

- [x] T047 [US3] Add User Story 3 manual demo checklist to `specs/005-final-offer-onboarding/manual-evidence.md` covering candidate own-offer view, accept, reject, unauthorized other-candidate access denial, duplicate response blocking, and expired acceptance blocking
- [x] T048 [US3] Peer review `specs/005-final-offer-onboarding/contracts/web-workflows.md` candidate offer routes and `app/Policies/OfferPolicy.php` ownership rules before editing source files

### Implementation for User Story 3

- [x] T049 [US3] Implement `CandidateOfferController::show` in `app/Controllers/CandidateOfferController.php` to verify candidate ownership, enforce expiry before display, and show only sent/accepted/rejected/expired own offers
- [x] T050 [US3] Implement `CandidateOfferController::accept` in `app/Controllers/CandidateOfferController.php` to validate ownership, status `SENT`, unexpired offer, no previous response, set status `ACCEPTED`, set `accepted_at`, and set application status `HIRED`
- [x] T051 [US3] Implement `CandidateOfferController::reject` in `app/Controllers/CandidateOfferController.php` to validate ownership, status `SENT`, unexpired offer, no previous response, set status `REJECTED`, set `rejected_at`, and set application status `REJECTED`
- [x] T052 [US3] Update `app/Repositories/OfferRepository.php` to implement reusable `enforceExpiryForOffer(int $offerId, int $actorUserId): void` behavior that sets `EXPIRED`, `expired_at`, and application status `REJECTED` for overdue sent offers without responses
- [x] T053 [US3] Update `app/Repositories/PostOfferAuditRepository.php` usage from `CandidateOfferController.php` and `OfferRepository.php` to write `OFFER_ACCEPT`, `OFFER_REJECT`, and `OFFER_EXPIRE` audit records
- [x] T054 [US3] Create `views/candidate/offers/show.php` with own offer details, status, expiry, accept form, reject form, CSRF fields, and no internal HR notes or interviewer comments
- [x] T055 [US3] Add candidate offer routes from `specs/005-final-offer-onboarding/route-map.md` to `routes/web.php`
- [x] T056 [US3] Add candidate offer link to `views/candidate/applications/show.php` when the candidate has a sent, accepted, rejected, or expired offer
- [x] T057 [US3] Update `views/hr/offers/index.php` and `views/hr/offers/show.php` to display expired status after lazy expiry enforcement and show accepted/rejected timestamps
- [x] T058 [US3] Run `composer test` and record User Story 3 results in `specs/005-final-offer-onboarding/manual-evidence.md`

**Checkpoint**: User Story 3 is independently functional after US2 sends an offer.

---

## Phase 6: User Story 4 - Create Onboarding Record (Priority: P4)

**Goal**: HR Admin creates and updates one onboarding record only for accepted offers.

**Independent Test**: Accept an offer, create onboarding with start date/status/document state, update progress, verify rejected/expired/draft offers are blocked, and verify duplicate onboarding is blocked.

### Manual Demo Check for User Story 4

- [x] T059 [US4] Add User Story 4 manual demo checklist to `specs/005-final-offer-onboarding/manual-evidence.md` covering accepted-offer onboarding creation, rejected/expired/draft blocking, duplicate blocking, and onboarding status update
- [x] T060 [US4] Peer review `specs/005-final-offer-onboarding/contracts/web-workflows.md` onboarding routes and `specs/005-final-offer-onboarding/data-model.md` Onboarding Record rules before editing source files

### Implementation for User Story 4

- [x] T061 [US4] Implement `HrOnboardingController::index` in `app/Controllers/HrOnboardingController.php` to list accepted offers with candidate, job, onboarding status, start date, and document-completion state
- [x] T062 [US4] Implement `HrOnboardingController::create` in `app/Controllers/HrOnboardingController.php` to validate accepted offer eligibility and duplicate onboarding blocking before showing form
- [x] T063 [US4] Implement `HrOnboardingController::store` in `app/Controllers/HrOnboardingController.php` to validate accepted offer, no duplicate onboarding, allowed status, valid start date, and documents_completed boolean
- [x] T064 [US4] Implement `HrOnboardingController::show` in `app/Controllers/HrOnboardingController.php` to show onboarding detail with candidate and offer context
- [x] T065 [US4] Implement `HrOnboardingController::update` in `app/Controllers/HrOnboardingController.php` to validate allowed status, valid start date, documents_completed boolean, and update existing onboarding
- [x] T066 [US4] Update `app/Repositories/PostOfferAuditRepository.php` usage from `HrOnboardingController.php` to write `ONBOARDING_CREATE` and `ONBOARDING_UPDATE` audit records with start date, status, and document-completion changes
- [x] T067 [US4] Create `views/hr/onboarding/index.php` with onboarding table columns for candidate, job, offer status, onboarding status, start date, and documents completed
- [x] T068 [US4] Create `views/hr/onboarding/form.php` with start date input, status select, documents completed checkbox, CSRF field, and validation error rendering
- [x] T069 [US4] Create `views/hr/onboarding/show.php` with onboarding detail, update form, candidate summary, offer summary, and audit-relevant timestamps
- [x] T070 [US4] Add onboarding routes from `specs/005-final-offer-onboarding/route-map.md` to `routes/web.php`
- [x] T071 [US4] Add onboarding creation link from `views/hr/offers/show.php` only when offer status is `ACCEPTED` and no onboarding record exists
- [x] T072 [US4] Run `composer test` and record User Story 4 results in `specs/005-final-offer-onboarding/manual-evidence.md`

**Checkpoint**: User Story 4 is independently functional after US3 accepts an offer.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Final validation, cleanup, security review, and demo readiness across all user stories.

- [x] T073 [P] Update `views/hr/dashboard.php` with visible links to HR offers and HR onboarding pages for the manual demo
- [x] T074 [P] Update `views/candidate/dashboard.php` with a visible link to candidate offers for the manual demo
- [x] T075 Review all new controllers in `app/Controllers/` and ensure every mutating action validates CSRF, role/ownership policy, allowed enum values, and redirects with safe flash messages
- [x] T076 Review all new views in `views/hr/` and `views/candidate/offers/` to ensure compensation, final evaluation notes, interviewer comments, and onboarding data are hidden from unauthorized roles
- [x] T077 Review all new repository methods in `app/Repositories/` to ensure database writes use explicit allow-listed fields and preserve application status history for every specified transition
- [x] T078 Run `composer run db:schema` and record schema load results in `specs/005-final-offer-onboarding/manual-evidence.md`
- [x] T079 Run `composer run db:seed` and record seed/demo data results in `specs/005-final-offer-onboarding/manual-evidence.md`
- [x] T080 Run final `composer test` and record final check results in `specs/005-final-offer-onboarding/manual-evidence.md`
- [x] T081 Execute the full manual demo flow in `specs/005-final-offer-onboarding/quickstart.md` and record pass/fail notes in `specs/005-final-offer-onboarding/manual-evidence.md`
- [x] T082 Peer review implemented code against `specs/005-final-offer-onboarding/spec.md`, `specs/005-final-offer-onboarding/plan.md`, and `specs/005-final-offer-onboarding/tasks.md` before marking the feature complete

---

## Dependencies & Execution Order

### Phase Dependencies

- Phase 1 Setup has no dependencies.
- Phase 2 Foundational depends on Phase 1 and blocks every user story.
- Phase 3 US1 depends on Phase 2 and is the MVP.
- Phase 4 US2 depends on Phase 3 because offer creation requires a saved hire recommendation.
- Phase 5 US3 depends on Phase 4 because candidate responses require a sent offer.
- Phase 6 US4 depends on Phase 5 because onboarding requires an accepted offer.
- Phase 7 Polish depends on whichever user stories are implemented.

### User Story Dependencies

- US1 Record Final Evaluation: no dependency on other user stories after foundation.
- US2 Create Offer Package: requires US1 to create an offer-eligible final evaluation.
- US3 Track Offer Status and Expiry: requires US2 to create and send offers.
- US4 Create Onboarding Record: requires US3 to accept an offer.

### Within Each User Story

- Manual demo checklist and peer review tasks come first.
- Repository and controller changes come before view wiring when the view needs live data.
- Routes are added after controller methods exist.
- Navigation links are added after target routes exist.
- `composer test` and manual evidence are recorded at the end of each story.

---

## Parallel Opportunities

- T002, T003, and T004 can run in parallel during setup.
- T010, T011, T012, T017, T018, and T019 can run in parallel after schema tasks are understood because they create separate enum/policy files.
- Within US1, T025 and T028 can start after T023/T024 interfaces are agreed because repository aggregation and view layout touch different files.
- Within US2, T041, T042, and T043 can run in parallel after controller data keys are agreed because they create separate HR offer views.
- Within US3, T054 and T057 can run in parallel after candidate/HR offer display data keys are agreed because they touch separate view files.
- Within US4, T067, T068, and T069 can run in parallel after controller data keys are agreed because they create separate onboarding views.
- T073 and T074 can run in parallel during polish because they update separate dashboards.

## Parallel Example: User Story 2

```text
Task: "T041 [US2] Create views/hr/offers/index.php"
Task: "T042 [US2] Create views/hr/offers/form.php"
Task: "T043 [US2] Create views/hr/offers/show.php"
```

## Parallel Example: User Story 4

```text
Task: "T067 [US4] Create views/hr/onboarding/index.php"
Task: "T068 [US4] Create views/hr/onboarding/form.php"
Task: "T069 [US4] Create views/hr/onboarding/show.php"
```

---

## Implementation Strategy

### MVP First: User Story 1 Only

1. Complete Phase 1 setup.
2. Complete Phase 2 foundation.
3. Complete Phase 3 User Story 1.
4. Stop and validate HR can save a final evaluation with aggregate score and partial evidence handling.
5. Demo MVP before continuing to offers.

### Incremental Delivery

1. Add US1 final evaluation and validate independently.
2. Add US2 offer draft/send and validate independently using a US1 hire recommendation.
3. Add US3 candidate offer response and expiry validation using a US2 sent offer.
4. Add US4 onboarding using a US3 accepted offer.
5. Complete Phase 7 polish and full quickstart demo.

### Guidance For Lower-Cost Implementation Model

- Do not skip foundational schema, enum, repository, and policy tasks.
- Do not add REST APIs, JavaScript SPA flows, Laravel dependencies, or external email/e-signature/background-check integrations.
- Prefer existing project patterns in `app/Controllers/HrInterviewController.php`, `app/Repositories/InterviewRepository.php`, `app/Policies/InterviewPolicy.php`, `views/hr/interviews/`, and `routes/web.php`.
- Keep changes small and run `composer test` after each user story checkpoint.
