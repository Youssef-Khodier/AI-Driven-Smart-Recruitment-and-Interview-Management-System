# SRIM Project Review — Why It's Not Working Well

## Summary

The project is a **hybrid codebase** that was partially migrated from Laravel to Vanilla PHP MVC. The active runtime (`app/Core/*`, `app/Controllers/*`, `views/*`, `routes/web.php`) is a custom Vanilla PHP MVC framework, but the old Laravel code (`app/Models/*`, `app/Policies/*`, `app/Http/*`, `app/Providers/*`, `app/Support/*`, `database/migrations/*`, `database/seeders/*`, `config/*`) was **never removed**. This creates a split-brain architecture with one critical crash bug and many latent issues.

---

## 🔴 Critical — App-Crashing Bug

### 1. `DashboardController::redirect()` Conflicts with `Controller::redirect()`

**This is the #1 reason the app breaks after login.**

The base [Controller.php](file:///h:/Apps/XAMPPP/htdocs/srim/app/Core/Controller.php#L12-L15) defines:
```php
protected function redirect(string $path): Response  // line 12-15
```

The [DashboardController.php](file:///h:/Apps/XAMPPP/htdocs/srim/app/Controllers/DashboardController.php#L12-L21) defines a **public method with the same name but incompatible signature**:
```php
public function redirect(Request $request): Response  // line 12-21
```

PHP throws a **Fatal Error** at runtime:
> `Fatal error: Declaration of App\Controllers\DashboardController::redirect(App\Core\Request $request): App\Core\Response must be compatible with App\Core\Controller::redirect(string $path): App\Core\Response`

**Impact:** Every user who logs in (any role) or visits `/dashboard` gets a fatal error. The entire post-login experience is dead.

**Fix:** Rename the method to something like `redirectToDashboard()` and update the route in `web.php`.

---

## 🟠 High Severity Issues

### 2. Dead Laravel Code Everywhere — Split-Brain Architecture

The constitution (v2.0.0) explicitly states: *"Laravel framework code, Blade templates, Eloquent models, migrations, Form Requests, and Artisan commands are migration sources only and MUST be replaced by framework-free equivalents before the rewrite is considered complete."*

Yet these **Laravel-dependent files remain in the project** and are never loaded by the Vanilla runtime:

| Directory | What It Contains | Problem |
|---|---|---|
| `app/Models/` (14 files) | Eloquent models extending `Illuminate\Database\Eloquent\Model` | Import `Illuminate\*` classes that don't exist (no Laravel in `composer.json`) |
| `app/Policies/` (5 files) | Policies using Eloquent `User` model objects | Reference `App\Models\User` (Eloquent), not the plain array auth system |
| `app/Http/Controllers/` | Old Laravel controllers | Duplicate logic of `app/Controllers/` |
| `app/Http/Middleware/` | Laravel middleware classes | Not wired into the Vanilla PHP router |
| `app/Http/Requests/` | Laravel Form Request classes | Not used at all |
| `app/Providers/` | `AppServiceProvider.php` | Laravel service provider — nothing calls it |
| `app/Support/` | Duplicate `SimulatedAssessmentScorer`, `SimulatedMatchScorer` | Parallel copies of `app/Services/*` |
| `database/migrations/` (16 files) | Laravel migration classes | `artisan migrate` can't run — no Laravel installed |
| `database/seeders/` (4 files) | Laravel seeders using Eloquent | `artisan db:seed` can't run — no Laravel installed |
| `config/database.php` | Uses `env()` and `Illuminate\Support\Str` | Fatal if any code tries to `require` it |

> [!CAUTION]
> The `app/Models/` files are autoloadable via the PSR-style autoloader in `bootstrap/autoload.php`. If any code accidentally instantiates `new \App\Models\User()`, PHP will crash because `Illuminate\Foundation\Auth\User` doesn't exist. The autoloader will find the file, but its `use` statements reference a framework that isn't installed.

### 3. No Database Seeding Path for Vanilla PHP

The `database/schema.sql` only seeds two departments. There is **no Vanilla PHP script** to create the first HR Admin user. The Laravel seeders (`FirstHrAdminSeeder`, `AssessmentDemoSeeder`) can't run without Artisan/Eloquent.

**Impact:** After running `schema.sql`, there are zero users in the database. You **cannot log in** because there is no HR Admin account and no CLI seeder to create one.

**Fix:** Create a `scripts/seed.php` that inserts the first HR Admin using PDO.

### 4. Policies Are Never Enforced

All 5 policy classes in `app/Policies/` are **pure Laravel artifacts**. They depend on Eloquent model instances (`User`, `JobRequisition`, `CandidateAssessment`). The Vanilla PHP controllers use `$this->requireRole()` for coarse RBAC checks but **never consult any policy**.

**Missing policy enforcement examples:**
- `HrController::transitionRequisition()` — allows **any** status transition (DRAFT → CLOSED is allowed, skipping PENDING → APPROVED → OPEN)
- `HrController::updateRequisition()` — no check for whether the requisition is in an editable state
- `HrController::updateApplication()` — no check for valid status transitions
- `JobRequisitionPolicy::approve()` — requires `created_by !== user_id` (self-approval guard) but the controller doesn't enforce this

### 5. `config/` Files Are Laravel-Only and Dangerous

[config/database.php](file:///h:/Apps/XAMPPP/htdocs/srim/config/database.php) has:
```php
use Illuminate\Support\Str;  // Fatal: class not found
```

[config/app.php](file:///h:/Apps/XAMPPP/htdocs/srim/config/app.php) calls `env()` — a Laravel helper that doesn't exist in Vanilla PHP.

These files are not currently loaded, but their presence is misleading and will cause fatal errors if anyone tries to use them.

---

## 🟡 Medium Severity Issues

### 6. Validator Has Subtle Bugs

[Validator.php](file:///h:/Apps/XAMPPP/htdocs/srim/app/Core/Validator.php):

- **Non-required empty fields still get validated:** If a field has `['max', 160]` but no `'required'`, and the user submits `null`, the field is still added to `$validated` as `null`. The `min`/`max` checks won't fire (they check `is_string`), but `$validated[$field]` still returns `null`, which may later be inserted into a NOT NULL column.
- **`in` rule with pipe syntax is broken:** The `in` rule uses `is_array($rule)` to extract the parameter, but if rules are provided as a pipe-separated string (`'required|in:FOO,BAR'`), the parameter extraction won't work. The array syntax works fine, but this inconsistency is a trap.
- **No `unique` rule:** Email uniqueness is checked manually in controllers, which is fine, but it means the validator's abstraction is incomplete.

### 7. `is_active` Checkbox Handling Is Fragile

In [AssessmentController::store()](file:///h:/Apps/XAMPPP/htdocs/srim/app/Controllers/AssessmentController.php#L54):
```php
'is_active' => isset($data['is_active']) ? 1 : 0,
```

The validator always adds `is_active` to `$validated` (even as `null`), so `isset($data['is_active'])` returns `false` when unchecked BUT also `false` when the field is missing entirely. This works **by accident** because unchecked checkboxes don't send a value. However, the `$validated` array will contain `'is_active' => null`, and `isset(null)` returns `false`. This is fragile — a more explicit check like `!empty()` or `=== '1'` would be safer.

### 8. `JobRequisitionStatus` Enum Values Don't Match Database

[JobRequisitionStatus.php](file:///h:/Apps/XAMPPP/htdocs/srim/app/Enums/JobRequisitionStatus.php):
```php
case DRAFT = 'Draft';
case PENDING_APPROVAL = 'Pending Approval';
case APPROVED = 'Approved';
case OPEN = 'Open';
case CLOSED = 'Closed';
```

But the database stores **uppercase** statuses: `'DRAFT'`, `'PENDING'`, `'APPROVED'`, `'OPEN'`, `'CLOSED'`. The controllers use uppercase strings. This enum is a leftover Laravel artifact that's not used by the Vanilla runtime — but if anyone tries to use it for validation, comparisons will silently fail.

### 9. `back()` Method Creates a New Request Capture

[Controller.php line 18](file:///h:/Apps/XAMPPP/htdocs/srim/app/Core/Controller.php#L17-L20):
```php
protected function back(): Response
{
    return Response::redirect(Request::capture()->referer() ?: url('dashboard'));
}
```

This calls `Request::capture()` a second time, re-reading `$_SERVER`. This works but is wasteful and inconsistent — the request was already captured and passed through the router. `back()` isn't used currently, but it's a latent issue.

### 10. Session Not Regenerated After Registration

[AuthController::storeRegistration()](file:///h:/Apps/XAMPPP/htdocs/srim/app/Controllers/AuthController.php#L82):
```php
Session::put('user_id', $candidateId);
```

After registration, the session ID is **not regenerated** (unlike `Auth::attempt()` which calls `Session::regenerate()`). This is a session fixation vulnerability — an attacker who knows the session ID before registration can hijack the session afterward.

### 11. Missing `artisan` Replacement

The [artisan](file:///h:/Apps/XAMPPP/htdocs/srim/artisan) file still exists but is a Laravel CLI entry point. Since there's no Laravel, it can't work. There's no Vanilla PHP CLI tool for:
- Running `schema.sql`
- Seeding the database
- Creating users from the command line

---

## 🔵 Low Severity / Cleanup Issues

### 12. Duplicate Service Classes

| Active (Used) | Dead (Unused) |
|---|---|
| `app/Services/SimulatedMatchScorer.php` | `app/Support/SimulatedMatchScorer.php` |
| `app/Services/SimulatedAssessmentScorer.php` | `app/Support/SimulatedAssessmentScorer.php` |

### 13. `composer.json` Has No Autoload Configuration

The `autoload` and `autoload-dev` keys are empty `{}`. The project uses its own PSR-style autoloader in `bootstrap/autoload.php`. This is fine, but it means `composer dump-autoload` does nothing useful, and the `vendor/` directory exists with an empty Composer autoloader.

### 14. Two Entry Points (`index.php` and `public/index.php`)

Both the root `index.php` and `public/index.php` do the same thing. When accessed via `http://localhost/srim/`, the root `.htaccess` rewrites to root `index.php`. The `public/index.php` is a dead Laravel convention leftover.

### 15. No Error Logging

The `App::run()` method catches exceptions and renders error views, but there's no `error_log()` or file logging. In production, errors vanish silently.

### 16. `str_limit()` Uses `strlen()` Instead of `mb_strlen()`

[helpers.php line 67](file:///h:/Apps/XAMPPP/htdocs/srim/app/Core/helpers.php#L67-L72): With `utf8mb4` data, multi-byte characters will cause incorrect truncation.

### 17. `HttpException` Misuses Status Code for Redirects

The [Controller::requireAuth()](file:///h:/Apps/XAMPPP/htdocs/srim/app/Core/Controller.php#L22-L27) throws `HttpException(302, ...)` to trigger redirects. While `App::run()` handles this, it's a semantic misuse — HTTP 302 isn't an "error." A dedicated `RedirectException` or explicit `return` would be cleaner.

---

## File Cleanup Recommendation

The following files/directories are **dead Laravel leftovers** that should be removed or replaced with Vanilla PHP equivalents:

```
DELETE:
├── app/Http/                    (entire directory)
├── app/Models/                  (entire directory — controllers use raw SQL)
├── app/Policies/                (rewrite as Vanilla PHP policy classes)
├── app/Providers/               (entire directory)
├── app/Support/                 (duplicated in app/Services/)
├── artisan                      (Laravel CLI — non-functional)
├── bootstrap/providers.php      (Laravel artifact)
├── config/app.php               (calls env() — Laravel helper)
├── config/auth.php              (Laravel auth config)
├── config/cache.php             (Laravel cache config)
├── config/database.php          (uses Illuminate\Support\Str — fatal)
├── config/logging.php           (Laravel logging config)
├── config/session.php           (Laravel session config)
├── database/migrations/         (entire directory — use schema.sql instead)
├── database/seeders/            (rewrite as scripts/seed.php using PDO)
├── phpunit.xml                  (references Laravel TestCase — won't work)
├── public/index.php             (dead duplicate of root index.php)
├── routes/console.php           (Laravel console routes)

CREATE:
├── scripts/seed.php             (PDO-based first HR Admin + demo data seeder)
```

---

## Priority Action Items

| # | Priority | Issue | Fix |
|---|---|---|---|
| 1 | 🔴 Critical | `DashboardController::redirect()` crashes | Rename method to `redirectToDashboard()` |
| 2 | 🔴 Critical | No way to seed first HR Admin | Create `scripts/seed.php` with PDO |
| 3 | 🟠 High | Dead Laravel code creates confusion and crash risk | Delete `app/Models/`, `app/Http/`, `app/Policies/`, `app/Providers/`, `app/Support/`, `config/`, old seeders/migrations |
| 4 | 🟠 High | Policies not enforced — status transitions unchecked | Implement Vanilla PHP policy checks in controllers |
| 5 | 🟡 Medium | Session fixation after registration | Add `Session::regenerate()` after registration |
| 6 | 🟡 Medium | Enum values don't match DB uppercase | Fix or remove the Enum files |
| 7 | 🔵 Low | No error logging | Add `error_log()` calls in `App::run()` |
| 8 | 🔵 Low | Multi-byte string truncation | Use `mb_strlen()` / `mb_substr()` |

---

## Browser Testing Evidence

![SRIM Fatal Error on Dashboard](file:///C:/Users/Y416/.gemini/antigravity/brain/5d1be0dd-f784-4b6e-a565-bb370f0576c7/srim_homepage_test_1777909173460.webp)

The recording above shows: homepage loads ✅ → login page works ✅ → registration form works ✅ → redirect to dashboard **crashes with Fatal Error** ❌
