# Source Code Quality Review

## Scoring Matrix

| Category | Score | Notes |
| --- | --- | --- |
| **Readability** | 9 / 10 | High standard of PHP strict typing, PSR-12 formatting, and well-written docblocks. |
| **Maintainability** | 7 / 10 | Actions split business logic from controllers, but synchronous execution reduces robustness. |
| **Modularity** | 6 / 10 | Lack of domain boundaries and decoupled event-driven queues mixes components. |
| **Reusability** | 7 / 10 | Action classes are highly reusable; however, Livewire CRUD controllers share redundant boilerplate. |

---

## Detailed Code Quality Analysis

### 1. Code Style and Conventions
* **Strict Typing:** All custom classes use `declare(strict_types=1);` and detailed parameter/return type declarations. This enforces runtime safety and makes static analysis highly effective.
* **Modern PHP Features:** The code leverages modern PHP features such as `match` expressions, promotion properties, and constructor injection.
* **Naming Conventions:** Class and variable names are highly descriptive, following Standard PHP (PSR-12) formatting rules (PascalCase for classes, camelCase for methods/variables).

---

## Code Smells & Anti-Patterns (Temuan)

### 1. Boilerplate Duplication in CRUD Components
* **Evidence:** [MaterialManager.php:L75-94](file:///d:/LMS%20FLC/flc-lms/app/Livewire/Admin/MaterialManager.php#L75-94) vs [TaskManager.php:L70-91](file:///d:/LMS%20FLC/flc-lms/app/Livewire/Admin/TaskManager.php#L70-91)
* **Description:** The open, edit, delete, and close-modal methods in `MaterialManager` and `TaskManager` share the exact same logical steps, state resets, and page navigation controls.
* **Impact:** High maintenance overhead. If a new modal library or transition state is introduced, changes must be applied in multiple places.
* **Severity:** **Low-Medium**
* **Recommendation:** Create a reusable Livewire trait `HasFormModal` or abstract class to consolidate modal state toggles and form-cleaning logic.
* **Example Implementation:**
  ```php
  trait InteractsWithFormModal
  {
      public bool $isModalOpen = false;
      public ?int $editId = null;

      public function closeModal(): void
      {
          $this->isModalOpen = false;
          $this->resetForm();
      }
  }
  ```

### 2. Transaction Contention (Locking Risk)
* **Evidence:** [GradeSubmissionAction.php:L31-49](file:///d:/LMS%20FLC/flc-lms/app/Actions/LMS/GradeSubmissionAction.php#L31-49)
  ```php
  DB::transaction(function () use ($submission, $score, $earnedXp): void {
      $submission->update([...]);
      XpLog::create([...]);
      $submission->user->increment('total_xp', $earnedXp);
  });
  ```
* **Description:** This synchronous transaction updates three tables (`submissions`, `xp_logs`, and `users`). During exam periods when multiple grading actions are saved concurrently by instructors, updating the shared `users` row will lock the specific student's record. If a student is attempting to submit another task at the exact same time, their thread will block waiting for the database locks, leading to SQL lock timeout errors.
* **Impact:** System hangs or failed requests during high-concurrency grading events.
* **Severity:** **Medium**
* **Recommendation:** Separate the primary data record updates (`submissions.score = graded`) from the asynchronous reward calculations. Discharging an async event allows the HTTP request to finish immediately.

### 3. Static Level Queries (N+1 Query Pattern)
* **Evidence:** [User.php:L106-112](file:///d:/LMS%20FLC/flc-lms/app/Models/User.php#L106-112)
  ```php
  public function currentLevel(): ?Level
  {
      return Level::query()
          ->where('min_xp', '<=', $this->total_xp)
          ->orderByDesc('min_xp')
          ->first();
  }
  ```
* **Description:** Because `users.level_id` is never automatically set or synced during user XP changes, the user model queries the database on every check to determine their rank. In lists (such as the Leaderboard page), accessing `$user->currentLevel()` executes a separate SQL query for each student, generating 50 queries for 50 rows.
* **Impact:** Performance degradation on public feeds.
* **Severity:** **High**
* **Recommendation:** Eager-load or cache the levels array in memory once, and map values client-side rather than executing SQL queries on every level evaluation.
