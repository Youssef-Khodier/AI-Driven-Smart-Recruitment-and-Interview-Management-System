# Phase 0: Outline & Research

## Decision 1: UI Integration Strategy
- **Decision**: Directly translate the static HTML files provided in `stitch_screens/` into Vanilla PHP views in the `views/` directory (e.g., `views/layouts/app.php` and specific role views).
- **Rationale**: The project constitution mandates a Vanilla PHP monolithic MVC without adding REST APIs or separated frontend apps. The views are already server-rendered.
- **Alternatives considered**: Using an external frontend setup with an API, which violates the constitution.

## Decision 2: Tailwind CSS Loading
- **Decision**: Use the CDN setup found in the static HTML files (e.g., `<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>`) or a compiled CSS file for the academic scope, injecting into `views/layouts/app.php`.
- **Rationale**: The Tailwind CSS Amendment permits Tailwind for the UI. Using CDN is straightforward and aligns with the generated HTML without needing a build step for the monolithic academic project.
- **Alternatives considered**: Setting up Node/NPM to compile Tailwind locally, which adds unnecessary build complexity for an academic demo currently relying on a CDN.

## Decision 3: Preserving Vanilla PHP MVC Routing
- **Decision**: Update existing controllers and views rather than creating new routes, as this is a UI polish phase and NOT a new workflow phase.
- **Rationale**: Functional Requirement FR-008 mandates preserving existing recruitment workflow outcomes and FR-012 prevents adding new machine-facing workflows.
- **Alternatives considered**: Rewriting routes, which is unnecessary and risky for a purely visual phase.