# Route Map: Laravel RBAC Foundation

All routes are web routes in `routes/web.php` and return Blade pages, redirects, or form validation responses. No REST API contract is introduced.

## Public Routes

| Method | Path | Purpose | Access |
|--------|------|---------|--------|
| GET | `/` | Landing page or redirect to role dashboard when authenticated | Public |
| GET | `/register` | Show candidate registration form | Guests |
| POST | `/register` | Create candidate account with name, email, password, and phone | Guests |
| GET | `/login` | Show login form | Guests |
| POST | `/login` | Authenticate active account and redirect by role | Guests |

## Authenticated Routes

| Method | Path | Purpose | Access |
|--------|------|---------|--------|
| POST | `/logout` | End authenticated session | Authenticated users |
| GET | `/dashboard` | Redirect to current role dashboard | Authenticated active users |

## Role Dashboard Routes

| Method | Path | Purpose | Access |
|--------|------|---------|--------|
| GET | `/hr/dashboard` | HR admin dashboard | HR admin only |
| GET | `/interviewer/dashboard` | Technical interviewer dashboard | Technical interviewer only |
| GET | `/candidate/dashboard` | Candidate dashboard | Candidate only |

## HR User Administration Routes

| Method | Path | Purpose | Access |
|--------|------|---------|--------|
| GET | `/hr/users` | List user accounts for phase-1 administration | HR admin only |
| GET | `/hr/users/create` | Show account creation form | HR admin only |
| POST | `/hr/users` | Create HR admin, technical interviewer, or candidate account | HR admin only |
| GET | `/hr/users/{user}/access` | Show role/status change form | HR admin only |
| PUT/PATCH | `/hr/users/{user}/access` | Change role or active status | HR admin only |

## Candidate Routes

| Method | Path | Purpose | Access |
|--------|------|---------|--------|
| GET | `/candidate/profile` | Show authenticated candidate's own phase-1 profile summary | Candidate only |

## Middleware and Policy Notes

- All mutating forms require CSRF protection.
- All protected routes require authentication and active account status.
- Role middleware or policies gate dashboard and HR administration routes.
- Candidate profile access must verify the authenticated candidate owns the candidate profile.
- HR account creation and role/status changes write account audit records.
