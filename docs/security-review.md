# Security Assessment (OWASP Top 10)

## Security Finding Matrix

| Finding | Severity | Description | Recommendation |
| --- | --- | --- | --- |
| **A01:2021-RCE via Unrestricted File Upload** | **CRITICAL** | Submissions accept any file type (e.g. `.php`) stored directly in a public directory, allowing remote code execution. | Validate mime-types and file extensions. Do not execute scripts in public storage. |
| **A01:2021-Binary RBAC** | **Medium** | System relies on binary check (`role === 'admin'`) without granular permissions or policy controls. | Implement Laravel Policies and spatie/laravel-permission for granular RBAC. |
| **A07:2021-No Multi-Factor Authentication** | **Low-Medium** | Login system does not support MFA for administrative accounts. | Integrate Breeze MFA or Laravel Fortify for administrative two-factor login. |
| **A09:2021-Lack of Database Level Unique Guard** | **Low** | Absence of composite unique key on submissions can lead to duplicate entries via race conditions. | Add a unique composite index to `submissions` on `(user_id, task_id)`. |

---

## Detailed Security Analysis

### 1. Unrestricted File Upload (Remote Code Execution - RCE)

* **Evidence:** [TaskShow.php:L77-80](file:///d:/LMS%20FLC/flc-lms/app/Livewire/TaskShow.php#L77-80)
  ```php
  'file_upload' => [
      'uploadedFile' => ['required', 'file', 'max:2048'], // 2 MB
  ],
  ```
* **Impact:** The Livewire validation rule only checks for `'file'` and `'max:2048'`. It does not restrict file extensions or mime-types. A student can upload a malicious PHP file (`shell.php`) as their submission. The file is saved directly inside the local public storage path via `SubmitTaskAction.php`:
  ```php
  $fileUrl = $file->store('submissions', 'public');
  ```
  Since the public folder is symlinked to the web directory, a student can browse to `http://lms-url/storage/submissions/shell.php` and execute arbitrary bash/OS commands directly on the server, compromising the database and hosting environment.
* **Severity:** **CRITICAL**
* **Recommendation:** Restrict accepted file uploads to secure formats (e.g. `pdf, zip, rar, docx`) and validate mime-types explicitly. Disable executable script execution in Nginx/Apache configuration for the `/storage` directory.
* **Example Implementation:**
  ```php
  'uploadedFile' => [
      'required',
      'file',
      'mimes:pdf,zip,rar,doc,docx', // Limit to safe formats
      'max:2048'
  ]
  ```

---

### 2. Broken Access Control (RBAC Integrity)

* **Evidence:** [EnsureUserIsAdmin.php:L21-28](file:///d:/LMS%20FLC/flc-lms/app/Http/Middleware/EnsureUserIsAdmin.php#L21-28)
  ```php
  if (auth()->check() && $request->user()->role === 'admin') {
      return $next($request);
  }
  ```
* **Impact:** Access control is binary: either `admin` or `member`. There are no intermediate roles (e.g., "Lecturer", "Grader", "Assistant") or specific permission policies. As a result, any user with an `admin` role has destructive access (e.g., delete tasks, modify catalog items, access grading), increasing the risk of privilege abuse.
* **Severity:** **Medium**
* **Recommendation:** Define granular gate permissions. Create specific Policy classes for models (e.g., `MaterialPolicy`, `TaskPolicy`) to govern individual model operations.
* **Example Implementation:**
  ```php
  // In AuthServiceProvider or Model Policy
  public function delete(User $user, Material $material)
  {
      return $user->hasPermissionTo('delete-materials');
  }
  ```

---

### 3. Session Handling & Authentication
* **Current State:** Handled securely by Laravel Breeze. Passwords are encrypted using bcrypt via the standard Laravel hashing configurations.
* **Vulnerability:** Lack of account lockout mechanism. Multiple failed login attempts are not limited by default on the custom login controller.
* **Recommendation:** Implement standard rate-limiting on authentication requests inside the login process (e.g. Breeze's `RateLimiter` trait).

---

### 4. Input Validation & Injection
* **SQL Injection:** High security level. By writing standard Eloquent queries (e.g., `User::query()->where('role', 'member')->get()`), the engine compiles parameters using prepared SQL statements, neutralizing SQL injection vectors.
* **Cross-Site Scripting (XSS):** Blade's standard output brackets `{{ $data }}` escape HTML characters securely. No unsafe raw output tags (`{!! !!}`) are used for user inputs.
* **CSRF (Cross-Site Request Forgery):** Protected. Every Livewire component interaction automatically submits CSRF tokens via request headers, preventing unauthorized session actions.
