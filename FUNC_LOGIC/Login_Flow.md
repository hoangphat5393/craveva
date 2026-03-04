# Login Flow Documentation (Craveva ERP)

This document describes the authentication process, data structures, and troubleshooting steps for the Craveva ERP system.

## 1. ASCII Visual Flow

```ascii
+---------------------+       +----------------------+       +-------------------------+
|   User (Browser)    |       |   Laravel Backend    |       |        Database         |
+---------------------+       +----------------------+       +-------------------------+
          |                              |                                |
          | 1. Enter Email               |                                |
          |----------------------------->|                                |
          | (AJAX POST /check-email)     |                                |
          |                              | 2. Check users table           |
          |                              |------------------------------->|
          |                              |                                |
          |                              | 3. Return: Email Exists?       |
          |                              |<-------------------------------|
          | 4. JSON Response             |                                |
          |<-----------------------------|                                |
          | (Show Password Field)        |                                |
          |                              |                                |
          | 5. Enter Password & Submit   |                                |
          |----------------------------->|                                |
          | (AJAX POST /login)           |                                |
          |                              | 6. Fortify Authentication      |
          |                              |    (AttemptToAuthenticate)     |
          |                              |                                |
          |                              | 7. Validate Credentials        |
          |                              |    (Hash::check)               |
          |                              |------------------------------->| (user_auths table)
          |                              |                                |
          |                              | 8. Check Active Status         |
          |                              |------------------------------->| (users table)
          |                              |                                |
          |                              | 9. Generate Session            |
          |                              |    (Write to file/redis)       |
          | 10. JSON Response            |                                |
          |     {                        |                                |
          |       status: 'success',     |                                |
          |       action: 'redirect',    |                                |
          |       url: '...'             |                                |
          |     }                        |                                |
          |<-----------------------------|                                |
          |                              |                                |
          | 11. Browser Redirects        |                                |
          |----------------------------->|                                |
          | (GET /dashboard)             |                                |
          |                              | 12. Middleware Check           |
          |                              |     (Authenticate.php)         |
          |                              |     - Check Session            |
          |                              |     - Check Cache (Active?)    |
          |                              |                                |
          | 13. Render Dashboard         |                                |
          |<-----------------------------|                                |
          |                              |                                |
```

## 2. Key Database Tables

The authentication system primarily relies on two tables: `user_auths` for credentials and `users` for profile/status.

### Table: `user_auths` (Credentials)

Stores the login credentials. This separates authentication from user profile data, allowing for potential multi-tenant setups where one login accesses multiple companies.

| Column                      | Type      | Description              |
| :-------------------------- | :-------- | :----------------------- |
| `id`                        | BIGINT    | Primary Key              |
| `email`                     | VARCHAR   | **Login Email** (Unique) |
| `password`                  | VARCHAR   | Hashed Password (Bcrypt) |
| `two_factor_secret`         | TEXT      | 2FA Secret Key           |
| `two_factor_recovery_codes` | TEXT      | 2FA Recovery Codes       |
| `created_at`                | TIMESTAMP | Record creation time     |
| `updated_at`                | TIMESTAMP | Last update time         |

### Table: `users` (Profile & Status)

Stores the user's profile information, status, and company association. Linked to `user_auths` (often implicitly via email or explicit relation).

| Column          | Type    | Description                                     |
| :-------------- | :------ | :---------------------------------------------- |
| `id`            | BIGINT  | Primary Key                                     |
| `user_auth_id`  | BIGINT  | FK to `user_auths.id` (if applicable in schema) |
| `name`          | VARCHAR | Display Name                                    |
| `email`         | VARCHAR | Email (Should match `user_auths.email`)         |
| `status`        | ENUM    | **'active'** or 'inactive'. Critical for login. |
| `company_id`    | BIGINT  | FK to `companies.id`                            |
| `is_superadmin` | BOOLEAN | **1** for Superadmin, **0** for others          |
| `image`         | VARCHAR | Profile picture path                            |
| `mobile`        | VARCHAR | Phone number                                    |

### Table: `sessions` (If using database driver)

Stores session data if `SESSION_DRIVER=database`.

| Column          | Type     | Description             |
| :-------------- | :------- | :---------------------- |
| `id`            | VARCHAR  | Session ID              |
| `user_id`       | BIGINT   | User ID (nullable)      |
| `ip_address`    | VARCHAR  | IP Address              |
| `user_agent`    | TEXT     | Browser User Agent      |
| `payload`       | LONGTEXT | Serialized Session Data |
| `last_activity` | INT      | Timestamp               |

## 3. Detailed Flow Explanation

### Step 1: Frontend (`resources/views/auth/login.blade.php`)

- **Email Check:** The user enters an email. An AJAX request (`route('check_email')`) verifies if the email exists in the `users` table.
- **Form Submission:** The form `#login-form` is submitted via `$.easyAjax` (found in `public/vendor/helper/helper.js`). This library expects a JSON response.

### Step 2: Backend Authentication (`config/fortify.php`)

- **Pipeline:** The request goes through the Fortify pipeline:
    1.  `AttemptToAuthenticate`: Finds the user in `user_auths` and checks the password hash.
    2.  `EnsureLoginIsNotThrottled`: Limits login attempts.
    3.  `PrepareAuthenticatedSession`: Regenerates the session ID for security.

### Step 3: Validation & Session (`app/Providers/FortifyServiceProvider.php`)

- **Credentials:** Validated against `user_auths`.
- **Status:** The `UserAuth::validateLoginActiveDisabled` method checks if the linked `User` is active.
- **Response:**
    - If successful, `LoginResponse` returns a JSON object with a redirect URL.
    - **Redirect Logic:**
        - Superadmin -> `/account/super-admin-dashboard`
        - Regular User -> `/account/dashboard` (or intended URL)

### Step 4: Middleware Checks (`app/Http/Middleware/Authenticate.php`)

- **Session Check:** Ensures the user has a valid session.
- **Cache Check:** Retrieves `user_is_active_{id}` from the cache.
    - **CRITICAL:** If this cache key is `FALSE`, the user is logged out immediately, even if the DB says `active`.

## 4. Troubleshooting & Common Issues

### Issue 1: "User logs in but is immediately redirected back to login"

- **Cause:** Stale Cache. The `user_is_active_{id}` cache key is set to `FALSE`.
- **Fix:** Clear the cache.
    ```bash
    php artisan cache:forget user_is_active_{USER_ID}
    # OR
    php artisan cache:clear
    ```

### Issue 2: "AJAX Error or Form does not submit"

- **Cause:** The backend is returning HTML (redirect) instead of JSON, confusing `$.easyAjax`.
- **Fix:** Ensure `FortifyServiceProvider.php` checks `$request->wantsJson()` and returns a JSON response:
    ```php
    if ($request->wantsJson()) {
        return response()->json(['status' => 'success', 'action' => 'redirect', 'url' => ...]);
    }
    ```

### Issue 3: "Credentials do not match"

- **Cause:** Password hash mismatch between `user_auths` and input.
- **Fix:** Reset password or check if the user exists in `user_auths` (not just `users`).
    - **Note:** The system uses `user_auths` for password validation, NOT `users`.

### Issue 4: "419 Page Expired"

- **Cause:** Missing CSRF token or session cookie issues.
- **Fix:**
    - Ensure `{{ csrf_field() }}` is in the form.
    - Check `SESSION_DOMAIN` in `.env`. Set it to `null` for local development or the exact domain (e.g., `.craveva.com`) for staging.

## 5. Key Files

- **Frontend:** `resources/views/auth/login.blade.php`
- **Helper JS:** `public/vendor/helper/helper.js`
- **Provider:** `app/Providers/FortifyServiceProvider.php`
- **Middleware:** `app/Http/Middleware/Authenticate.php`
- **Models:** `app/Models/User.php`, `app/Models/UserAuth.php`
