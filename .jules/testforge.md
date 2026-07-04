# TestForge Journal - Critical Learnings

## 2026-07-03 - [SyncUserLevel Mass Assignment Bug]
**Issue:** The `SyncUserLevel` listener updated the user's level using `$user->update(['level_id' => $newLevel->id])`. However, `level_id` was not defined in the `User` model's `$fillable` array. As a result, Eloquent silently ignored the update, and the user's level never actually changed.
**Learning:** This bug was completely silent in the application (no exception was thrown) and went unnoticed because there were no unit tests verifying the side-effects of listeners.
**Prevention:** Always write dedicated unit tests for listeners that mutate state, and verify actual database changes (using `assertDatabaseHas` or asserting against a fresh model instance) instead of asserting that a method was called. Use direct assignment `$user->level_id = ...; $user->save();` or `forceFill()` to safely update guarded columns in system listeners.
