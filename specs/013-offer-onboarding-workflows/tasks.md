# Tasks: Offer Onboarding Workflows

**Input**: Design documents from `specs/013-offer-onboarding-workflows/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, route-map.md, contracts/offer-onboarding-web.md, quickstart.md

**Tests**: Automated tests were not explicitly requested. Each story includes an independent manual validation task mapped to the acceptance criteria and `quickstart.md`.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.
**Peer Review**: Include a peer-review task before implementation starts for each user story.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare shared files and extension points for offer/onboarding delivery.

- [X] T001 Review implementation scope and baseline constraints in `specs/013-offer-onboarding-workflows/plan.md`, `specs/013-offer-onboarding-workflows/spec.md`, and `.specify/memory/constitution.md`
- [X] T002 Create post-offer migration skeleton in `database/migrations/013_offer_onboarding_workflows.sql`
- [X] T003 [P] Create offer/onboarding enum placeholders in `app/Enums/BackgroundCheckOutcome.php`, `app/Enums/BackgroundCheckStatus.php`, `app/Enums/OfferLetterStatus.php`, `app/Enums/OfferNegotiationStatus.php`, `app/Enums/OfferRunCheckType.php`, `app/Enums/OnboardingDocumentStatus.php`, and `app/Enums/ReferralRewardStatus.php`
- [X] T004 [P] Create service placeholders in `app/Services/OfferPackageCalculator.php`, `app/Services/OfferLetterTemplateService.php`, and `app/Services/SimulatedBackgroundCheckService.php`
- [X] T005 [P] Create candidate onboarding and HR run-check view placeholders in `views/candidate/onboarding/welcome.php`, `views/candidate/onboarding/documents.php`, `views/hr/run-checks/offers.php`, and `views/hr/onboarding/documents.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared database, policy, audit, route, and repository support that MUST be complete before any user story can be implemented.

**CRITICAL**: No user story work can begin until this phase is complete.

- [ ] T006 Define shared post-offer tables and offer/onboarding column extensions in `database/migrations/013_offer_onboarding_workflows.sql`
- [ ] T007 Update baseline schema reference for post-offer tables in `database/schema.sql`
- [ ] T008 [P] Expand post-offer audit actions for offers, letters, negotiations, run checks, background checks, referrals, and onboarding documents in `app/Enums/PostOfferAuditAction.php`
- [ ] T009 [P] Implement shared append-only audit helpers with actor role, entity type, old values, new values, and reason support in `app/Repositories/PostOfferAuditRepository.php`
- [ ] T010 [P] Add shared notification helper methods for post-offer HR/candidate events in `app/Repositories/NotificationRepository.php`
- [ ] T011 Update offer and onboarding authorization rules for HR, candidate ownership, interviewer denial, and junior-staff denial in `app/Policies/OfferPolicy.php` and `app/Policies/OnboardingPolicy.php`
- [ ] T012 Register all shared offer, run-check, negotiation, letter, and onboarding document web routes in `routes/web.php`
- [ ] T013 Remove read-time offer expiry side effects from existing offer page flows in `app/Controllers/HrOfferController.php`, `app/Controllers/CandidateOfferController.php`, and `app/Repositories/OfferRepository.php`

**Checkpoint**: Foundation ready - user story implementation can now begin in priority order or in parallel where staffing allows.

---

## Phase 3: User Story 1 - Calculate and Prepare Offer Package (Priority: P1) MVP

**Goal**: HR can calculate and save an offer package for an eligible Hire or Strong Hire candidate using role level, base salary, bonus, and stock rules.

**Independent Test**: Select a Hire or Strong Hire application, enter compensation inputs, save a draft package, verify calculated totals/rule basis/audit history, and confirm invalid or duplicate active offers are rejected.

### Implementation for User Story 1

- [ ] T014 [US1] Peer review offer package scope, RBAC, privacy, validation, and acceptance criteria in `specs/013-offer-onboarding-workflows/spec.md`, `specs/013-offer-onboarding-workflows/plan.md`, and `specs/013-offer-onboarding-workflows/route-map.md`
- [ ] T015 [P] [US1] Add compensation rule and offer calculation columns to `database/migrations/013_offer_onboarding_workflows.sql`
- [ ] T016 [P] [US1] Implement offer package status and calculation enum values in `app/Enums/OfferStatus.php` and `app/Enums/OfferType.php`
- [ ] T017 [P] [US1] Implement compensation rule lookup and active-offer eligibility queries in `app/Repositories/OfferRepository.php`
- [ ] T018 [P] [US1] Implement deterministic package calculation and manual override validation in `app/Services/OfferPackageCalculator.php`
- [ ] T019 [US1] Update offer create/store flows with role level, base salary, bonus, stock, start date, expiry window, duplicate active offer prevention, and audit writes in `app/Controllers/HrOfferController.php`
- [ ] T020 [US1] Update offer package form with calculation inputs, validation errors, and CSRF-safe submission in `views/hr/offers/form.php`
- [ ] T021 [US1] Update HR offer detail page to show calculation snapshot, rule basis, draft status, and audit history in `views/hr/offers/show.php`
- [ ] T022 [US1] Add in-system HR notification for saved or blocked offer package actions in `app/Repositories/NotificationRepository.php`
- [ ] T023 [US1] Run manual US1 acceptance flow and record results in `specs/013-offer-onboarding-workflows/quickstart.md`

**Checkpoint**: User Story 1 should be fully functional and testable independently.

---

## Phase 4: User Story 2 - Generate and Send Versioned Offer Letter (Priority: P1)

**Goal**: HR can generate a digital offer letter from an approved template version, send it, and candidates can view/respond only to their own current offer.

**Independent Test**: Generate a letter from an approved active template, verify template version/content snapshot, send it, and confirm the candidate sees only their own offer with response options.

### Implementation for User Story 2

- [ ] T024 [US2] Peer review offer letter scope, template versioning, candidate access, and acceptance criteria in `specs/013-offer-onboarding-workflows/contracts/offer-onboarding-web.md` and `specs/013-offer-onboarding-workflows/data-model.md`
- [ ] T025 [P] [US2] Add offer template version and digital offer letter tables to `database/migrations/013_offer_onboarding_workflows.sql`
- [ ] T026 [P] [US2] Implement offer letter status enum values in `app/Enums/OfferLetterStatus.php`
- [ ] T027 [P] [US2] Implement approved template lookup, placeholder validation, and generated content snapshot rendering in `app/Services/OfferLetterTemplateService.php`
- [ ] T028 [US2] Add offer letter persistence, current letter lookup, and send-state updates in `app/Repositories/OfferRepository.php`
- [ ] T029 [US2] Implement HR generate-letter, preview-letter, and send-offer actions in `app/Controllers/HrOfferController.php`
- [ ] T030 [US2] Update candidate offer view, accept flow, reject flow, signature/consent handling, and ownership checks in `app/Controllers/CandidateOfferController.php`
- [ ] T031 [US2] Add HR letter preview UI with template version and generated body in `views/hr/offers/letter.php`
- [ ] T032 [US2] Update candidate offer page with generated letter, expiry, accept, decline, and negotiation entry points in `views/candidate/offers/show.php`
- [ ] T033 [US2] Add send and candidate-response notifications in `app/Repositories/NotificationRepository.php`
- [ ] T034 [US2] Run manual US2 acceptance flow and record results in `specs/013-offer-onboarding-workflows/quickstart.md`

**Checkpoint**: User Stories 1 and 2 should both work independently.

---

## Phase 5: User Story 3 - Track Negotiations and Offer Expiry (Priority: P1)

**Goal**: HR can manage counter-offers and revisions, and offer expiry occurs only through manual HR Run Checks.

**Independent Test**: Candidate requests negotiation, HR records a decision and revised offer, prior revisions remain preserved, and HR Run Checks marks overdue unsigned offers expired without page-load side effects.

### Implementation for User Story 3

- [ ] T035 [US3] Peer review negotiation and manual expiry scope in `specs/013-offer-onboarding-workflows/research.md`, `specs/013-offer-onboarding-workflows/route-map.md`, and `specs/013-offer-onboarding-workflows/contracts/offer-onboarding-web.md`
- [ ] T036 [P] [US3] Add negotiation revision, HR run check, and HR run check result tables to `database/migrations/013_offer_onboarding_workflows.sql`
- [ ] T037 [P] [US3] Implement negotiation and run-check enum values in `app/Enums/OfferNegotiationStatus.php` and `app/Enums/OfferRunCheckType.php`
- [ ] T038 [P] [US3] Implement negotiation revision, superseded offer, actionable offer, and manual expiry queries in `app/Repositories/OfferRepository.php`
- [ ] T039 [US3] Implement candidate negotiation submission and superseded-offer response blocking in `app/Controllers/CandidateOfferController.php`
- [ ] T040 [US3] Implement HR negotiation list and decision actions in `app/Controllers/HrOfferController.php`
- [ ] T041 [US3] Create HR Run Checks controller actions for listing overdue offers and manually expiring selected offers in `app/Controllers/HrRunChecksController.php`
- [ ] T042 [US3] Build HR negotiation review UI in `views/hr/offers/negotiation.php`
- [ ] T043 [US3] Build manual offer expiry run-check UI in `views/hr/run-checks/offers.php`
- [ ] T044 [US3] Add negotiation, revision, superseded, and expiry notifications in `app/Repositories/NotificationRepository.php`
- [ ] T045 [US3] Run manual US3 acceptance flow and record results in `specs/013-offer-onboarding-workflows/quickstart.md`

**Checkpoint**: P1 MVP set should now support package calculation, letter send/response, negotiations, and manual expiry.

---

## Phase 6: User Story 4 - Run Simulated Background Checks (Priority: P2)

**Goal**: HR can manually trigger and record simulated background-check outcomes after conditional offer acceptance, and onboarding gates respect the result.

**Independent Test**: Accept an offer, run simulated background check through HR Run Checks, choose cleared or review/failed outcome, and verify onboarding eligibility or block status.

### Implementation for User Story 4

- [ ] T046 [US4] Peer review simulated background-check scope and candidate-safe status requirements in `specs/013-offer-onboarding-workflows/spec.md` and `specs/013-offer-onboarding-workflows/research.md`
- [ ] T047 [P] [US4] Add simulated background check table and onboarding gate columns to `database/migrations/013_offer_onboarding_workflows.sql`
- [ ] T048 [P] [US4] Implement simulated background-check enum values in `app/Enums/BackgroundCheckOutcome.php` and `app/Enums/BackgroundCheckStatus.php`
- [ ] T049 [P] [US4] Implement deterministic simulated outcome handling and candidate-safe messages in `app/Services/SimulatedBackgroundCheckService.php`
- [ ] T050 [US4] Add background-check request, outcome, and gate queries in `app/Repositories/OnboardingRepository.php`
- [ ] T051 [US4] Extend HR Run Checks background-check actions with simulated labels, rationale, audit writes, and no duplicate state changes in `app/Controllers/HrRunChecksController.php`
- [ ] T052 [US4] Update HR run-check UI to show accepted offers awaiting simulated checks and outcome controls in `views/hr/run-checks/offers.php`
- [ ] T053 [US4] Enforce accepted-offer and cleared-background-check onboarding gate in `app/Controllers/HrOnboardingController.php`
- [ ] T054 [US4] Run manual US4 acceptance flow and record results in `specs/013-offer-onboarding-workflows/quickstart.md`

**Checkpoint**: User Story 4 should be independently testable after any accepted offer exists.

---

## Phase 7: User Story 5 - Attribute Referral Rewards (Priority: P2)

**Goal**: HR can see and manage referral reward eligibility for referred candidates after offer acceptance and onboarding clearance.

**Independent Test**: For a referred accepted and cleared candidate, verify referral reward eligibility appears with referrer, milestone, state, and audit history; no-referrer and invalid-referrer cases are handled.

### Implementation for User Story 5

- [ ] T055 [US5] Peer review referral attribution scope and payroll exclusion in `specs/013-offer-onboarding-workflows/research.md` and `specs/013-offer-onboarding-workflows/data-model.md`
- [ ] T056 [P] [US5] Add referral reward attribution table to `database/migrations/013_offer_onboarding_workflows.sql`
- [ ] T057 [P] [US5] Implement referral reward status enum values in `app/Enums/ReferralRewardStatus.php`
- [ ] T058 [US5] Add referral attribution creation, no-referrer, invalid-referrer, hold, reject, and correction queries in `app/Repositories/OfferRepository.php`
- [ ] T059 [US5] Implement HR referral review and correction actions in `app/Controllers/HrOfferController.php`
- [ ] T060 [US5] Display referral attribution state and correction form in `views/hr/offers/show.php`
- [ ] T061 [US5] Add referral attribution audit events and HR notifications in `app/Repositories/PostOfferAuditRepository.php` and `app/Repositories/NotificationRepository.php`
- [ ] T062 [US5] Run manual US5 acceptance flow and record results in `specs/013-offer-onboarding-workflows/quickstart.md`

**Checkpoint**: User Story 5 should be independently testable after an accepted and cleared referred offer exists.

---

## Phase 8: User Story 6 - Complete Pre-Onboarding Welcome Portal (Priority: P2)

**Goal**: Cleared candidates can use a welcome portal to view day-one information and submit required onboarding documents while HR reviews completion.

**Independent Test**: Open the candidate welcome portal for a cleared accepted offer, submit required document items, have HR accept or request correction, and verify completion state.

### Implementation for User Story 6

- [ ] T063 [US6] Peer review welcome portal, document privacy, and HR review scope in `specs/013-offer-onboarding-workflows/contracts/offer-onboarding-web.md` and `specs/013-offer-onboarding-workflows/data-model.md`
- [ ] T064 [P] [US6] Add onboarding document item table and onboarding completion columns to `database/migrations/013_offer_onboarding_workflows.sql`
- [ ] T065 [P] [US6] Implement onboarding document status enum values in `app/Enums/OnboardingDocumentStatus.php`
- [ ] T066 [P] [US6] Implement onboarding document checklist, submission, review, completion, and correction queries in `app/Repositories/OnboardingRepository.php`
- [ ] T067 [US6] Implement candidate welcome portal and document submission actions in `app/Controllers/CandidateOnboardingController.php`
- [ ] T068 [US6] Implement HR onboarding document review actions and completion gating in `app/Controllers/HrOnboardingController.php`
- [ ] T069 [US6] Build candidate welcome portal UI in `views/candidate/onboarding/welcome.php`
- [ ] T070 [US6] Build candidate document checklist UI in `views/candidate/onboarding/documents.php`
- [ ] T071 [US6] Build HR onboarding document review UI in `views/hr/onboarding/documents.php`
- [ ] T072 [US6] Update HR onboarding list/detail pages with document completion and blocked/readiness states in `views/hr/onboarding/index.php` and `views/hr/onboarding/show.php`
- [ ] T073 [US6] Add candidate document correction and onboarding readiness notifications in `app/Repositories/NotificationRepository.php`
- [ ] T074 [US6] Run manual US6 acceptance flow and record results in `specs/013-offer-onboarding-workflows/quickstart.md`

**Checkpoint**: User Story 6 should complete the candidate pre-onboarding workflow independently after offer acceptance and clearance.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Purpose**: Final validation, cleanup, documentation, and cross-story quality checks.

- [ ] T075 [P] Run PHP syntax checks for changed controllers in `app/Controllers/HrOfferController.php`, `app/Controllers/CandidateOfferController.php`, `app/Controllers/HrOnboardingController.php`, `app/Controllers/CandidateOnboardingController.php`, and `app/Controllers/HrRunChecksController.php`
- [ ] T076 [P] Run PHP syntax checks for changed repositories, services, policies, and enums in `app/Repositories/`, `app/Services/`, `app/Policies/`, and `app/Enums/`
- [ ] T077 Verify manual route names and form methods for all offer/onboarding routes in `routes/web.php`
- [ ] T078 Verify migration applies cleanly and preserves baseline offer/onboarding relationships in `database/migrations/013_offer_onboarding_workflows.sql` and `database/schema.sql`
- [ ] T079 Verify RBAC denial paths for interviewer, junior staff, unrelated candidate, and unauthenticated access in `app/Policies/OfferPolicy.php` and `app/Policies/OnboardingPolicy.php`
- [ ] T080 Verify audit coverage for each state-changing action in `app/Repositories/PostOfferAuditRepository.php`
- [ ] T081 Run full manual quickstart validation and record final evidence in `specs/013-offer-onboarding-workflows/quickstart.md`
- [ ] T082 Update known limitations and implementation notes in `specs/013-offer-onboarding-workflows/plan.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies; can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion; blocks all user stories.
- **User Stories (Phase 3+)**: All depend on Foundational completion.
- **Polish (Phase 9)**: Depends on all implemented user stories for the target release.

### User Story Dependencies

- **US1 Calculate and Prepare Offer Package (P1)**: First MVP story after foundation; no dependency on other stories.
- **US2 Generate and Send Versioned Offer Letter (P1)**: Depends on US1 for a complete saved offer package.
- **US3 Track Negotiations and Offer Expiry (P1)**: Depends on US2 for sent offers and candidate response paths.
- **US4 Run Simulated Background Checks (P2)**: Depends on US2 for accepted offers; can proceed before US3 if a sent/accepted offer path exists.
- **US5 Attribute Referral Rewards (P2)**: Depends on US4 clearance milestone for eligible reward attribution.
- **US6 Complete Pre-Onboarding Welcome Portal (P2)**: Depends on US4 clearance milestone for welcome portal readiness.

### Within Each User Story

- Peer review task must be completed before implementation tasks in that story.
- Database and enum tasks come before repository and service tasks.
- Repository and service tasks come before controller tasks.
- Controller tasks come before view tasks when views require controller-provided data.
- Notification, audit, and manual validation tasks complete each story.

## Parallel Opportunities

- T003, T004, and T005 can run in parallel after T001 and T002.
- T008, T009, and T010 can run in parallel after T006 and T007.
- In US1, T015, T016, T017, and T018 can run in parallel after T014.
- In US2, T025, T026, and T027 can run in parallel after T024.
- In US3, T036, T037, and T038 can run in parallel after T035.
- In US4, T047, T048, and T049 can run in parallel after T046.
- In US6, T064, T065, and T066 can run in parallel after T063.
- US5 and US6 can run in parallel after US4 produces cleared onboarding eligibility.

## Parallel Example: User Story 1

```text
Task: "T015 [US1] Add compensation rule and offer calculation columns to database/migrations/013_offer_onboarding_workflows.sql"
Task: "T016 [US1] Implement offer package status and calculation enum values in app/Enums/OfferStatus.php and app/Enums/OfferType.php"
Task: "T017 [US1] Implement compensation rule lookup and active-offer eligibility queries in app/Repositories/OfferRepository.php"
Task: "T018 [US1] Implement deterministic package calculation and manual override validation in app/Services/OfferPackageCalculator.php"
```

## Parallel Example: User Story 2

```text
Task: "T025 [US2] Add offer template version and digital offer letter tables to database/migrations/013_offer_onboarding_workflows.sql"
Task: "T026 [US2] Implement offer letter status enum values in app/Enums/OfferLetterStatus.php"
Task: "T027 [US2] Implement approved template lookup, placeholder validation, and generated content snapshot rendering in app/Services/OfferLetterTemplateService.php"
```

## Parallel Example: User Story 6

```text
Task: "T064 [US6] Add onboarding document item table and onboarding completion columns to database/migrations/013_offer_onboarding_workflows.sql"
Task: "T065 [US6] Implement onboarding document status enum values in app/Enums/OnboardingDocumentStatus.php"
Task: "T066 [US6] Implement onboarding document checklist, submission, review, completion, and correction queries in app/Repositories/OnboardingRepository.php"
```

---

## Implementation Strategy

### MVP First (P1 Stories)

1. Complete Phase 1: Setup.
2. Complete Phase 2: Foundational.
3. Complete Phase 3: US1 Calculate and Prepare Offer Package.
4. Complete Phase 4: US2 Generate and Send Versioned Offer Letter.
5. Complete Phase 5: US3 Track Negotiations and Offer Expiry.
6. Stop and validate P1 offer package, letter, candidate response, negotiation, and manual expiry flows.

### Incremental Delivery

1. Deliver US1 to prove HR can create governed offer packages.
2. Add US2 to make offer letters candidate-facing and versioned.
3. Add US3 to govern negotiation and manual expiry.
4. Add US4 to enforce simulated clearance gates.
5. Add US5 and US6 in parallel if staffing allows.
6. Finish with cross-story RBAC, audit, syntax, route, migration, and quickstart validation.

### Team Parallel Strategy

1. Team completes Setup and Foundational tasks together.
2. Developer A implements US1 and US2 offer package/letter flow.
3. Developer B implements US3 and US4 run-check/negotiation/background-check flow.
4. Developer C implements US5 and US6 referral/onboarding portal flow after US4 clearance gates are available.

## Notes

- `[P]` tasks touch different files or can be done without waiting for incomplete tasks in the same phase.
- `[US1]` through `[US6]` labels map directly to the user stories in `specs/013-offer-onboarding-workflows/spec.md`.
- No REST API, separated frontend, scheduler, background worker, external payroll, external background-check provider, or external email integration is in scope.
- Offer expiry and simulated background checks must remain manual HR Run Checks only.
