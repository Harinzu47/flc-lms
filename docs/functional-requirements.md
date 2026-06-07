# Functional Requirements Analysis

## Feature Checklist Matrix

| Module | Feature | Description | Status |
| ------ | ------- | ----------- | ------ |
| **Authentication** | User Login | Custom split-screen interface using Laravel Breeze. | Complete |
| **Authentication** | User Registration | Default sign-up screen for student and admin registration. | Complete |
| **Authentication** | Profile Management | Edit name, email, update password, and delete account. | Complete |
| **Student Portal** | Gamified Dashboard | High-fidelity interface displaying user profile, level progress bar, recent XP logs, upcoming tasks, and a mini leaderboard. | Complete |
| **Student Portal** | Material Details | Document, video, or link viewer which grants `+10 XP` on the first read action. | Complete |
| **Student Portal** | Task Submission | Panel for essay writing and zip file uploads, restricted to one submission per student per task. | Complete |
| **Student Portal** | Hall of Fame | Leaderboard showing the top 50 student rankings and highlighting the active user's position. | Complete |
| **Admin Portal** | Grading Station | Sidebar list of pending submissions to assign scores (0-100) and award calculated XP. | Complete |
| **Admin Portal** | Material Manager | Paginated CRUD manager for learning materials with pop-up modal interfaces. | Complete |
| **Admin Portal** | Task Manager | Paginated CRUD manager for coursework assignments with deadline and XP settings. | Complete |
| **Gamification** | Level System | Dynamic Level computation based on total XP (via `currentLevel()` and `nextLevel()`). | Partial |
| **Gamification** | Badge System | Pivot table relationships for student badge unlocking. | Incomplete (No trigger) |
| **Gamification** | XP Logs | Audit trail showing the transaction history of earned XP. | Complete |

---

## Detailed Functional Analysis

### 1. Existing Features (Fitur yang Sudah Ada)
* **Student Dashboard Coordination:** The student view ([gamified-dashboard.blade.php](file:///d:/LMS%20FLC/flc-lms/resources/views/livewire/gamified-dashboard.blade.php)) acts as a clean orchestrator, dividing concerns into profile headers, badges grids, and recent activity logs.
* **Proportional XP Grading:** The Admin Grading Station successfully implements proportional XP allocation. A student receives XP based on `(score / 100) * base_xp`, which rewards qualitative performance instead of a binary pass/fail.
* **Idempotency Safeguards:** In `AwardMaterialXpAction.php`, a guard ensures that students cannot repeatedly trigger XP awards by reading the same material multiple times. This prevents exploitation of gamified rewards.

### 2. Incomplete Features (Fitur yang Tidak Lengkap)
* **Dynamic Level-Up Storage:** While [User.php](file:///d:/LMS%20FLC/flc-lms/app/Models/User.php) contains helper methods to calculate levels (`currentLevel()`, `nextLevel()`), the database column `users.level_id` is never automatically updated in the backend. When a user levels up, their `level_id` foreign key remains unchanged, forcing the app to run on-the-fly SQL queries inside `currentLevel()` to get the correct tier.
* **Badge Unlocking Engine:** The codebase defines a `user_badges` table, but there is no engine, service, or event listener that evaluates student activity (e.g., number of materials read or tasks submitted) to unlock badges. The grid in `badges-grid.blade.php` will always render empty for students.

### 3. Unimplemented Features (Fitur yang Belum Memiliki Implementasi)
* **Quiz Core Engine:** The `tasks` table includes a `quiz` enum type (`type: 'essay', 'file_upload', 'quiz'`), but there is no router, controller, model, or UI view to support quiz taking or automatic quiz evaluation.
* **Email / Notification System:** The project includes Laravel's `Notifiable` trait in `User.php`, but no notification classes or mail channels are configured to alert students about graded tasks.

### 4. Redundant Features (Fitur yang Redundan)
* **Double-Handling of User Level Data:** The database schema tracks user progress in two ways:
  1. An on-the-fly relation query: `currentLevel()` queries `Level::where('min_xp', '<=', $this->total_xp)`.
  2. A persistent foreign key: `level_id` on the `users` table.
  Having both is redundant and invites consistency bugs if `level_id` is not continuously synced with the actual level boundaries.

### 5. Technical Debt (Fitur yang Berpotensi Menjadi Technical Debt)
* **Eager-loading Levels in Leaderboard:** In `HallOfFame.php`, the leaderboard displays the top 50 users. If the template evaluates `currentLevel()` for each row, it triggers an N+1 query problem, making 50 individual queries to the `levels` table. Eager loading is not configured for these dynamic virtual relations.
* **Local Storage Dependency:** In `SubmitTaskAction.php`, uploaded files are saved directly to the local directory:
  ```php
  $fileUrl = $file->store('submissions', 'public');
  ```
  This is a serious bottleneck for enterprise horizontal scaling. If the application is deployed behind a load balancer on multiple web servers, a student's file uploaded on Server A will not be accessible to an admin viewing the Grading Station on Server B.
