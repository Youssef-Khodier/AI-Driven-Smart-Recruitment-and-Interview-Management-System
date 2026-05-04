# Route Map: Job Requisition and Candidate Applications

All routes are web routes in `routes/web.php`, protected by session authentication, CSRF on mutating forms, active-account checks, and role/policy authorization. This file is a planning contract, not an API contract.

## HR Routes

| Method | Path | Route Name | Controller Action | Purpose |
|--------|------|------------|-------------------|---------|
| GET | `/hr/requisitions` | `hr.requisitions.index` | `Hr\JobRequisitionController@index` | List/filter requisitions |
| GET | `/hr/requisitions/create` | `hr.requisitions.create` | `Hr\JobRequisitionController@create` | Show create form |
| POST | `/hr/requisitions` | `hr.requisitions.store` | `Hr\JobRequisitionController@store` | Save Draft requisition |
| GET | `/hr/requisitions/{job}` | `hr.requisitions.show` | `Hr\JobRequisitionController@show` | Show requisition detail |
| GET | `/hr/requisitions/{job}/edit` | `hr.requisitions.edit` | `Hr\JobRequisitionController@edit` | Show edit form |
| PUT | `/hr/requisitions/{job}` | `hr.requisitions.update` | `Hr\JobRequisitionController@update` | Update Draft requisition with stale-edit protection |
| POST | `/hr/requisitions/{job}/submit` | `hr.requisitions.submit` | `Hr\JobRequisitionController@submit` | Move Draft to Pending Approval |
| POST | `/hr/requisitions/{job}/approve` | `hr.requisitions.approve` | `Hr\JobRequisitionController@approve` | Approve Pending Approval requisition |
| POST | `/hr/requisitions/{job}/open` | `hr.requisitions.open` | `Hr\JobRequisitionController@open` | Open Approved requisition |
| POST | `/hr/requisitions/{job}/close` | `hr.requisitions.close` | `Hr\JobRequisitionController@close` | Close Approved/Open requisition |
| GET | `/hr/requisitions/{job}/applications` | `hr.requisitions.applications.index` | `Hr\ApplicationController@index` | Review applicants for a requisition |
| PATCH | `/hr/applications/{application}/status` | `hr.applications.status` | `Hr\ApplicationController@updateStatus` | Update application status |

## Candidate Routes

| Method | Path | Route Name | Controller Action | Purpose |
|--------|------|------------|-------------------|---------|
| GET | `/candidate/jobs` | `candidate.jobs.index` | `Candidate\JobController@index` | Browse Open requisitions |
| GET | `/candidate/jobs/{job}` | `candidate.jobs.show` | `Candidate\JobController@show` | View Open requisition detail |
| POST | `/candidate/jobs/{job}/apply` | `candidate.jobs.apply` | `Candidate\ApplicationController@store` | Apply once to Open requisition |
| GET | `/candidate/applications` | `candidate.applications.index` | `Candidate\ApplicationController@index` | Track own applications |
| GET | `/candidate/applications/{application}` | `candidate.applications.show` | `Candidate\ApplicationController@show` | View own application detail |
| GET | `/candidate/profile` | `candidate.profile.edit` | `Candidate\ProfileController@edit` | Existing profile edit page |
| PUT | `/candidate/profile` | `candidate.profile.update` | `Candidate\ProfileController@update` | Update profile including skill keywords |

## Policy Notes

- `JobRequisitionPolicy` controls HR lifecycle actions and candidate Open-job visibility.
- `ApplicationPolicy` controls candidate ownership, HR review access, and HR status updates.
- Route model binding must not leak unauthorized records; policies deny access before rendering sensitive details.
