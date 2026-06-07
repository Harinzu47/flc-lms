# Enterprise Gap Analysis

This document identifies the technical and functional gaps that prevent the current FLC UMJ Gamified LMS from operating in a large-scale, high-concurrency enterprise environment.

---

## Gap Matrix

| Feature Domain | Current Implementation | Enterprise Requirement | Impact of Gap | Priority |
| --- | --- | --- | --- | --- |
| **Authentication** | Basic Breeze Session Login (Email/Password) | Single Sign-On (SSO) with OIDC, SAML2, or Active Directory (UMJ Campus SSO) | Users must manage multiple credentials; higher IT security administration. | **High** |
| **Authorization** | Binary checking: `role === 'admin'` or `'member'` | Role-Based Access Control (RBAC) with granular permissions | Lecturers can accidentally delete tasks; unable to assign grader-only permissions. | **High** |
| **Audit Trail** | XP log append-only table (`xp_logs`) | System-wide Audit logging of administrative activities | Lack of compliance tracking; unable to see who deleted tasks or changed grades. | **Medium** |
| **Asynchronous Jobs** | Synchronous actions inside request cycles | Background Queue processing (backed by Redis/SQS) | Page loads are blocked by gamification checks; potential timeouts under load. | **High** |
| **Caching Layer** | Direct database Eloquent queries | Distributed cache (Redis) for user data and Leaderboard rankings | MySQL becomes a performance bottleneck under concurrent traffic. | **High** |
| **Notification Center** | In-app Livewire toast notifications only | Multi-channel (Email, Slack, Push) message broker | Students miss deadlines; no alerts when grading completes. | **Medium** |
| **Search Engine** | Basic SQL `LIKE` queries | Full-text Search Engine (Laravel Scout with Meilisearch/Elasticsearch) | Hard to navigate learning materials and course catalogues as data grows. | **Low** |
| **Multi-Tenancy** | Single database, single university tenancy | Multi-Tenant Architecture (database-per-tenant or tenant-id columns) | Unable to host the LMS for other faculties or universities on a single deploy. | **Low** |
| **Integration Webhooks** | None | Event Bus (Kafka/RabbitMQ) or outgoing REST Webhooks | Unable to trigger external services (e.g. sync student records with campus ERP). | **Low-Medium** |

---

## Detailed Gap Analysis & Solutions

### 1. Identity & Access Management (SSO & MFA)
* **Gap:** University environments typically require central identity control. Relying on database-driven username/password registration increases credential sprawl.
* **Solution:** Integrate **Laravel Socialite** or **laravel-saml2** to delegate authentication to the university’s Microsoft Active Directory (Azure AD), Google Workspace, or central LDAP server. Enforce Multi-Factor Authentication (MFA) for administrative accounts.

### 2. Fine-Grained Authorization (RBAC)
* **Gap:** Currently, a user is either a student (`member`) or an administrator (`admin`). Lecturers, teaching assistants, and administrative staff share the same binary access controls.
* **Solution:** Integrate the `spatie/laravel-permission` package. Define roles: `SuperAdmin`, `Lecturer`, `TeachingAssistant`, `Student`. Define explicit permissions: `create-tasks`, `grade-submissions`, `manage-materials`, `view-analytics`.

### 3. Background Job Queueing
* **Gap:** Database mutations inside Actions occur in the web request thread. If a database query blocks or a mail notification is added, request latency will spike.
* **Solution:** Change `QUEUE_CONNECTION` in `.env` from `sync` to `redis`. Refactor gamification engines to dispatch events implementing the `ShouldQueue` interface.

### 4. Administrative Audit Trails
* **Gap:** There is no record of who modified task details, who edited material links, or when a grade was altered.
* **Solution:** Implement a package like `spatie/laravel-activitylog` to automatically record database inserts, updates, and deletes with metadata (IP, user agent, performing user, old vs new values).

### 5. Leaderboard Performance Caching
* **Gap:** The leaderboard ranks the top 50 users using query-level joins and counts every page load.
* **Solution:** Implement Redis Sorted Sets (`ZSET`). When a student earns XP, update their score in Redis using `ZINCRBY`. Fetch the leaderboard directly from memory in $O(\log N)$ time, bypassing SQL execution.
