# RDRIMS – Research Data & Institutional Management System

## Overview
RDRIMS is a modern, full‑stack research management platform built with **Laravel** (backend) and **Vue 3** (frontend). It provides role‑based dashboards, proposal lifecycle management, project tracking, finance & ethics checks, and a public portal for open research outputs.

## Tech Stack
- **Backend**: PHP 8, Laravel 10, Sanctum authentication, MySQL
- **Frontend**: Vue 3, Vite, Pinia, Vue‑Router, ApexCharts, Tailwind‑CSS (custom design system)
- **Styling**: Vanilla CSS with a curated design system (dark mode, glassmorphism, micro‑animations)
- **Testing**: PHPUnit, Cypress (optional)

## Quick Start
```bash
# Clone the repo
git clone https://github.com/yourorg/RDRIMS.git
cd RDRIMS

# Backend setup
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed   # seeds demo data
php artisan serve            # http://127.0.0.1:8000

# Frontend setup
cd frontend
npm install
npm run dev                # http://localhost:5173
```

## Public Endpoints (no auth required)
- `GET /api/settings` – system configuration
- `GET /api/lookups/{table}` – reference data
- `GET /api/universities`, `GET /api/calls`, `GET /api/publications`, `GET /api/community-problems`, `GET /api/events`
- `GET /api/public/*` – public‑facing resources (projects, publications, events)

These endpoints are whitelisted in `frontend/src/services/api.js` to **prevent automatic logout** on a 401 response.

## Authentication Flow
1. **Login** – `POST /api/login` returns a Sanctum token stored in `localStorage`.
2. **Axios interceptor** injects the token into every request.
3. **Router guard** (`router/index.js`) redirects unauthenticated users to `/login` and prevents logged‑in users from accessing guest routes.
4. **Logout** clears the token and redirects to `/login`.

## Role‑Based Navigation
The navigation menu (`MainLayout.vue`) builds its items dynamically based on `auth.hasRole(...)`. Roles include:
- `super_admin`
- `research_admin`
- `reviewer`
- `finance_officer`
- `ethics_officer`
- `director`, `department_head`, etc.

## Contributing
1. Fork the repo.
2. Create a feature branch.
3. Follow the existing coding style (Tailwind‑free, vanilla CSS, component‑first).
4. Submit a pull request.

## License
MIT © 2026 RDRIMS Team
