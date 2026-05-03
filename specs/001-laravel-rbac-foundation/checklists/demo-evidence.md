# Demo Evidence Checklist

- Reviewer: Pending team peer reviewer
- Date: 2026-05-04
- Result: BLOCKED for runtime demo because Composer dependencies cannot be installed in this shell.

## Automated Evidence

- [x] `php artisan test` was attempted.
- [x] Result: FAIL before Laravel boot because `vendor/autoload.php` is missing.
- [x] Blocker: global `composer` is not available on PATH.
- [x] Temporary Composer bootstrap was attempted with `curl`, but Composer setup stopped because this PHP build lacks the `openssl` extension required for secure HTTPS transfers.
- [x] PHP syntax validation was run across created PHP and Blade files with `php -l`; all checked files reported no syntax errors.

## Quickstart Demo Flow Notes

- [ ] Register a candidate with name, email, password, and phone.
- [ ] Confirm candidate dashboard access and HR/interviewer denial.
- [ ] Sign in as the seeded HR admin.
- [ ] Create a technical interviewer and confirm an account audit record.
- [ ] Create or update a candidate role/status and confirm an account audit record.
- [ ] Sign in as the technical interviewer and confirm interviewer-only dashboard access.
- [ ] Deactivate a non-HR user and confirm login/protected access denial.
- [ ] Attempt to deactivate or downgrade the last active HR admin and confirm denial.

## Reviewer Notes

The manual Blade-page quickstart could not be executed in this environment because Laravel dependencies are unavailable. After installing Composer with OpenSSL-enabled PHP, run `composer install`, `php artisan migrate --seed`, `php artisan test`, and then complete the demo flow above.
