# Project Folder Guide

## Root Files

### `.env`
Stores the local environment settings, such as database name, database user, password, and first HR admin account values.

### `.env.example`
Template file showing which environment values are needed. Use it when setting up the project on a new machine.

### `.gitignore`
Lists files and folders that should not be committed to git, such as local logs, `.env`, `vendor`, and editor files.

### `.htaccess`
Apache configuration for routing requests through the app and blocking direct public access to private folders and sensitive files.

### `composer.json`
Composer project file. It defines project metadata and the PHP version requirement.

### `index.php`
Main entry point for the web app. It loads the application from `bootstrap/app.php` and runs it.

### `README.md`
Main project overview, setup guide, seeded accounts, and demo script.

## Folders

### `app`
Contains the main PHP application code.

#### `app/Controllers`
Handles browser requests, reads user input, calls repositories or services, and returns views or redirects.

#### `app/Core`
Contains the small custom framework pieces used by the project, including routing, requests, responses, sessions, validation, authentication helpers, database setup, and view rendering.

#### `app/Enums`
Contains fixed status and type values used across the system, such as user roles, application statuses, interview statuses, offer statuses, and audit actions.

#### `app/Policies`
Contains authorization rules. These files decide what each user role is allowed to view or change.

#### `app/Repositories`
Contains database access logic. Controllers use repositories to read and write recruitment, interview, assessment, offer, onboarding, audit, and compliance data.

#### `app/Services`
Contains business logic that is bigger than simple database access, such as screening scores, duplicate detection, simulated assessments, offer calculations, background checks, and template comparisons.

### `bootstrap`
Starts the app.

#### `bootstrap/app.php`
Loads config, starts sessions, configures the database, registers routes, and returns the app instance.

#### `bootstrap/autoload.php`
Custom class autoloader for the app. This project uses this instead of relying on Composer autoloading.

### `database`
Contains database setup files.

#### `database/schema.sql`
Full database schema used to create the tables.

#### `database/migrations`
Contains extra SQL migration files for later features, such as governance, assessments, interviews, feedback, offers, onboarding, and compliance.

### `Diagrams`
Contains project diagrams and documentation used to explain the system design, database design, and architecture.

### `routes`
Contains route definitions.

#### `routes/web.php`
Maps URLs to controller methods. This is where the app defines pages and form actions.

### `storage`
Stores generated app files and logs that should not be public.

#### `storage/app`
Place for generated or uploaded application files if the app needs them.

#### `storage/logs`
Place for runtime log files. Log files can usually be deleted when they are no longer needed for debugging.

### `vendor`
Composer-generated folder. It can be regenerated with `composer install`. This project currently has no third-party Composer packages.

### `views`
Contains server-rendered PHP templates.

#### `views/auth`
Login and registration pages.

#### `views/candidate`
Candidate pages for dashboard, profile, jobs, applications, assessments, interviews, offers, and onboarding.

#### `views/errors`
Error pages such as 403, 404, 419, and 500.

#### `views/hr`
HR and admin pages for recruitment, requisitions, applications, screening, assessments, interviews, feedback, offers, onboarding, reports, compliance, audit logs, users, and governance.

#### `views/interviewer`
Interviewer pages for assigned interviews, interview details, workspace, and feedback.

#### `views/interviews`
Shared interview workspace pages.

#### `views/layouts`
Shared layout template used by the app.

#### `views/notifications`
Notification list page.
