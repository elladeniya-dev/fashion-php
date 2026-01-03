# Fix Log - January 3, 2026

## Issues found and fixed

1. **Docker build failure**
   - **Problem:** Dockerfile tried to `COPY ./modules` but modules were moved under `public/`, causing build error (path not found).
   - **Fix:** Removed the `COPY ./modules` line from Dockerfile. Rebuild now succeeds.

2. **DB config include path (all modules)**
   - **Problem:** After moving modules into `public/modules`, PHP includes still pointed to `../../config/database.php`.
   - **Fix:** Updated all module PHP files to `include_once __DIR__ . '/../../../config/database.php';` so DB loads correctly.

3. **Admin/Customer/Supplier login page assets and links**
   - **Problem:** Login pages referenced CSS and targets using old relative paths.
   - **Fix:** Updated CSS links to `../../assets/css/login.css`, corrected form actions to `login.php`, and fixed cross-links between portals.

4. **Customer registration form action**
   - **Problem:** Form still posted to old filename `customer registration.php`.
   - **Fix:** Updated action to `registration.php`.

5. **Supplier registration form action**
   - **Problem:** Form still posted to old filename `supplier registration.php`.
   - **Fix:** Updated action to `registration.php`.

6. **Landing page portal links**
   - **Problem:** Index links used `../modules/...` paths that broke after moving modules under `public/`.
   - **Fix:** Updated links to `modules/...` (relative to public root).

## Outstanding considerations
- Passwords are stored in plain text; consider `password_hash()` / `password_verify()`.
- No CSRF/input validation; add validation before deploying.
- Missing images (`logo.jpg`, `registration.jpg`) will show 404 if not added under `public/assets/images/`.
