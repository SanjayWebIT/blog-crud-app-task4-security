# Blog CRUD Application — Tasks 1–4 (PHP + MySQL)

A learning project with account registration/login, post CRUD, search, pagination, a magazine-style single article page, and Task 4 security enhancements.

## Requirements
- PHP 8.0+ with PDO MySQL and mbstring extensions
- MySQL or MariaDB
- MAMP, XAMPP, or WAMP
- Web browser

## Fresh installation
1. Copy this folder to your web root, for example `C:\MAMP\htdocs\blog-crud-app`.
2. Start Apache and MySQL.
3. Import `database.sql` in phpMyAdmin.
4. Update the database credentials in `config.php` for your local environment.
5. Visit `http://localhost/blog-crud-app/` (or your configured MAMP port).
6. Register an account. It will receive the `author` role by default.
7. To make the first account an administrator, open phpMyAdmin → SQL and run the following after replacing the username:

   ```sql
   UPDATE users SET role = 'admin' WHERE username = 'sanjay';
   ```

8. Log out and log in again. Administrators will see **Manage users** in the navigation.

## Updating an existing Task 3 database
Do not re-import `database.sql` over your existing database. Run `migration_task4.sql` once in phpMyAdmin. Replace `YOUR_USERNAME` in its final statement with your account username before executing. If you prefer to promote the account later, run the UPDATE statement separately.

## Features
- Register, login, logout, password hashing, and session ID regeneration
- Create, read, update, and delete blog posts
- Search posts by title/content and paginate results
- Magazine-style single article page and sidebar
- Prepared SQL statements using PDO for values supplied by users
- Server-side and browser-native client-side form validation
- CSRF tokens on all state-changing forms
- Output escaping with `htmlspecialchars()`
- Role-based access control: Admin, Editor, Author
- Admin-only user role management

## Task 4 — Security measures

### 1. Prepared statements / SQL injection prevention
- Database access uses PDO with `PDO::ATTR_EMULATE_PREPARES => false`.
- All SQL statements containing request or session values use `prepare()` and bound/execute parameters.
- Pagination values are bound as integers. Dynamic SQL is limited to fixed query fragments; user input is never concatenated into SQL syntax.

### 2. Form validation
- Server-side validation is authoritative for registration, login, post creation, post editing, and role changes.
- Username: 3–30 letters, digits, or underscores.
- Password: 8–72 bytes; stored using `password_hash()` and checked with `password_verify()`.
- Post title: required, maximum 200 characters. Content: required, maximum 50,000 characters.
- Client-side validation uses `required`, `minlength`, `maxlength`, and `pattern` attributes for immediate feedback. Client-side checks can be bypassed, so server-side validation is always repeated.

### 3. User roles and permissions

| Role | Permissions |
|---|---|
| Admin | Create posts; edit/delete any post; open the user-role management page; assign Admin, Editor, or Author roles |
| Editor | Create posts; edit/delete any post |
| Author | Create posts; edit/delete only their own posts |
| Guest | View and search public posts; cannot create, edit, or delete |

- Public registration always assigns `author`; a submitted role is never accepted.
- Only an Admin can access `users.php` and change roles.
- An Admin cannot accidentally remove their own Admin role from the role-management page.
- Role is re-read from the database when checking permissions, so role changes take effect for active sessions.

### Additional protections
- CSRF tokens are verified for POST requests, including logout and role changes.
- Session cookies use HttpOnly and SameSite=Lax; Secure is enabled when HTTPS is active. Strict session mode is enabled and session IDs are regenerated after login.
- HTML output is escaped to reduce stored/reflected XSS risk.
- Database errors are logged server-side; raw exception details are not displayed to visitors.

## Task 3: Advanced features
- Search by post title or content
- Pagination with five posts per page
- Search term is preserved between pages
- Responsive editorial homepage and individual article layout

## Suggested 10-day Task 4 timeline
1. Day 1: Review database queries and PDO configuration.
2. Day 2: Audit and parameterize all SQL statements.
3. Day 3: Add registration and login validation.
4. Day 4: Add post create/edit validation and limits.
5. Day 5: Add client-side constraints and validation messages.
6. Day 6: Add role column and migrate existing accounts.
7. Day 7: Implement Admin, Editor, and Author permissions.
8. Day 8: Build the Admin user-role management page.
9. Day 9: Test SQL injection strings, invalid forms, CSRF, and access restrictions.
10. Day 10: Document security measures and commit the completed work.

## Manual security test checklist
- [ ] Register with invalid usernames and short/oversized passwords; confirm rejection.
- [ ] Try a duplicate username; confirm rejection.
- [ ] Submit empty/oversized post fields; confirm server-side rejection.
- [ ] Search using a quote or SQL-looking string such as `' OR 1=1 --`; it must be treated as ordinary text.
- [ ] As Author A, try to edit/delete Author B's post by changing the URL ID; expect HTTP 403.
- [ ] As Editor, verify you can edit/delete any post but cannot open `users.php`.
- [ ] As Admin, verify user-role management works and a changed role takes effect.
- [ ] Submit a POST form without a valid CSRF token; expect HTTP 400.
- [ ] Confirm HTML/script-like text in a post is displayed as text, not executed.

## Important before production
This is an educational application, not a production security certification. Use HTTPS, secrets outside the web root/environment variables, login rate limiting, password reset and email verification, backups, least-privilege database credentials, security logging, and regular dependency/runtime updates before public deployment. Never commit real database passwords or production secrets to GitHub.
