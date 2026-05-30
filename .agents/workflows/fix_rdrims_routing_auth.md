---
description: Fix RDRIMS routing conflicts and authentication barriers
---

# Overview
This workflow outlines the step‑by‑step process to resolve the routing loops and authentication redirects that prevent the public home page from loading for unauthenticated guests. It covers backend route configuration, controller adjustments, frontend router guards, Axios interceptor tweaks, and UI layout updates.

## Step 1 – Verify Public Settings Endpoint
1. Open `backend/routes/api.php`.
2. Ensure the `/settings` endpoint is **outside** the `auth:sanctum` middleware group.
3. If it is inside, move it to the public group:
   ```php
   Route::get('/settings', [SettingController::class, 'index']);
   ```
4. Run `php artisan route:list` and confirm the route is listed without the `auth:sanctum` middleware.

## Step 2 – Remove Authorization Gate from SettingController
1. Open `backend/app/Http/Controllers/SettingController.php`.
2. Locate the `index` method and **remove** any `$this->authorize(...)` or policy checks.
3. The method should simply return the settings:
   ```php
   public function index(): JsonResponse {
       return response()->json(Setting::all());
   }
   ```
4. Commit the change.

## Step 3 – Adjust Frontend API Interceptor
1. Open `frontend/src/services/api.js`.
2. Add a whitelist of public endpoints that should **not** trigger a logout on a 401 response, e.g.:
   ```js
   const PUBLIC_ENDPOINTS = [
     '/settings',
     '/lookups',
     '/universities',
     '/calls',
     '/publications',
     '/community-problems',
   ];
   ```
3. Modify the response interceptor:
   ```js
   if (error.response?.status === 401) {
     const isPublic = PUBLIC_ENDPOINTS.some(p => error.config.url.includes(p));
     if (!isPublic) {
       localStorage.removeItem('rdrims_token');
       localStorage.removeItem('rdrims_user');
       if (window.location.pathname !== '/login') window.location.href = '/login';
     }
   }
   ```
4. Save and ensure the file is recompiled (`npm run dev`).

## Step 4 – Refactor Vue Router Guard
1. Open `frontend/src/router/index.js`.
2. Confirm the `requiresAuth` meta flag is only on routes under `/app` (the authenticated layout).
3. Ensure the public root route (`/`) **does not** have `requiresAuth`.
4. Update the `beforeEach` guard to respect the public whitelist:
   ```js
   if (to.meta.requiresAuth && !auth.isAuthenticated) return '/login';
   if (to.meta.guest && auth.isAuthenticated) return '/dashboard';
   ```
5. Verify that the public home route (`/`) loads `PublicLayout.vue` and its child `HomeView.vue`.

## Step 5 – Verify Public Layout Components
1. Open `frontend/src/layouts/PublicLayout.vue`.
2. Ensure it does **not** import or depend on the auth store for rendering.
3. Confirm it includes `PublicNavbar` and `PublicFooter`.
4. If any auth‑only components are present, wrap them in `v-if="auth.isAuthenticated"`.

## Step 6 – Test the Public Home Page
1. Stop the dev server (`Ctrl+C`) and restart both backend and frontend to clear caches.
2. Open an incognito browser window and navigate to `http://localhost:5173/`.
3. The home page should load without redirecting to `/login`.
4. Open the browser console and verify no 401 errors are logged for the whitelisted endpoints.

## Step 7 – Automated Tests (Optional but Recommended)
1. Add a Laravel feature test `tests/Feature/PublicSettingsTest.php` that asserts the `/api/settings` endpoint returns 200 without authentication.
2. Add a Cypress test `cypress/integration/public_home.spec.js` that visits `/` and checks that the URL remains `/` and the home view is visible.
3. Run the tests and ensure they pass.

## Step 8 – Documentation Update
1. Update `README.md` or the project wiki with a section **"Public Access & Authentication"** describing the public endpoints and the auth interceptor whitelist.
2. Mention the routing guard logic for future developers.

---
**End of Workflow**
