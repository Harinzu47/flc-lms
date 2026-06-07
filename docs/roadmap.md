# Refactoring Roadmap

This roadmap structures the necessary refactoring tasks to transition the FLC UMJ Gamified LMS from a single-class prototype to a production-ready enterprise system.

---

## Roadmap Summary Table

| Phase | Task | Effort | Impact | Priority |
| --- | --- | --- | --- | --- |
| **Quick Wins** | Prevent RCE by validating mime-types and file extensions on task uploads. | Low | High | **High** |
| **Quick Wins** | Fix N+1 queries in the Hall of Fame user level lookup. | Low | High | **High** |
| **Quick Wins** | Add composite unique index to `submissions` to prevent concurrent double-submits. | Low | High | **High** |
| **Quick Wins** | Add index to `xp_logs.reference_id` to speed up claim checks. | Low | Medium | **High** |
| **Medium-Term** | Decouple XP adjustments and level evaluations using Laravel Events/Listeners. | Medium | High | **High** |
| **Medium-Term** | Run gamification updates asynchronously using Redis Queues. | Medium | High | **High** |
| **Medium-Term** | Implement Spatie Laravel Permission for granular RBAC (SuperAdmin, Lecturer, Grader). | Medium | High | **High** |
| **Medium-Term** | Setup GitHub Actions CI for linting, testing, and Docker builds. | Medium | Medium | **Medium** |
| **Major Refactor** | Switch storage driver to AWS S3 or MinIO. | Medium | High | **High** |
| **Major Refactor** | Build REST API (v1) secured by Laravel Sanctum. | High | High | **High** |
| **Major Refactor** | Cache Leaderboards in Redis Sorted Sets (`ZSET`). | Medium | High | **Medium-High** |
| **Major Refactor** | Add full-text material catalog search via Laravel Scout & Meilisearch. | Medium | Medium | **Medium** |
| **Enterprise Sync** | Integrate Campus SSO (OIDC/SAML2) for unified logins. | High | High | **High** |
| **Enterprise Sync** | Build database-level Multi-Tenancy for university-wide scaling. | High | Medium | **Low-Medium** |

---

## Detailed Execution Phases

### 1. Quick Wins (1 - 2 Weeks)
* **Goal:** Eliminate immediate security risks, database integrity threats, and obvious performance issues.
* **Actions:**
  1. Update [TaskShow.php](file:///d:/LMS%20FLC/flc-lms/app/Livewire/TaskShow.php) validation rules to enforce file extensions (`pdf,zip,doc,docx`).
  2. Implement a Laravel database migration to add unique composite keys (`user_id`, `task_id`) on `submissions`.
  3. Optimize the Leaderboard controller query to join `levels` or map levels dynamically, eliminating N+1 queries.

### 2. Medium Improvements (1 - 3 Months)
* **Goal:** Transition from a synchronous monolith to an event-driven system and build proper developer guardrails.
* **Actions:**
  1. Define `MaterialRead`, `TaskSubmitted`, and `SubmissionGraded` event classes.
  2. Map events to queueable Listeners (`AwardXP`, `VerifyLevelUp`, `UnlockBadges`) to offload requests from the web thread.
  3. Configure Docker supervisord to run background queue workers.
  4. Replace binary role checking with a robust middleware checking roles/permissions dynamically.

### 3. Major Refactoring (3 - 6 Months)
* **Goal:** Enable horizontal scaling, API extensibility, and optimized caching.
* **Actions:**
  1. Update file upload logic to use the cloud `s3` storage disk instead of direct local path storage.
  2. Implement a complete versioned API (`/api/v1/`) with structured JSON formatting.
  3. Integrate Redis Sorted Sets for the Leaderboard. When XP changes, update Redis scores asynchronously, and query Redis for Top-50 lists.
  4. Implement an audit logging library to record lecturers' grading events.

### 4. Enterprise Transformation (6 - 12 Months)
* **Goal:** Deploy as a high-availability, fully integrated university campus platform.
* **Actions:**
  1. Connect the system’s authentication layer to the university SSO portal (Active Directory / OIDC).
  2. Scale deployments on a container orchestration platform (Kubernetes) with automatic scaling policies.
  3. Support Multi-Tenancy schema boundaries to allow other faculties within the university to run isolated LMS setups.
