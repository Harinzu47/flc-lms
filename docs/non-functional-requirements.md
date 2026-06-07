# Non-Functional Requirements Analysis

## 1. Performance

### Query Efficiency & N+1 Problems
* **Leaderboard Query:** The Hall of Fame queries the top 50 users sorted by total XP.
  * **Evidence:** [HallOfFame.php:L38-42](file:///d:/LMS%20FLC/flc-lms/app/Livewire/HallOfFame.php#L38-42)
  * **Issue:** In the corresponding Blade view, each user's current level is evaluated by calling `$user->currentLevel()`. This triggers a database check on `levels` for every single loop iteration. For a list of 50 users, this executes 50 additional database queries (N+1 query problem).
  * **Resolution:** Eager-load level information by building a cached map of levels in memory or updating the query to join the levels table.

### Caching Strategy
* **Current State:** Completely missing. No cache driver is utilized to store complex query results.
* **Impact:** High server CPU and database disk read usage under heavy concurrent requests, particularly on resource-intensive pages like the Leaderboard.
* **Resolution:** Implement Redis or Memcached. Cache the leaderboard list for 5–10 minutes instead of querying MySQL on every refresh.

### File Storage Infrastructure
* **Current State:** Stores uploads in local file systems.
  * **Evidence:** [SubmitTaskAction.php:L48](file:///d:/LMS%20FLC/flc-lms/app/Actions/LMS/SubmitTaskAction.php#L48)
* **Impact:** Local file storage is a major performance and scaling barrier. It consumes web server disk space and slows down response times for large file uploads.
* **Resolution:** Integrate Amazon S3 or MinIO as the cloud file storage driver (`filesystems.disks.s3`), offloading file handling from the web server.

---

## 2. Scalability

### Horizontal Scaling Readiness (Statelessness)
* **Bottleneck 1 (Sessions):** The project uses Laravel's default session configuration (likely `file` driver). In a horizontal cluster, if a user's session lands on Server A, they will be logged out if their next request hits Server B.
* **Bottleneck 2 (File Uploads):** Local storage makes attachments unreadable across multiple servers.
* **Resolution:** 
  1. Change session driver to `database` or `redis`.
  2. Implement a cloud-based storage system (S3) for all files.

### Queue & Event-Driven Readiness
* **Current State:** Synchronous operations only. The queue table is migrated but never used.
* **Bottleneck:** Awarding XP, updating user total score, and logging transaction tables happen synchronously in the HTTP request threat. If the app needs to send confirmation emails or process third-party webhooks, pages will freeze.
* **Resolution:** Move gamification updates and notifications to Laravel Queues backed by Redis.

---

## 3. Availability & Disaster Recovery
* **Database Replication:** The application relies on a single MySQL instance. There is no read/write splitting configuration in `config/database.php`.
* **Backup Strategy:** No automated backup or snapshot system exists in the code or environment setup.
* **Resolution:** Setup daily automated database backups (using tools like `spatie/laravel-backup`) and deploy a replica database for failover capability.

---

## 4. Reliability

### Error Handling
* **Current State:** Localized try/catch blocks only (e.g., [TaskShow.php:L86-100](file:///d:/LMS%20FLC/flc-lms/app/Livewire/TaskShow.php#L86-100)).
* **Issue:** Uncaught exceptions bubble up to the default Laravel error screen, leaking system stack traces to users in non-production environments.
* **Resolution:** Register a global exception handler in `bootstrap/app.php` to log details safely and render friendly error messages to students.

### Logging & Observability
* **Current State:** Default Laravel file logging (`storage/logs/laravel.log`).
* **Issue:** Single-file logging is hard to aggregate, search, or monitor in production.
* **Resolution:** Direct logs to standard output (`stdout`) for container aggregation, and integrate an observability platform (e.g., Sentry, Bugsnag, or Datadog) for error alerting.

---

## 5. Maintainability

### Architectural Compliance
* **Structure:** The separation of controllers, Livewire components, Actions, and Views is clean and easy to follow.
* **SOLID Compliance:**
  * *Single Responsibility Principle (SRP):* Strongly adhered to via Action classes.
  * *Open/Closed Principle (OCP):* Violated. For example, adding new submission tasks require editing hardcoded conditions in [TaskShow.php](file:///d:/LMS%20FLC/flc-lms/app/Livewire/TaskShow.php).
* **Resolution:** Extract dynamic task and material types into polymorphic components to decouple validation rules from core files.
