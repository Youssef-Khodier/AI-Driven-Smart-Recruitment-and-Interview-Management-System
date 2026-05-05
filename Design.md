# SRIM Design System

> The canonical design reference for the AI-Driven Smart Recruitment & Interview Management System.
> All server-rendered PHP templates, layouts, and CSS must follow this specification.

---

## 1. Brand Identity

| Property | Value |
|----------|-------|
| **Product Name** | SRIM — Smart Recruitment & Interview Management System |
| **Tagline** | AI-Driven Smart Recruitment Platform |
| **Personality** | Professional, trustworthy, modern enterprise SaaS |
| **Feel** | Clean, spacious, data-rich but not overwhelming |
| **Inspiration** | Workday, Greenhouse, Lever, BambooHR |

---

## 2. Color System

### 2.1 Core Palette

| Token | Hex | Usage |
|-------|-----|-------|
| `--primary` | `#172033` | Header, primary text, dark surfaces |
| `--accent` | `#1f5eff` | Buttons, links, interactive elements |
| `--accent-hover` | `#1a4ecc` | Button/link hover state |
| `--bg` | `#f5f7fb` | Page background |
| `--card` | `#ffffff` | Card surfaces |
| `--card-shadow` | `rgba(23, 32, 51, 0.08)` | Card elevation |
| `--text` | `#172033` | Body text |
| `--text-muted` | `#64748b` | Secondary/helper text |
| `--border` | `#e2e8f0` | Borders, dividers, table rules |

### 2.2 Semantic Colors

| Token | Background | Text | Usage |
|-------|-----------|------|-------|
| `--success` | `#e8f7ef` | `#16a34a` | Success alerts, positive states |
| `--warning` | `#fef3c7` | `#d97706` | Warning alerts, pending actions |
| `--error` | `#fdecec` | `#dc2626` | Error alerts, validation, destructive states |
| `--info` | `#eff6ff` | `#2563eb` | Informational alerts |

### 2.3 Status Badge Colors

Every status in the system maps to a colored pill badge:

| Status | Background | Text | Context |
|--------|-----------|------|---------|
| `DRAFT` | `#94a3b8` | `#475569` | Requisitions, offers |
| `PENDING` / `PENDING_APPROVAL` | `#f59e0b` | `#92400e` | Requisitions, onboarding |
| `APPROVED` / `SENT` | `#3b82f6` | `#1e40af` | Requisitions, offers |
| `OPEN` / `ACTIVE` | `#22c55e` | `#166534` | Requisitions, accounts |
| `CLOSED` / `COMPLETED` | `#1e293b` | `#ffffff` | Requisitions, onboarding |
| `REJECTED` | `#ef4444` | `#991b1b` | Applications, offers |
| `EXPIRED` | `#f97316` | `#9a3412` | Offers |
| `HIRED` | `#10b981` | `#065f46` | Applications |

### 2.4 Role Badge Colors

| Role | Background | Text |
|------|-----------|------|
| `HR_ADMIN` | `#7c3aed` | `#ffffff` |
| `CANDIDATE` | `#3b82f6` | `#ffffff` |
| `INTERVIEWER` | `#16a34a` | `#ffffff` |

---

## 3. Typography

| Element | Font | Size | Weight | Extra |
|---------|------|------|--------|-------|
| **Font Stack** | `Inter, system-ui, -apple-system, sans-serif` | — | — | Load from Google Fonts |
| **H1** | — | `1.75rem` | `700` | Page titles |
| **H2** | — | `1.25rem` | `600` | Section headings |
| **H3** | — | `1.1rem` | `600` | Sub-section headings |
| **Body** | — | `0.95rem` | `400` | Paragraphs, table cells |
| **Small / Label** | — | `0.8rem` | `500` | `text-transform: uppercase; letter-spacing: 0.05em` |
| **Code / Mono** | `monospace` | `0.85rem` | `400` | Scores, IDs, timestamps |

---

## 4. Spacing & Layout

### 4.1 Spacing Scale

| Token | Value | Usage |
|-------|-------|-------|
| `--space-xs` | `0.25rem` (4px) | Tight gaps |
| `--space-sm` | `0.5rem` (8px) | Badge padding, compact gaps |
| `--space-md` | `1rem` (16px) | Standard element spacing |
| `--space-lg` | `1.5rem` (24px) | Card padding, section gaps |
| `--space-xl` | `2rem` (32px) | Page margin, major sections |

### 4.2 Layout Constraints

| Property | Value |
|----------|-------|
| Content max-width | `1100px`, centered |
| Content padding | `0 1rem` |
| Card padding | `1.5rem` |
| Card border-radius | `0.75rem` (12px) |
| Button / input radius | `0.4rem` (6px) |
| Form max-width | `36rem` (576px) |

### 4.3 Shadows

| Element | Shadow |
|---------|--------|
| Card (resting) | `0 10px 30px rgba(23, 32, 51, 0.08)` |
| Card (hover) | `0 14px 40px rgba(23, 32, 51, 0.12)` |
| Button (hover) | `0 4px 12px rgba(31, 94, 255, 0.3)` |

---

## 5. Component Specifications

### 5.1 App Shell / Layout

```
┌──────────────────────────────────────────────────────┐
│  HEADER — dark navy (#172033), full-width, sticky    │
│  ┌──────────────────────────────────────────────────┐ │
│  │ SRIM (logo)  │ Nav Links (role-specific) │ Logout│ │
│  └──────────────────────────────────────────────────┘ │
├──────────────────────────────────────────────────────┤
│  MAIN — soft grey bg (#f5f7fb), max-1100px centered  │
│  ┌──────────────────────────────────────────────────┐ │
│  │  Flash alert (success/error) if present          │ │
│  ├──────────────────────────────────────────────────┤ │
│  │  White card (.card) — content area               │ │
│  │  border-radius: 12px, shadow, padding 1.5rem     │ │
│  └──────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────┘
```

- **Header**: `background: var(--primary)`, white text, `padding: 1rem`
- **Nav**: Flexbox row, `gap: 1rem`, links/buttons white, no underline
- **Logout**: Pushed right with `margin-left: auto`, inline form

#### Role-Specific Navigation

| Role | Nav Links |
|------|-----------|
| HR Admin | Dashboard, Users, Requisitions |
| Candidate | Dashboard, My Profile, Open Jobs, My Applications |
| Interviewer | Dashboard, My Interviews |
| Guest | Login, Register |

### 5.2 Buttons

| Variant | Background | Text | Border | Use Case |
|---------|-----------|------|--------|----------|
| **Primary** | `var(--accent)` | `#fff` | none | Main actions (Save, Submit, Create) |
| **Secondary / Outlined** | transparent | `var(--accent)` | `1px solid var(--accent)` | Cancel, secondary actions |
| **Danger** | `var(--error)` | `#fff` | none | Delete, reject |
| **Ghost / Muted** | `#e2e8f0` | `var(--text)` | none | Draft, low-priority |
| **Success** | `var(--success)` | `#fff` | none | Accept, approve |

All buttons: `padding: 0.65rem 1rem`, `border-radius: 0.4rem`, `cursor: pointer`, `font: inherit`.
Hover: darken 10%, add `box-shadow: var(--shadow-button-hover)`.
Transition: `all 0.2s ease`.

### 5.3 Form Controls

- **Labels**: Above inputs, `font-weight: 500`, `margin-bottom: 0.25rem`
- **Inputs / Selects / Textareas**: Full width up to `max-width: 36rem`, `padding: 0.65rem`, `border: 1px solid var(--border)`, `border-radius: 0.4rem`, `margin-bottom: 1rem`
- **Textarea min-height**: `7rem`
- **Focus state**: `border-color: var(--accent)`, `box-shadow: 0 0 0 3px rgba(31, 94, 255, 0.15)`, `outline: none`
- **Validation errors**: Red text below the field, `color: var(--error)`, `font-size: 0.85rem`

### 5.4 Data Tables

- `width: 100%`, `border-collapse: collapse`
- **Header row**: `font-weight: 600`, `text-transform: uppercase`, `font-size: 0.8rem`, `color: var(--text-muted)`, `border-bottom: 2px solid var(--border)`
- **Body cells**: `padding: 0.75rem`, `border-bottom: 1px solid var(--border)`, `vertical-align: top`
- **Zebra striping**: `tr:nth-child(even) { background: #f8fafc }`
- **Hover rows**: `tr:hover { background: #f1f5f9 }`, `transition: background 0.15s`
- **Action column**: Right-aligned, contains link buttons

### 5.5 Status Badges

```css
.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;        /* full pill */
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;
}
```

Apply background/text colors from **Section 2.3** using modifier classes (`.badge-draft`, `.badge-pending`, etc.).

### 5.6 Alert / Flash Messages

```css
.alert {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    border-left: 4px solid;
}
.alert-success { background: var(--success-bg); color: #116033; border-color: var(--success); }
.alert-error   { background: var(--error-bg);   color: #8f1f1f; border-color: var(--error);   }
.alert-warning { background: var(--warning-bg); color: #92400e; border-color: var(--warning); }
.alert-info    { background: var(--info-bg);     color: #1e40af; border-color: var(--info);    }
```

### 5.7 Cards

- **Base**: `background: var(--card)`, `border-radius: 0.75rem`, `box-shadow: var(--shadow-card)`, `padding: 1.5rem`, `overflow-x: auto`
- **Hover (interactive cards only)**: `box-shadow: var(--shadow-card-hover)`, `transform: translateY(-2px)`, `transition: all 0.2s ease`

### 5.8 Dashboard Stat Cards

Grid of summary cards, each containing:
- Icon/emoji (left or top)
- Metric label (small, muted, uppercase)
- Metric value (large, bold, `font-size: 1.75rem`)
- Optional trend or sub-text

Layout: `display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem`

### 5.9 Progress / Score Indicators

- **Progress bar**: `height: 8px`, `border-radius: 4px`, `background: var(--border)`, fill with semantic color
- **Circular score**: Large number centered in a ring (CSS `conic-gradient` or SVG), used for assessment results and aggregate evaluation scores
- **Star ratings**: 1–5 filled/empty stars for interview feedback scores
- **Score bar (horizontal)**: Labeled bar showing score out of 5, colored by value (green ≥ 4, amber ≥ 3, red < 3)

### 5.10 Timeline / Stepper

Used for application status progression and offer audit trails:
- Horizontal stepper: circles connected by lines, active step filled with `var(--accent)`, completed steps in `var(--success)`, future steps in `var(--border)`
- Vertical timeline (audit): timestamp left, event description right, connected by a vertical line with dot markers

### 5.11 Empty States

Centered layout with:
- Muted icon or illustration
- Heading: "No [items] yet"
- Description text in `var(--text-muted)`
- Optional action button

### 5.12 Pagination

- Row of page number buttons, current page highlighted with `var(--accent)`
- Previous/Next arrow buttons
- `font-size: 0.85rem`, `gap: 0.25rem`

---

## 6. Page Templates

### 6.1 Template Types

| Template | Structure | Used For |
|----------|-----------|----------|
| **Auth (centered)** | No nav bar. Centered white card on gradient navy background. | Login, Register, Welcome |
| **Dashboard** | Nav + stat cards grid + quick-action buttons | HR/Candidate/Interviewer home |
| **List** | Nav + title bar with "Create" button + data table + pagination | All index/listing pages |
| **Detail / Show** | Nav + header with title + status badge + metadata grid + related sections + action buttons | Requisition, Application, Interview, Offer, Onboarding detail |
| **Form** | Nav + title + single-column form fields + action buttons | Create/Edit pages |
| **Assessment (focused)** | Minimal nav + timer bar + question + radio options | Candidate taking a timed assessment |
| **Error** | Nav + centered large error code + message + "Go Home" button | 403, 404 |

### 6.2 Screen Inventory

#### Auth (Public)
| # | Screen | Template |
|---|--------|----------|
| 1 | Welcome / Landing | Auth centered |
| 2 | Login | Auth centered |
| 3 | Candidate Registration | Auth centered |

#### HR Admin Portal
| # | Screen | Template |
|---|--------|----------|
| 4 | HR Dashboard | Dashboard |
| 5 | User Management | List |
| 6 | Create User | Form |
| 7 | Job Requisitions | List |
| 8 | Create/Edit Requisition | Form |
| 9 | Requisition Detail | Detail |
| 10 | Application Detail | Detail |
| 11 | Assessments List | List |
| 12 | Create/Edit Assessment | Form |
| 13 | Assessment Detail | Detail |
| 14 | Interviews List | List |
| 15 | Schedule Interview | Form |
| 16 | Interview Detail | Detail |
| 17 | Final Evaluation | Detail + Form hybrid |
| 18 | Offers List | List |
| 19 | Create/Edit Offer | Form |
| 20 | Offer Detail | Detail |
| 21 | Onboarding List | List |
| 22 | Create/Edit Onboarding | Form |
| 23 | Onboarding Detail | Detail |

#### Candidate Portal
| # | Screen | Template |
|---|--------|----------|
| 24 | Candidate Dashboard | Dashboard |
| 25 | My Profile | Form |
| 26 | Open Jobs (Browse) | List (card variant) |
| 27 | Job Detail & Apply | Detail |
| 28 | My Applications | List |
| 29 | Application Detail | Detail + Stepper |
| 30 | Take Assessment | Assessment focused |
| 31 | Assessment Results | Detail (score focus) |
| 32 | View Offer | Detail |

#### Interviewer Portal
| # | Screen | Template |
|---|--------|----------|
| 33 | Interviewer Dashboard | Dashboard |
| 34 | Assigned Interviews | List |
| 35 | Interview Detail & Feedback | Detail + Form hybrid |

#### Error Pages
| # | Screen | Template |
|---|--------|----------|
| 36 | 403 Forbidden | Error |
| 37 | 404 Not Found | Error |

---

## 7. User Flows

### Flow A: Public → Auth
```
Welcome → Login → Role-specific Dashboard
Welcome → Register → Login
```

### Flow B: HR Recruitment Pipeline
```
HR Dashboard → Requisitions → Create Requisition → Requisition Detail
Requisition Detail → Application Detail → Final Evaluation
Final Evaluation → Create Offer → Offer Detail → Create Onboarding
```

### Flow C: Candidate Journey
```
Candidate Dashboard → Browse Jobs → Job Detail → Apply
My Applications → Application Detail → Start Assessment → Results
Application Detail → View Offer → Accept / Decline
```

### Flow D: Interviewer Journey
```
Interviewer Dashboard → Assigned Interviews → Interview Detail → Submit Feedback
```

---

## 8. Responsive Behavior

| Breakpoint | Behavior |
|------------|----------|
| `≥ 1100px` | Full desktop layout, content centered at max-width |
| `768px – 1099px` | Content fills width with padding, tables scroll horizontally |
| `< 768px` | Nav collapses to hamburger menu, stat cards stack to 1 column, forms go full width |

---

## 9. Interaction & Animation

| Interaction | Specification |
|-------------|---------------|
| **Button hover** | Darken 10%, add shadow, `transition: all 0.2s ease` |
| **Card hover** (interactive) | Lift `translateY(-2px)`, deepen shadow, `transition: all 0.2s ease` |
| **Table row hover** | Background shift to `#f1f5f9`, `transition: background 0.15s` |
| **Input focus** | Blue border + blue glow ring `0 0 0 3px rgba(31,94,255,0.15)` |
| **Badge** | No animation, static pill |
| **Page transitions** | Content area `opacity 0→1`, `transform translateY(8px→0)`, `0.3s ease` |
| **Timer (assessment)** | Pulse animation when < 5 minutes remaining, red color shift |
| **Alert dismiss** | Fade out + slide up on close, `0.3s ease` |

---

## 10. Accessibility

| Requirement | Implementation |
|-------------|----------------|
| Color contrast | All text meets WCAG AA (4.5:1 ratio minimum) |
| Focus indicators | Visible focus ring on all interactive elements |
| Semantic HTML | `<header>`, `<nav>`, `<main>`, `<section>`, `<table>`, `<form>` |
| ARIA labels | `aria-label` on nav, icon-only buttons, status badges |
| Keyboard navigation | All actions reachable via Tab/Enter/Space |
| Form labels | Every input has an associated `<label>` |

---

## 11. File Structure

```
css/
└── app.css              ← Full design system stylesheet (CSS custom properties)

views/
├── layouts/
│   └── app.php          ← Shared shell: <html>, <head>, header/nav, main, flash alerts
├── auth/                ← Login, register (no nav bar variant)
├── hr/                  ← All HR Admin pages
├── candidate/           ← All Candidate pages
├── interviewer/         ← All Interviewer pages
└── errors/              ← 403, 404
```

The layout shell (`views/layouts/app.php`) includes the CSS file and renders the shared header, navigation, alerts, and content card wrapper. Individual page templates render only their inner content.
