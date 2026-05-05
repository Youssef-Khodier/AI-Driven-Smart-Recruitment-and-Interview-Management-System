# UI/UX Implementation Plan

## 1. Goal
Integrate the newly designed Tailwind CSS screens (generated via Stitch) into the SRIM Vanilla PHP monolithic MVC architecture. This phase transitions the application from raw functionality to a fully polished, responsive, and professional user interface.

## 2. Approach
Per the **Tailwind CSS Amendment** (Constitution v2.1.0), we will use Tailwind CSS via CDN. The reference UI designs and HTML code have been downloaded to the `stitch_screens/` directory in the project root. We will extract the repeated HTML structures (like headers, navigation, and the Tailwind configuration) from the templates in `stitch_screens/` into a central layout file, and then systematically update individual page views to match the new designs, plugging in dynamic PHP variables where appropriate.

## 3. Task List

### Phase 1: Global Layout & Configuration Setup
- [ ] Extract the Tailwind configuration object from the Stitch HTML (`<script id="tailwind-config">`) into a dedicated `public/js/tailwind-config.js` file.
- [ ] Create a master layout file `views/layouts/app.php` that includes:
    - HTML `<head>` with Tailwind CDN, Google Fonts (Inter), and Material Symbols.
    - Global Top Navigation Bar (with dynamic logic to show HR/Candidate links based on session role).
    - A `<main>` wrapper for page content.
    - Flash messages placeholder styled with Tailwind alerts.

### Phase 2: Candidate Portal UI Update
- [ ] **Auth Pages**: Update `views/auth/login.php` and `views/auth/register.php` with the centered Auth card template.
- [ ] **Dashboard**: Create `views/candidate/dashboard.php` utilizing the new dashboard cards.
- [ ] **Open Jobs List**: Update `views/candidate/jobs.php` with the new listing table/cards.
- [ ] **Application Detail & Progress**: Implement the stepper and application timeline in `views/candidate/application.php`.
- [ ] **My Profile**: Update the candidate profile form in `views/candidate/profile.php`.

### Phase 3: HR Admin Portal UI Update
- [ ] **HR Dashboard**: Update `views/hr/dashboard.php` with the robust analytics and table cards.
- [ ] **Requisition Detail**: Apply the new detail view layout to `views/hr/requisition_detail.php`.
- [ ] **User Management**: Update the users list to the new Tailwind data table in `views/hr/users.php`.
- [ ] **Job Offers**: Implement the offer detail page `views/hr/offer.php`.

### Phase 4: Interviewer Portal UI Update
- [ ] **Technical Assessment**: Implement the focused assessment view in `views/interviewer/assessment.php`.
- [ ] **Assessment Results**: Update the results card.

### Phase 5: Controller & Routing Verification
- [ ] Ensure all relevant controllers (e.g., `AuthController`, `DashboardController`, `RequisitionController`) inject the correct `$data` array and use `renderView('layout/app', 'view_name')`.
- [ ] Verify CSRF tokens are present in all new Tailwind forms.
- [ ] Verify validation errors are displayed correctly using the new UI text colors (e.g., `text-error`).

## 4. Evidence Checkpoint
- Visual confirmation of the new UI matching the Stitch prototypes.
- Navigation flows correctly between role-based dashboards.
- Forms successfully submit with CSRF tokens and return Tailwind-styled validation errors on failure.
