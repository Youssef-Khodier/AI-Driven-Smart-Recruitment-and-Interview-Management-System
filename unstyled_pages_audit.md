# SRIM Front-End Styling Audit

> [!NOTE]
> Based on a full code review of all **41 view files** and live browser verification.
> **Detection method:** Views using Tailwind CSS utility classes (e.g., `bg-card-surface`, `rounded-xl`, `shadow-ambient`, `text-primary`) are marked ✅ STYLED. Views using only raw HTML tags (`<h1>`, `<table>`, `<form>`, `<label>`) with no CSS classes are marked ❌ UNSTYLED.

---

## Summary

| Category | Styled ✅ | Unstyled ❌ | Total |
|---|---|---|---|
| Auth | 2 | 0 | 2 |
| Welcome | 1 | 0 | 1 |
| HR Dashboard | 1 | 0 | 1 |
| HR Users | 1 | 2 | 3 |
| HR Requisitions | 1 | 2 | 3 |
| HR Applications | 0 | 1 | 1 |
| HR Assessments | 0 | 5 | 5 |
| HR Assessment Questions | 0 | 1 | 1 |
| HR Interviews | 0 | 3 | 3 |
| HR Evaluations | 0 | 1 | 1 |
| HR Offers | 0 | 3 | 3 |
| HR Onboarding | 0 | 3 | 3 |
| Candidate Dashboard | 1 | 0 | 1 |
| Candidate Profile | 1 | 0 | 1 |
| Candidate Jobs | 1 | 1 | 2 |
| Candidate Applications | 2 | 0 | 2 |
| Candidate Assessments | 0 | 2 | 2 |
| Candidate Offers | 0 | 1 | 1 |
| Interviewer Dashboard | 0 | 1 | 1 |
| Interviewer Interviews | 2 | 0 | 2 |
| Interviewer Feedback | 0 | 1 | 1 |
| Error Pages | 0 | 4 | 4 |
| **TOTAL** | **13** | **31** | **44** |

---

## ✅ Pages WITH Styling (13 pages)

These pages use the Tailwind design system with cards, proper spacing, Material Symbols icons, hover effects, and status badges:

| # | Route | View File | Notes |
|---|---|---|---|
| 1 | `/` | [welcome.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/welcome.php) | Hero section, styled CTA buttons |
| 2 | `/login` | [login.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/auth/login.php) | Centered card, styled inputs |
| 3 | `/register` | [register.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/auth/register.php) | 2-col grid form, styled inputs |
| 4 | `/hr/dashboard` | [dashboard.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/dashboard.php) | Nav cards with icons & hover-lift |
| 5 | `/hr/users` | [index.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/users/index.php) | Styled table, avatar initials, badges |
| 6 | `/hr/requisitions` | [index.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/requisitions/index.php) | Filter pills, styled table, status badges |
| 7 | `/candidate/dashboard` | [dashboard.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/candidate/dashboard.php) | Card layout, Material icons |
| 8 | `/candidate/profile` | [profile.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/candidate/profile.php) | Grid form, styled inputs |
| 9 | `/candidate/jobs` | [index.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/candidate/jobs/index.php) | Styled table with hover effects |
| 10 | `/candidate/applications` | [index.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/candidate/applications/index.php) | Styled table, status badges |
| 11 | `/candidate/applications/{id}` | [show.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/candidate/applications/show.php) | Cards, offer section, Material icons |
| 12 | `/interviewer/interviews` | [index.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/interviewer/interviews/index.php) | Styled table, feedback status indicators |
| 13 | `/interviewer/interviews/{id}` | [show.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/interviewer/interviews/show.php) | 3-column grid, cards, briefing layout |

---

## ❌ Pages WITHOUT Styling (31 pages)

### Your 5 Identified Pages ✓ Confirmed

| # | Route | View File | Current State |
|---|---|---|---|
| 1 | `/hr/requisitions/create` | [form.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/requisitions/form.php) | Raw `<h1>`, `<label>`, `<input>`, `<textarea>`, `<button>` — no layout, no classes |
| 2 | `/hr/users/create` | [create.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/users/create.php) | Raw form, no container, no grid |
| 3 | `/hr/requisitions/{id}` | [show.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/requisitions/show.php) | Raw `<h1>`, `<p>`, unstyled `<table>` for status history |
| 4 | `/hr/users/{id}/access` | [access.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/users/access.php) | Raw form, unstyled selects |
| 5 | `/interviewer/dashboard` | [dashboard.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/interviewer/dashboard.php) | Plain text, unstyled link, `class="muted"` (undefined) |

### Additional Unstyled HR Pages (17 more)

| # | Route | View File | Current State |
|---|---|---|---|
| 6 | `/hr/requisitions/{id}/edit` | [form.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/requisitions/form.php) | Same unstyled form as create |
| 7 | `/hr/requisitions/{id}/applications` | [index.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/applications/index.php) | Unstyled table, inline forms, `class="muted"` |
| 8 | `/hr/requisitions/{id}/assessments` | [index.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/assessments/index.php) | Unstyled table, `class="button"` (undefined) |
| 9 | `/hr/requisitions/{id}/assessments/create` | [form.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/assessments/form.php) | Raw form, `class="muted"` |
| 10 | `/hr/assessments/{id}` | [show.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/assessments/show.php) | Raw tables, `class="button"` and `class="muted"` |
| 11 | `/hr/assessments/{id}/edit` | [form.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/assessments/form.php) | Same unstyled form |
| 12 | `/hr/assessments/{id}/questions/create` | [form.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/assessment-questions/form.php) | Raw form, `class="muted"` |
| 13 | `/hr/assessment-questions/{id}/edit` | [form.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/assessment-questions/form.php) | Same unstyled form |
| 14 | `/hr/requisitions/{id}/assessment-results` | [results.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/assessments/results.php) | Unstyled table |
| 15 | `/hr/candidate-assessments/{id}` | [attempt.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/assessments/attempt.php) | Unstyled tables |
| 16 | `/hr/interviews` | [index.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/interviews/index.php) | Raw `<table>`, `<th>`, `<td>` — no classes |
| 17 | `/hr/interviews/{id}` | [show.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/interviews/show.php) | Raw `<div>`, `<table>`, inline `style="display:inline;"` |
| 18 | `/hr/applications/{id}/interviews/create` | [form.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/interviews/form.php) | Raw form, inline `style="margin-bottom: 10px;"`, `class="error"` |
| 19 | `/hr/applications/{id}/final-evaluation` | [show.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/evaluations/show.php) | Raw form, inline `style="color: orange"` |
| 20 | `/hr/offers` | [index.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/offers/index.php) | Raw table, no classes |
| 21 | `/hr/offers/{id}` | [show.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/offers/show.php) | Raw `<p>`, `class="button"` (undefined) |
| 22 | `/hr/applications/{id}/offers/create` | [form.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/offers/form.php) | Raw form, no layout |
| 23 | `/hr/onboarding` | [index.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/onboarding/index.php) | Raw table, no classes |
| 24 | `/hr/onboarding/{id}` | [show.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/onboarding/show.php) | Raw text + includes unstyled form |
| 25 | `/hr/offers/{id}/onboarding/create` | [form.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/hr/onboarding/form.php) | Raw form, no layout |

### Additional Unstyled Candidate Pages (3 more)

| # | Route | View File | Current State |
|---|---|---|---|
| 26 | `/candidate/jobs/{id}` | [show.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/candidate/jobs/show.php) | Raw `<h1>`, `<p>`, `class="alert alert-success"` (undefined) |
| 27 | `/candidate/assessments/{id}` | [show.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/candidate/assessments/show.php) | Raw forms, inline `style` attributes |
| 28 | `/candidate/assessments/{id}/result` | [result.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/candidate/assessments/result.php) | Raw table, `class="muted"` |
| 29 | `/candidate/offers/{id}` | [show.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/candidate/offers/show.php) | Inline `style` attributes, raw buttons |

### Unstyled Interviewer Page (1 more)

| # | Route | View File | Current State |
|---|---|---|---|
| 30 | `/interviewer/interviews/{id}/feedback` | [feedback.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/interviewer/interviews/feedback.php) | Raw form, `class="error"` (undefined) |

### Unstyled Error Pages (4)

| # | Route | View File | Current State |
|---|---|---|---|
| 31 | `403` | [403.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/errors/403.php) | Raw `<h1>` and `<p>` only |
| 32 | `404` | [404.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/errors/404.php) | Raw `<h1>` and `<p>` only |
| 33 | `419` | [419.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/errors/419.php) | Raw `<h1>` and `<p>` only |
| 34 | `500` | [500.php](file:///h:/Apps/XAMPPP/htdocs/srim/views/errors/500.php) | Raw `<h1>` and `<p>` only |

---

## Undefined CSS Classes Used

These classes appear in unstyled views but have **no definition** anywhere:

| Class | Used In |
|---|---|
| `muted` | assessments/form, assessment-questions/form, assessments/results, interviewer dashboard, hr applications index |
| `button` | requisitions/show, assessments/index, assessments/show, evaluations/show, offers/show |
| `error` | interviews/form, interviewer feedback |
| `alert alert-success` | candidate/jobs/show |
| `inline` | multiple forms |
| `actions` | requisitions/show |

---

## Browser Recording

![Front-end styling review recording](C:/Users/Y416/.gemini/antigravity/brain/22e9113a-2b7d-4aaf-9456-b1425066e6bc/page_styling_review_1777947541115.webp)

---

## Test Credentials

| Role | Email | Password |
|---|---|---|
| HR Admin | `hr.admin@example.com` | `password` |
| Interviewer | `Interviewer@gmail.com` | `password` |
| Candidate | Register via `/register` | (no seeded candidate) |
