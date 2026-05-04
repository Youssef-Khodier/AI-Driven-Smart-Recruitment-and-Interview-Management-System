# Web Workflow Contracts: Job Requisition and Candidate Applications

These contracts define Blade page and form workflows. They are not REST API contracts.

## Shared Access Rules

- All protected pages require an authenticated active user session.
- HR workflows require `HR_ADMIN` role.
- Candidate workflows require `CANDIDATE` role.
- CSRF protection applies to all mutating form submissions.
- Unauthorized requests are denied without disclosing candidate profile, application, score, or requisition management data.

## HR Requisition Index

**Actor**: HR Admin  
**Purpose**: View requisitions grouped or filtered by lifecycle status.

**Page contract**:

- Shows Draft, Pending Approval, Approved, Open, and Closed filters.
- Shows title, department, status, creator, created date, and latest update date.
- Shows available actions based on policy and current status.

**Empty state**:

- Shows an HR-friendly message and a create action when no requisitions match the current filter.

## HR Create/Edit Requisition

**Actor**: HR Admin  
**Purpose**: Create or update Draft requisition details.

**Form input**:

- `department_id`: Required existing department.
- `title`: Required job title.
- `description`: Required job description.
- `requirements`: Required requirements text.
- `last_seen_updated_at`: Required on edit for stale-edit detection.

**Success result**:

- Saves Draft requisition.
- Redirects to requisition detail with a success message.

**Validation and conflict results**:

- Missing required fields return to the form with field errors.
- Stale edit conflict blocks save and asks HR Admin to reload before editing again.
- Non-HR or inactive users are denied.

## HR Submit Requisition

**Actor**: HR Admin  
**Purpose**: Move a complete Draft requisition to Pending Approval.

**Preconditions**:

- Requisition exists.
- Requisition is Draft.
- Required fields are complete.

**Success result**:

- Status changes to Pending Approval.
- Status history records actor, old status, new status, and timestamp.
- Redirects to requisition detail.

**Failure result**:

- Incomplete Draft stays Draft with validation guidance.

## HR Approve Requisition

**Actor**: HR Admin different from creator  
**Purpose**: Approve a submitted requisition.

**Preconditions**:

- Requisition is Pending Approval.
- Approving HR Admin is active.
- Approving HR Admin is not the creator.

**Success result**:

- Status changes to Approved.
- Approval actor and timestamp are stored.
- Status history is recorded.

**Failure result**:

- Creator self-approval is denied with an explanatory message.

## HR Open/Close Requisition

**Actor**: HR Admin  
**Purpose**: Make approved requisitions available to candidates or stop new applications.

**Open preconditions**:

- Requisition is Approved.

**Open success result**:

- Status changes to Open.
- Candidates can browse and apply.
- Status history is recorded.

**Close preconditions**:

- Requisition is Approved or Open.

**Close success result**:

- Status changes to Closed.
- New applications are blocked.
- Existing applications remain visible to authorized users.
- Status history is recorded.

## Candidate Profile Update

**Actor**: Candidate  
**Purpose**: Maintain profile fields required for application and scoring.

**Form input**:

- `phone`: Existing candidate contact field.
- `current_title`: Required before applying.
- `years_experience`: Required numeric value, minimum `0`.
- `location`: Required before applying.
- `resume_url`: Required resume reference before applying.
- `skill_keywords`: Required comma-separated skills or keywords list before applying.

**Success result**:

- Candidate profile updates.
- Candidate remains able to apply when all required fields are complete.

**Failure result**:

- Validation errors identify missing or invalid profile fields.

## Candidate Browse Open Jobs

**Actor**: Candidate  
**Purpose**: Find open requisitions and review requirements.

**Page contract**:

- Shows only Open requisitions.
- Shows title, department, description summary, and requirements summary.
- Provides detail and apply actions for Open jobs.

**Empty state**:

- Shows a message that no open jobs are available.

## Candidate Apply Once

**Actor**: Candidate  
**Purpose**: Submit one application for an Open requisition.

**Preconditions**:

- Candidate profile is complete.
- Requisition is Open at submission time.
- Candidate has not already applied to the requisition.

**Success result**:

- Creates one Application with Applied status.
- Calculates and stores simulated match score using clarified weights.
- Shows the existing application status and simulated score label.

**Failure results**:

- Duplicate application is blocked and redirects to existing application status.
- Closed/non-open requisition is blocked with a clear message.
- Incomplete profile redirects to profile edit guidance.

## HR Applicant Review

**Actor**: HR Admin  
**Purpose**: Review candidates for a requisition and manage application statuses.

**Page contract**:

- Shows applicant list for a selected requisition.
- Shows candidate summary, application status, applied date, and simulated match score.
- Supports sorting or filtering enough for HR to identify top-scoring applicants among 100 applications within the success criterion.

**Status update input**:

- `status`: Applied, Screening, Assessment, Interview, Offer, Rejected, or Hired.
- `reason`: Optional HR note.

**Success result**:

- Updates application status.
- Records application status history.
- Redirects back to applicant list or detail with a success message.

## Candidate Application Tracking

**Actor**: Candidate  
**Purpose**: View own application statuses.

**Page contract**:

- Shows only the signed-in candidate's applications.
- Shows job title, exact pipeline status, applied date, and simulated score label.
- Uses statuses Applied, Screening, Assessment, Interview, Offer, Rejected, and Hired.

**Privacy result**:

- Candidate cannot view another candidate's applications by URL guessing or form manipulation.
