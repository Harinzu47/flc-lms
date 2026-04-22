# Project Context & Architecture Guidelines: FLC UMJ Gamified LMS

## 1. Project Overview
This project is a Web-Based Learning Management System (LMS) with an integrated Gamification Engine, built for a university community (FLC UMJ). The primary goal is to increase user engagement through points, badges, and leaderboards (PBL). 

## 2. Tech Stack & Environment
**Strictly adhere to the following stack:**
* **Backend:** Laravel (Latest stable version).
* **Frontend:** TALL Stack (Tailwind CSS, Alpine.js, Laravel Livewire). DO NOT use React, Vue, or Inertia.js.
* **Database:** MySQL.
* **Development Environment:** Laravel Sail (Docker). All configurations must assume a containerized environment to ensure complete isolation and prevent conflicts with existing setups on the host machine used for daily work.
* **File Storage:** Local Storage (symlink to `public` directory).

## 3. Architectural Patterns

### A. MVC (Model-View-Controller)
* **Controllers:** Must remain thin. Controllers should only handle HTTP requests, input validation (via Form Requests), and returning Views or Livewire components.
* **Models:** Contain relationships, scopes, and basic accessors/mutators.
* **Views:** Use Blade templates heavily integrated with Tailwind CSS and Livewire.

### B. Event-Driven Architecture (Crucial for Gamification)
The Gamification logic MUST be completely decoupled from the core LMS logic.
* **Rule:** Never calculate XP, check level-ups, or unlock badges directly inside a Controller or a Livewire component.
* **Implementation:** 1. Dispatch an Event (e.g., `TaskCompleted`, `MaterialRead`).
    2. Create Listeners to handle the Event in the background (e.g., `AwardXP`, `CheckBadgeUnlock`, `CheckLevelUp`).
* **Benefit:** This ensures the primary user action (e.g., submitting a task) responds immediately without waiting for complex gamification calculations.

## 4. Frontend Guidelines (TALL Stack)
* **Livewire:** Use for server-side interactions without page reloads (e.g., submitting forms, dynamic pagination, refreshing the Leaderboard via `wire:poll`).
* **Alpine.js:** Use strictly for lightweight, client-side UI toggles where server interaction is unnecessary (e.g., dropdowns, modals, tabs, flash message auto-hide using `x-data`, `x-show`, `x-on`).
* **Tailwind CSS:** Do not write custom CSS unless absolutely necessary. Rely on utility classes.

## 5. Database Schema Blueprint (Reference)
When generating migrations or models, follow this structural separation:

**Core LMS Entities:**
* `users`: id, name, email, password, role (admin/member), total_xp, level_id.
* `materials`: id, title, description, file_url, created_at.
* `tasks`: id, title, type (enum), deadline, base_xp.
* `submissions`: id, task_id, user_id, answer/file, score, status.

**Gamification Entities:**
* `levels`: id, name, min_xp.
* `badges`: id, name, icon_url, description, criteria_type, criteria_value.
* `user_badges`: id, user_id, badge_id, unlocked_at.
* `xp_logs`: id, user_id, action, xp_earned, created_at. (Do not recalculate XP on the fly; read from `users.total_xp`. Use this table only for audit trails).

## 6. Prompting Instructions for AI Assistant
* **Consistency:** Before generating code, review the Tech Stack and Architectural Patterns.
* **Code Style:** Follow PSR-12 coding standards. Use strict typing in PHP where possible.
* **Comments:** Provide clear docblocks for Events and Listeners, as these will be referenced in academic documentation (thesis).
* **Refactoring:** If you output a Controller that handles gamification logic, immediately refactor it into an Event/Listener structure.