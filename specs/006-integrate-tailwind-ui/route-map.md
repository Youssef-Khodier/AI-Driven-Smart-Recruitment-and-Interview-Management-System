# Phase 1: Route Map & View Integration

The existing routing in `routes/web.php` remains largely unchanged. The updates will happen within the `views/` directory and potentially the controllers to ensure correct data structures are passed to the new Tailwind views.

## Target Views (based on stitch_screens)
- `views/layouts/app.php` -> Global Layout with Tailwind, Role-based Nav (derived from existing layout and Stitch Navbars).
- `views/auth/login.php`, `register.php` -> Based on Stitch Candidate Registration / User Management (Adapted for Auth).
- `views/candidate/dashboard.php`, `jobs.php`, `applications.php` -> Based on Stitch `5_Browse_Open_Jobs.html`, `6_My_Applications.html`.
- `views/hr/dashboard.php`, `requisitions.php`, `users.php` -> Based on Stitch `11_HR_Dashboard_SRIM.html`, `12_Requisition_Detail_White_Nav.html`, `3_User_Management.html`.
- `views/interviewer/assessment.php` -> Based on Stitch `14_Technical_Assessment.html`.

## Target Controllers (for view data bindings)
- `App\Controllers\AuthController`
- `App\Controllers\DashboardController`
- `App\Controllers\CandidateController`
- `App\Controllers\HrController`
- `App\Controllers\InterviewerInterviewController`