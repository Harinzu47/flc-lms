# Database Schema & Eloquent Relationships: FLC Gamified LMS

## Overview
This document defines the strict database schema, data types, and Eloquent model relationships for the LMS. All Laravel migrations and models generated must strictly adhere to this structure. Do not invent new columns or tables without explicit instructions.

## 1. Core LMS Entities

### Table: `users`
* **Columns:**
  * `id` (bigint, unsigned, auto-increment, primary key)
  * `name` (string)
  * `email` (string, unique)
  * `password` (string)
  * `role` (enum: 'admin', 'member') - Default: 'member'
  * `level_id` (foreignId, nullable, constrained to `levels`) - Default: null (assigned on first login/action)
  * `total_xp` (integer, unsigned) - Default: 0
  * `remember_token` (string, nullable)
  * `created_at`, `updated_at` (timestamps)
* **Relationships:**
  * `belongsTo(Level::class)`
  * `hasMany(Submission::class)`
  * `hasMany(XpLog::class)`
  * `belongsToMany(Badge::class, 'user_badges')`

### Table: `materials`
* **Columns:**
  * `id` (bigint, unsigned, auto-increment, primary key)
  * `title` (string)
  * `description` (text, nullable)
  * `file_url` (string, nullable) - Path to local storage or external link
  * `type` (enum: 'document', 'video', 'link') - Default: 'document'
  * `created_at`, `updated_at` (timestamps)

### Table: `tasks`
* **Columns:**
  * `id` (bigint, unsigned, auto-increment, primary key)
  * `title` (string)
  * `description` (text)
  * `type` (enum: 'essay', 'file_upload', 'quiz') - Default: 'essay'
  * `base_xp` (integer, unsigned) - The maximum XP a user can earn for this task
  * `deadline` (datetime, nullable)
  * `created_at`, `updated_at` (timestamps)
* **Relationships:**
  * `hasMany(Submission::class)`

### Table: `submissions`
* **Columns:**
  * `id` (bigint, unsigned, auto-increment, primary key)
  * `task_id` (foreignId, constrained, cascadeOnDelete)
  * `user_id` (foreignId, constrained, cascadeOnDelete)
  * `answer_text` (text, nullable) - Used if task type is 'essay'
  * `file_url` (string, nullable) - Used if task type is 'file_upload'
  * `score` (integer, unsigned, nullable) - Admin graded score (0-100)
  * `status` (enum: 'pending', 'graded') - Default: 'pending'
  * `created_at`, `updated_at` (timestamps)
* **Relationships:**
  * `belongsTo(Task::class)`
  * `belongsTo(User::class)`


## 2. Gamification Entities (The Core Engine)

### Table: `levels`
* **Columns:**
  * `id` (bigint, unsigned, auto-increment, primary key)
  * `name` (string) - e.g., 'Beginner', 'Intermediate', 'Polyglot'
  * `min_xp` (integer, unsigned) - The minimum XP required to reach this level
  * `icon_url` (string, nullable)
  * `created_at`, `updated_at` (timestamps)
* **Relationships:**
  * `hasMany(User::class)`

### Table: `badges`
* **Columns:**
  * `id` (bigint, unsigned, auto-increment, primary key)
  * `name` (string) - e.g., 'First Blood', 'Task Master'
  * `description` (text)
  * `icon_url` (string)
  * `criteria_type` (string) - Defines what triggers the badge (e.g., 'task_completed', 'material_read')
  * `criteria_value` (integer, unsigned) - Defines the threshold (e.g., 10 tasks)
  * `created_at`, `updated_at` (timestamps)
* **Relationships:**
  * `belongsToMany(User::class, 'user_badges')`

### Table: `user_badges` (Pivot Table)
* **Columns:**
  * `id` (bigint, unsigned, auto-increment, primary key)
  * `user_id` (foreignId, constrained, cascadeOnDelete)
  * `badge_id` (foreignId, constrained, cascadeOnDelete)
  * `unlocked_at` (timestamp)
* **Note:** Use `created_at` as `unlocked_at` if omitting the custom column.

### Table: `xp_logs`
* **Description:** An audit trail for all XP transactions. Never update existing records; only append.
* **Columns:**
  * `id` (bigint, unsigned, auto-increment, primary key)
  * `user_id` (foreignId, constrained, cascadeOnDelete)
  * `action` (string) - e.g., 'task_submitted', 'material_read', 'daily_login'
  * `xp_earned` (integer) - Can be negative if penalizing, but typically positive.
  * `reference_id` (bigint, unsigned, nullable) - Polymorphic reference (e.g., Task ID or Material ID) to prevent duplicate rewards.
  * `created_at`, `updated_at` (timestamps)
* **Relationships:**
  * `belongsTo(User::class)`

## 3. Indexing & Performance Rules
* Always index foreign keys (`user_id`, `task_id`, `badge_id`, `level_id`).
* Index the `total_xp` column in the `users` table as it will be heavily queried for sorting the Leaderboard.
* The `email` column in the `users` table must be unique and indexed.