# RDRIMS – FRONTEND FUNCTIONAL SPECIFICATION

**Focus: Functionality, Logic, Rules, Workflows, Permissions, Data Flow, API Integration**
**No styling, no CSS, no design discussion.**

---

## 1. SYSTEM OVERVIEW

### 1.1 What RDRIMS Does
RDRIMS digitizes the complete research lifecycle for any university. It replaces paper forms, Excel spreadsheets, and fragmented digital files with a centralized, role-based, workflow-driven web platform.

### 1.2 Core Functional Principles
- **Multi-Tenant:** Any university can be added. Wollo University is the first tenant, not the only one.
- **Dynamic Everything:** All dropdowns, statuses, types, roles, permissions, and configuration come from the API. Nothing is hardcoded in the frontend.
- **Public-First:** Key information (calls, events, publications, researchers, community problems) is visible without authentication.
- **Guest Registration:** New users register and get the "guest" role with limited access. Admins upgrade their roles.
- **Hierarchical Access:** Data visibility is scoped by the user's position in the university hierarchy.
- **Workflow-Driven:** Proposals, reviews, outputs, finance checks, and ethics approvals follow strict state machines.

---

## 2. USER ROLES & PERMISSIONS SYSTEM

### 2.1 The 10 Roles
| # | Role Name | How User Gets This Role | What They Can Do |
|---|-----------|------------------------|-------------------|
| 1 | `super_admin` | Created by system/seeder only | Everything across all universities |
| 2 | `research_admin` | Assigned by super_admin | Everything within their university |
| 3 | `director` | Assigned by research_admin | Manage their research center |
| 4 | `department_head` | Assigned by research_admin | Manage their department's outputs |
| 5 | `researcher` | Assigned by admin, or self-requested | Submit proposals, manage projects, submit outputs |
| 6 | `reviewer` | Assigned by admin | Review assigned proposals (blind) |
| 7 | `finance_officer` | Assigned by admin | Approve/reject proposal budgets and expenses |
| 8 | `ethics_officer` | Assigned by admin | Approve/reject ethics clearance requests |
| 9 | `student` | Assigned by admin, or self-registered | Submit theses, internships, projects |
| 10 | `guest` | Default on self-registration | Browse public content, limited authenticated features |

### 2.2 How Role Assignment Works
- User self-registers → automatically gets `guest` role
- `guest` sees a banner on dashboard: "You have limited access. Contact your university administrator to get a researcher, student, or other role."
- Admin goes to `/users` → clicks user → "Assign Roles" modal → checks one or more roles → saves
- A user can have **multiple roles** (e.g., a person can be both `researcher` and `reviewer`)
- The sidebar and all UI elements adapt to the **union** of all assigned roles and permissions

### 2.3 Data Scoping Rules (Enforced on Every List Page)
| Role | Sees |
|------|------|
| `super_admin` | All records across all universities |
| `research_admin` | Records belonging to their university (determined by their department→faculty→campus→university chain) |
| `director` | Records belonging to their research center |
| `department_head` | Records belonging to their department |
| `researcher` | Their own proposals, projects, outputs only |
| `reviewer` | Proposals assigned to them for review |
| `finance_officer` | Finance checks for their university's proposals |
| `ethics_officer` | Ethics requests for their university's proposals |
| `student` | Their own outputs only |
| `guest` | Public data only; sees upgrade prompt on authenticated pages |

### 2.4 How Scope Is Computed
The user object from `GET /api/user` includes a `department` relationship which chains up: `department → faculty → campus → university`. The frontend extracts the IDs from this chain to determine what data to request.

---

## 3. UNIVERSITY HIERARCHY (Multi-Tenant Foundation)

### 3.1 The Chain
```
University
  └── Campus (belongs to University)
        └── Faculty (belongs to Campus)
              └── Department (belongs to Faculty)
                    └── User (belongs to Department)
```

### 3.2 Research Centers
- A research center can be linked at **any level**: directly to a University, to a Campus, or to a Faculty
- A user can belong to one or more research centers via `user_research_centers` pivot
- Each membership has a `center_role_id` (Director, Senior Researcher, Researcher, etc.)

### 3.3 Adding a New University (Super Admin Only)
- Super admin goes to `/universities` → "Add University" → enters name and code → saves
- Then adds Campuses under that university
- Then adds Faculties under each campus
- Then adds Departments under each faculty
- Then users can be assigned to those departments
- The new university is immediately visible on the public homepage and all filters

---

## 4. ALL 21 LOOKUP TABLES (Dynamic Dropdowns)

Every dropdown, filter, and status badge in the system gets its values from these API endpoints. If an admin adds a new value to any of these tables (via the admin UI or directly in the database), the frontend picks it up automatically on the next API call.

| # | API Endpoint | Used In |
|---|-------------|---------|
| 1 | `/api/lookups/call_statuses` | Call status filter, call form |
| 2 | `/api/lookups/proposal_types` | Proposal type filter, proposal form |
| 3 | `/api/lookups/proposal_statuses` | Proposal status filter, proposal detail |
| 4 | `/api/lookups/review_decisions` | Review form (Accept/Minor/Major/Reject) |
| 5 | `/api/lookups/finance_check_statuses` | Finance check workflow |
| 6 | `/api/lookups/ethics_approval_statuses` | Ethics approval workflow |
| 7 | `/api/lookups/patent_statuses` | Patent status filter |
| 8 | `/api/lookups/community_problem_statuses` | Community problem tabs |
| 9 | `/api/lookups/project_statuses` | Project status filter |
| 10 | `/api/lookups/milestone_statuses` | Milestone inline toggle |
| 11 | `/api/lookups/task_statuses` | Task inline toggle |
| 12 | `/api/lookups/investigator_roles` | Proposal co-investigator form |
| 13 | `/api/lookups/invitation_statuses` | Investigator status display |
| 14 | `/api/lookups/agreement_types` | Agreement file attachment |
| 15 | `/api/lookups/output_categories` | Output list filter, output form |
| 16 | `/api/lookups/student_levels` | Output form (student level selector) |
| 17 | `/api/lookups/output_subtypes` | Output form (subtype selector) |
| 18 | `/api/lookups/detection_services` | Plagiarism check display |
| 19 | `/api/lookups/detection_statuses` | Plagiarism check progress |
| 20 | `/api/lookups/participant_types` | Output participant form |
| 21 | `/api/lookups/output_statuses` | Output workflow |

---

## 5. SYSTEM SETTINGS (Runtime Configuration)

The system behavior is controlled by settings from `GET /api/settings`. The frontend fetches these on app startup and caches them in the lookup store.

| Setting Key | What It Controls |
|-------------|-----------------|
| `app_name` | Displayed in browser tab, navbar, footer |
| `default_language` | Initial language (en or am) |
| `max_proposal_budget` | Validation on proposal form |
| `min_proposal_budget` | Validation on proposal form |
| `allow_public_registration` | Shows/hides Register link |
| `ethics_required` | Whether ethics step is mandatory before proposal approval |
| `plagiarism_threshold` | Color threshold on similarity score gauge (green < threshold, red > threshold) |
| `auto_approve_below_budget` | Proposals below this amount skip finance check |
| `default_project_duration_months` | Default end date when creating project from proposal |
| `max_reviewers_per_proposal` | Limit on reviewer assignment |
| `min_reviewers_per_proposal` | Minimum before proposal can be approved |
| `proposal_review_deadline_days` | Countdown shown to reviewers |
| `max_file_upload_size_mb` | FileUpload component max size |
| `allowed_file_types` | FileUpload component accept attribute |
| `enable_notifications` | Whether to show notification bell and send emails |
| `allow_public_problem_submission` | Whether guests can submit community problems |
| `contact_email` | Footer contact section |
| `contact_phone` | Footer contact section |
| `contact_address` | Footer contact section |
| `app_description` | Footer about section |

---

## 6. AUTHENTICATION FLOW

### 6.1 Public Access (No Token)
- User visits `/` → sees public homepage
- All public pages fetch data from public API endpoints (no auth header)
- Navbar shows "Sign In" and "Sign Up" buttons

### 6.2 Registration
- User fills form: Name, Email, Password, Confirm Password, Department (optional dropdown), University (optional dropdown)
- Password strength meter: checks length, uppercase, lowercase, numbers, special characters → shows weak/medium/strong
- Confirm password match indicator: real-time ✓ or ✗
- Department dropdown: fetched from `GET /api/departments`, grouped by Faculty → Campus → University
- On submit: `POST /api/register` with all fields
- Backend creates user with role `guest` only
- Backend returns user object + token
- Frontend stores token in localStorage, user in Pinia store
- Redirect to `/dashboard`

### 6.3 Login
- User fills email and password
- `POST /api/login`
- Backend returns user object + token
- Frontend stores token and user
- Redirect to `/dashboard`
- If account is deactivated (`is_active = false`), backend returns 403, frontend shows error message

### 6.4 Logout
- `POST /api/logout` (with token)
- Backend revokes token
- Frontend clears localStorage and Pinia store
- Redirect to `/`

### 6.5 Token Expiry
- API interceptor catches 401 responses
- Automatically clears storage
- Redirects to `/login`
- Shows notification: "Your session has expired. Please sign in again."

### 6.6 Forgot Password
- User enters email → `POST /api/forgot-password`
- Backend sends reset link email
- Frontend shows success message

### 6.7 Reset Password
- User clicks link in email → arrives at `/reset-password/:token`
- Enters new password + confirm password
- `POST /api/reset-password` with token, email, password
- Success → redirect to `/login` with notification

---

## 7. NAVIGATION & SIDEBAR LOGIC

### 7.1 Public Navigation
- Always visible: Home, Research Calls, Events, Publications, Researchers, Community
- Language toggle: EN / አማ (saves preference to `PUT /api/language-preference` and localStorage)
- Sign In / Sign Up buttons

### 7.2 Authenticated Sidebar (Dynamic Generation)
The sidebar is built by checking the user's roles array. No menu items are hardcoded.

**Logic:**
```
Start with common items: Dashboard, Proposals, Calls, Projects, Publications, Events, Partners, Outputs, Patents, Community, Notifications, Profile

If user.roles includes 'reviewer':
    Insert 'Reviews' after 'Proposals'

If user.roles includes 'finance_officer':
    Add 'Finance Checks'

If user.roles includes 'ethics_officer':
    Add 'Ethics Requests'

If user.roles includes 'research_admin' OR 'super_admin':
    Add 'Users', 'Reports', 'File Repository', 'Research Centers', 'Academic Years', 'Review Criteria', 'Expertise', 'Departments', 'Faculties', 'Campuses'

If user.roles includes 'super_admin':
    Add 'Settings', 'Roles', 'Permissions', 'Audit Logs', 'Universities'

If user.roles includes 'director':
    Add 'My Research Center' (links to their specific center)

If user.roles includes 'department_head':
    Add 'My Department' (links to their specific department)
```

### 7.3 Notifications Badge
- On sidebar load, fetch `GET /api/notifications?unread=1`
- Get the count from the response meta
- Display count badge on the Notifications menu item
- Refresh count every 60 seconds via polling or on WebSocket event

### 7.4 Breadcrumbs
- Generated from route meta
- Example: Home > Proposals > Create Proposal
- Each segment is clickable (except current page)

---

## 8. PUBLIC HOMEPAGE FUNCTIONALITY

### 8.1 Stats Bar
- **Universities:** `GET /api/universities` → count of items in response
- **Open Calls:** `GET /api/calls?status=open` → total from pagination meta
- **Publications:** `GET /api/publications` → total from pagination meta
- **Community Problems Solved:** `GET /api/community-problems?status=completed` → total from pagination meta
- Numbers animate from 0 to final value on scroll into view

### 8.2 Open Calls Section
- Fetch: `GET /api/calls?status=open&per_page=6`
- Filter by university: `GET /api/calls?status=open&university_id=X`
- University dropdown populated from `GET /api/universities`
- Each card shows: title, university name, status badge (from lookup), deadline with countdown (red if < 7 days), thematic areas as chips, description truncated to 3 lines
- Click card → `/calls/:id`
- If no open calls: Empty state with "No open calls at this time. Check back soon."

### 8.3 Upcoming Events Section
- Fetch: `GET /api/events?upcoming=true&per_page=6`
- Each card: date badge (day + month), title, venue, date/time range, university
- Click → `/events/:id`

### 8.4 Latest Publications Section
- Fetch: `GET /api/publications?per_page=10&sort=publication_date:desc`
- Each row: title (link to detail), authors (first 3 + "et al."), journal (italic), date, DOI link, citation count, keywords chips, PDF download icon (only if file is public)
- Click title → `/publications/:id`

### 8.5 Community Impact Section
- Fetch: `GET /api/community-problems?per_page=6`
- Each card: title, location, status badge, description (2 lines), submitted by (name or "Anonymous"), rating stars (if solved with feedback)
- "Submit a Problem" button:
  - Check setting `allow_public_problem_submission`
  - If true: opens modal with form
  - If false: redirects to `/register`

### 8.6 Our Researchers Section
- Fetch: `GET /api/users?role=researcher&per_page=8` (public endpoint returns limited fields)
- Filter by university dropdown
- Each card: avatar (initials), name, department→university chain, expertise chips (max 3), publications count, ORCID icon link
- Click → `/researchers/:id`

### 8.7 Partner Institutions Section
- Fetch: `GET /api/universities`
- Each card: name, code, campus count, research center count

---

## 9. PUBLIC SUB-PAGES

### 9.1 Public Calls List (`/calls`)
- Fetch: `GET /api/calls?status=all&page=X`
- Tabs: All / Open / Closed (from lookup `call_statuses`)
- Search: debounced, filters by title and thematic_areas
- University filter dropdown
- Cards same as homepage section
- Pagination

### 9.2 Public Call Detail (`/calls/:id`)
- Fetch: `GET /api/calls/:id`
- Display: title, university, status badge, deadline with countdown, thematic areas chips, full description
- Guideline file download (if `guideline_file_id` exists and file is public)
- "X proposals submitted" count
- CTA banner: "Interested? Sign in to submit a proposal" with buttons to `/login` and `/register`
- Related calls: same university, same thematic areas

### 9.3 Public Events List (`/events`)
- Fetch: `GET /api/events?page=X`
- Tabs: Upcoming / Past / All
- University filter
- Cards with date badge, title, venue, date/time
- Pagination

### 9.4 Public Event Detail (`/events/:id`)
- Fetch: `GET /api/events/:id`
- Display: date badge, image, title, venue, date/time, university, description
- Capacity bar: "X registered out of Y"
- Registration deadline with countdown
- CTA: "Register for this Event" → if not authenticated, link to `/login`

### 9.5 Public Publications List (`/publications`)
- Fetch: `GET /api/publications?page=X`
- Search: title, journal, keywords, author
- Filters: year, university, project
- List rows with title (link), authors, journal, date, DOI, citation count, keywords, PDF download
- Pagination

### 9.6 Public Publication Detail (`/publications/:id`)
- Fetch: `GET /api/publications/:id`
- Display: title, authors with order numbers, journal, DOI (external link), date, abstract, keywords, citation count, linked project, PDF download
- Related publications: same keywords, same authors

### 9.7 Public Researchers List (`/researchers`)
- Fetch: `GET /api/users?role=researcher&page=X` (limited public fields)
- Search: name, expertise
- Filters: university, department (cascading), expertise
- Cards: avatar, name, department chain, expertise chips, publications count, ORCID link
- Pagination

### 9.8 Public Researcher Profile (`/researchers/:id`)
- Fetch: `GET /api/users/:id` (limited public fields)
- Header: avatar, name, department→faculty→campus→university chain
- Bio
- ORCID, Google Scholar, Scopus, LinkedIn links as icon buttons
- Expertise chips
- Tabs:
  - Publications: fetch `GET /api/publications?author_id=X`
  - Research Centers: centers the researcher belongs to, with role badges
  - Community: problems claimed/completed by this researcher, with status badges and ratings
- **No private data exposed:** email hidden, roles hidden, activity logs hidden

### 9.9 Public Community (`/community`)
- Fetch: `GET /api/community-problems?page=X`
- Tabs: Open / In Progress / Solved (from lookup)
- Search: title, location
- Filters: university, location
- Cards: title, location, status badge, description, submitted by/anonymous, date, rating (if solved)
- Submit Problem button (modal if setting allows, else CTA to register)
- Pagination

### 9.10 Submit Community Problem Modal
- Fields: Title (required), Description (required), Location (required), Contact Info (optional), Anonymous toggle
- On submit: `POST /api/community-problems`
- If not authenticated and public submission is allowed: submit without token
- If authenticated: submit with token
- On success: close modal, refresh list, show success notification

---

## 10. DASHBOARD FUNCTIONALITY

### 10.1 Welcome Section
- "Welcome back, [user.name]!"
- Current date formatted
- University name (from user.department chain)
- Primary role badge (first role from user.roles)

### 10.2 Stats Cards (Role-Dependent)
The frontend determines which stats to show based on `user.roles`:

**Logic:**
```
If super_admin:
    Fetch counts from: /api/proposals, /api/projects, /api/universities, /api/users
If research_admin:
    Fetch counts from: /api/proposals (scoped by API), /api/projects (scoped), /api/publications (scoped), /api/events (scoped)
If director:
    Fetch counts from: /api/proposals (center), /api/projects (center), /api/outputs (center)
If department_head:
    Fetch counts from: /api/proposals (dept), /api/outputs (dept), /api/users (dept)
If researcher:
    Fetch counts from: /api/proposals?submitted_by=me, /api/projects?pi=me, /api/publications?author=me, /api/outputs?author=me
If reviewer:
    Fetch counts from: /api/reviewer/proposals (pending), /api/reviewer/proposals (completed)
If finance_officer:
    Fetch counts from: /api/proposals?status=finance_check (pending), /api/proposals?status=finance_check (approved)
If ethics_officer:
    Fetch counts from: /api/ethics-requests (pending), /api/ethics-requests (approved)
If student:
    Fetch counts from: /api/outputs?author=me (by status)
If guest:
    Show public stats: universities, open calls, publications, solved problems
    Show banner: "You are currently a Guest. Contact your administrator to upgrade your role."
```

### 10.3 Charts
- **Proposals by Status (Pie Chart):** Fetch all proposals (scoped), group by status name, render pie chart with dynamic colors
- **Projects by Status (Donut Chart):** Fetch all projects (scoped), group by status name

### 10.4 Quick Actions (Role-Dependent)
```
If researcher or admin:
    "Submit New Proposal" → /proposals/create
    "My Drafts" → /proposals?status=draft
If reviewer:
    "Pending Reviews" → /reviewer/proposals (with count badge)
If finance_officer:
    "Pending Finance Checks" → /finance-checks (with count badge)
If ethics_officer:
    "Pending Ethics Requests" → /ethics-requests (with count badge)
If admin:
    "Create Call" → opens call creation modal
    "Manage Users" → /users
    "Generate Report" → /reports
All users:
    "View Events" → /events
    "Community Problems" → /community
    "Register for Event" → /events
```

### 10.5 Upcoming Deadlines (Scoped)
- **Calls closing within 14 days:** `GET /api/calls?status=open` → filter where deadline is within 14 days
- **My tasks due within 7 days:** `GET /api/projects?pi=me` → for each project, `GET /api/projects/:id/milestones` → for each milestone, `GET /api/milestones/:id/tasks` → filter where `assigned_to = me` and `due_date` within 7 days
- **MoUs expiring within 30 days:** `GET /api/partners/:id/mo-us` → filter where end_date within 30 days
- **Licenses expiring within 30 days:** `GET /api/patents/:id/licenses` → filter where end_date within 30 days

---

## 11. PROPOSAL MANAGEMENT

### 11.1 Proposals List (`/proposals`)
**Scoping Logic:**
- The API handles scoping based on the authenticated user's role and department chain
- Frontend just calls `GET /api/proposals?page=X&search=Y&status=Z&type=W&call=V`
- The backend returns only the proposals the user is authorized to see

**Filters:**
- Search input (title, keywords) — debounced 300ms, sends as `search` query param
- Status multi-select — checkboxes populated from `GET /api/lookups/proposal_statuses`, sends as `status[]` array
- Type dropdown — populated from `GET /api/lookups/proposal_types`, sends as `type`
- Call dropdown — populated from `GET /api/calls`, sends as `call_id`
- Date range picker — sends as `from_date` and `to_date`
- "Clear Filters" resets all filters to default and refetches

**Table Display:**
- Columns: Title (clickable → detail), Type badge, Status badge, Budget (ETB formatted), Call name, Submitted by (name + avatar), Submitted date
- Actions column:
  - View button (always visible)
  - Edit button (only if `status_id` = draft AND `submitted_by` = current user)
  - Delete button (only if `status_id` = draft AND `submitted_by` = current user) — opens confirmation dialog

**"New Proposal" Button:** Visible only if user has role `researcher`, `research_admin`, or `super_admin`

**Pagination:** Standard Laravel paginator (current_page, last_page, total, per_page)

**States:**
- Loading: skeleton rows
- Empty: "No proposals yet. Create your first proposal!" + button to `/proposals/create`
- No results after filter: "No proposals match your filters. Try adjusting or clear filters."
- Error: "Failed to load proposals. Please try again." + retry button

### 11.2 Create Proposal (`/proposals/create`)
**Access:** researcher, research_admin, super_admin

**Auto-Save:**
- Every 30 seconds, save form data to localStorage under key `proposal_draft_{user_id}`
- On mount, check localStorage for saved draft and offer to restore: "You have an unsaved draft. Would you like to continue?" (Yes/No)

**Step 1 - Basic Information:**
- **Call dropdown:** Fetch `GET /api/calls?status=open` (only open calls). If response is empty, show warning: "No open calls available. You can save this proposal as a draft and submit when a call opens."
- **Type dropdown:** Fetch `GET /api/lookups/proposal_types` — options: sr, sp, thesis
- **Title:** Text input, required, max 255 chars with live counter
- **Abstract:** Textarea, required, 5 rows
- **Objectives:** Textarea, required, 5 rows
- **Methodology:** Textarea, required, 5 rows
- **Keywords:** Tag input — type and press Enter or comma to add. Minimum 3 keywords recommended. Shows warning if less than 3.
- **Budget:** Number input, ETB formatted. Must be between `settings.min_proposal_budget` and `settings.max_proposal_budget`. Shows validation error if outside range.
- **Budget Allocation (expandable section):** Personnel, Equipment, Travel, Materials, Other — each is a number input. Auto-sum displayed below. Must equal total budget. Shows red warning if sum ≠ budget.
- **Academic Year:** Dropdown from `GET /api/academic-years`, defaults to the one with `is_current = true`

**Step 2 - Co-Investigators:**
- **Existing investigators list:** Cards showing name, email, institution, role badge (from lookup), invitation status badge (from lookup), remove button
- **Add Investigator:**
  - Toggle: "Existing User" / "External Collaborator"
  - If Existing User: searchable dropdown from `GET /api/users?role=researcher` (shows name + email)
  - If External: Name input, Email input, Institution input
  - Role dropdown from `GET /api/lookups/investigator_roles`
  - "Add" button + "Cancel" button
- Minimum 1 investigator required (the submitter counts)

**Step 3 - Documents:**
- **Proposal Document:** FileUpload component, accept PDF/DOC/DOCX, max size from settings
- **Ethics Clearance Document (optional):** Separate FileUpload, labeled clearly
- Uploaded files shown with: name, size, upload date, download icon, replace icon, delete icon

**Step 4 - Review & Submit:**
- Read-only summary of all fields from Steps 1-3
- Completion checklist:
  - ✅/⚠️ All required fields filled (title, abstract, objectives, methodology, keywords ≥ 3, budget valid)
  - ✅/⚠️ At least 1 investigator added
  - ✅/⚠️ Proposal document uploaded
  - ✅/⚠️ Budget allocation matches total (if allocation filled)
- Research policy confirmation text: "I confirm that this proposal is my original work and all information provided is accurate."
- Checkbox to agree (required before submit)

**Bottom Bar (sticky):**
- "Save as Draft" (outline button): Calls `POST /api/proposals` with `status_id` = draft lookup ID. On success, redirect to `/proposals/:id` with notification "Proposal saved as draft."
- "Submit Proposal" (primary button): Calls `POST /api/proposals` with `status_id` = draft, then immediately calls `POST /api/proposals/:id/submit`. On success, redirect to `/proposals/:id` with notification "Proposal submitted successfully!"
- Both buttons disabled until form is valid

### 11.3 Proposal Detail (`/proposals/:id`)
**Data Fetching:** `GET /api/proposals/:id` — loads proposal with all relationships

**Top Bar:**
- Back link: "← Back to Proposals" → `/proposals`
- Title (large text)
- Status badge (large, color dynamic from lookup)
- Proposal ID: `#PRO-{id}` (formatted with leading zeros)

**Action Buttons (Contextual — shown only if conditions met):**

The frontend checks the proposal's current `status_id` and the user's roles to determine which buttons to show:

```
If proposal.status is 'draft' AND current user is the submitter:
    Show: [Edit] [Submit] [Delete]

If proposal.status is 'submitted' AND user has role 'research_admin' or 'super_admin':
    Show: [Assign Reviewers] [Send to Finance Check] [Request Ethics] [Approve] [Reject]

If proposal.status is 'under_review' AND user has role 'research_admin' or 'super_admin':
    Show: [View Reviews] [Approve] [Reject]

If proposal.status is 'finance_check' AND user has role 'finance_officer':
    Show: [Approve Finance] [Reject Finance]

If proposal.status is 'finance_check' AND user has role 'research_admin' or 'super_admin':
    Show: [View Finance Status]

If proposal has ethics_request with status 'pending' AND user has role 'ethics_officer':
    Show: [Approve Ethics] [Reject Ethics]

Always show: [Check Originality] [Download as PDF]
```

**Details Tab:**
- Two-column layout:
  - Left: Call name, Type, Academic Year, Submitted by (avatar + name), Submitted date, Approved by + date (if approved)
  - Right: Budget (large ETB) with allocation breakdown as horizontal progress bars
- Sections: Abstract, Objectives, Methodology
- Keywords displayed as chips

**Investigators Tab:**
- Cards for each investigator: Name, Email, Institution, Role badge (from lookup), Invitation Status badge (from lookup), Invited date
- If proposal is draft and user is owner: "Add Investigator" button

**Documents Tab:**
- File list with: file icon, name (download link), size, upload date, version number
- If proposal is draft and user is owner: Upload button
- Version history expandable per file

**Reviews Tab:**
- If no reviews assigned: "No reviewers assigned yet."
- If reviewers assigned but none submitted: "X of Y reviewers have submitted their reviews." with progress bar
- If all reviews submitted: Summary card showing average score (large number, colored: green ≥ 70%, yellow ≥ 50%, red < 50%), overall decision summary
- Per-reviewer cards: Name (if not blind period), Scores per criterion (mini horizontal bars), Overall score, Decision badge, Comments (expandable)
- Aggregate table: Criteria as rows, Reviewers as columns, Average column

**Finance Tab:**
- Current status badge
- If checked: Checker name + avatar, Checked date, Comments
- If not checked and user is admin: "Request Finance Check" button → `POST /api/proposals/:id/finance-checks`
- If awaiting check and user is finance officer: Approve/Reject buttons with comment textarea

**Ethics Tab:**
- Current status badge
- Ethics request version number
- Generated PDF download link (if generated)
- "Submitted to IRB" badge (true/false)
- If not submitted and user is researcher or admin: "Generate Ethics Request PDF" button → `POST /api/proposals/:id/ethics-requests`
- If awaiting approval and user is ethics officer: Approve/Reject buttons with comment textarea

**Originality Tab:**
- If never checked: "Check Originality" button → opens modal to select service (from lookup) → `POST /api/detection/requests`
- If processing: Progress bar with "Checking... estimated X seconds remaining"
- If completed: Service badge, Similarity Score circular gauge (colored by threshold from settings: green if ≤ threshold, red if > threshold), AI Probability gauge, Report download link, Requested by + date
- If failed: Error message with retry button

### 11.4 Proposal Actions (Modals)

**Assign Reviewers Modal:**
- Opens on click of "Assign Reviewers" button
- Two sections:
  - **Auto-Suggested:** Fetches `GET /api/proposals/:id/suggest-reviewers` — shows reviewer name, expertise match percentage, checkbox to select
  - **Manual Add:** Searchable dropdown from `GET /api/users?role=reviewer` (filters by university scope)
- Currently assigned reviewers shown at top (if any)
- Save → `POST /api/proposals/:id/assign-reviewers` with `reviewer_ids` array
- Maximum reviewers enforced by `settings.max_reviewers_per_proposal`
- Minimum reviewers enforced by `settings.min_reviewers_per_proposal`

**Approve Modal:**
- Confirmation dialog: "This will approve the proposal and automatically create a project. This action cannot be undone."
- Confirm → `POST /api/proposals/:id/approve`
- On success: notification, redirect to `/projects/:new_project_id`

**Reject Modal:**
- Textarea for rejection reason (required, min 50 characters)
- Confirm → `POST /api/proposals/:id/reject` with `comment`
- On success: notification, refresh proposal detail

**Finance Approve/Reject Modal:**
- Same pattern: comment textarea + confirm → `POST /api/proposals/:id/finance-checks` with `status_id` and `comments`

**Ethics Approve/Reject Modal:**
- Same pattern: comment textarea + confirm → `POST /api/proposals/:id/ethics-requests/:id` with `approval_status_id` and `comments`

---

## 12. REVIEWER FUNCTIONALITY

### 12.1 Reviewer Proposals List (`/reviewer/proposals`)
**Access:** reviewer role only

**Fetch:** `GET /api/reviewer/proposals?page=X`

**Tabs:**
- Pending: `GET /api/reviewer/proposals?status=pending`
- Reviewed: `GET /api/reviewer/proposals?status=reviewed`
- All: no filter

**Cards:**
- Title (clickable → detail), Abstract preview (2 lines, italic, truncated), Keywords chips, Budget (ETB), Assigned date, Status badge (Pending/Reviewed), Score (if reviewed)

**States:**
- Empty (pending): "No proposals assigned to you yet. Check back later."
- Empty (reviewed): "You haven't reviewed any proposals yet."
- All reviewed: "All proposals reviewed! Great work."

### 12.2 Blind Review Detail (`/reviewer/proposals/:id`)
**Access:** reviewer, and must be assigned to this proposal

**Data Fetching:** `GET /api/reviewer/proposals/:id` (returns anonymized proposal — no submitter info)

**Blind Review Banner:**
- Yellow banner at top: "Blind Review Mode — Author information is hidden to ensure impartial evaluation."

**Proposal Content (Read-Only):**
- Title, Abstract, Objectives, Methodology, Keywords, Budget
- Document download link (proposal file)
- **Submitter name and investigators are NOT shown**

**Dynamic Review Form:**
- Fetches criteria from `GET /api/review-criteria` — the number of criteria, names, descriptions, and max scores are all dynamic
- For each criterion:
  - Name (bold)
  - Description (small text below name)
  - Score input: number, min 0, max = criterion.max_score
  - Comments textarea (optional)
- Overall Score: number input, 0-100
- Overall Comments: textarea (optional)
- Decision: radio buttons from `GET /api/lookups/review_decisions` — Accept, Minor Revisions, Major Revisions, Reject
- Each decision option has a color indicator (Accept=green, Minor=yellow, Major=orange, Reject=red)

**Buttons:**
- "Save Draft" (outline): Saves scores to localStorage for this proposal (no API call)
- "Submit Review" (primary): `POST /api/reviewer/proposals/:id/review` with scores array, overall_score, overall_comments, decision_id
- On submit: confirmation dialog "You cannot edit your review after submission. Continue?" → Yes/No

**After Submission:**
- Green success banner: "Review submitted on [date]."
- All fields become read-only
- If within `settings.proposal_review_deadline_days` of assignment: "Edit Review" button → changes back to editable mode → resubmit

---

## 13. CALLS MANAGEMENT

### 13.1 Calls List (Authenticated) (`/calls`)
**Data Fetching:** `GET /api/calls?page=X`

**Additional Admin Features:**
- "Create Call" button (visible to research_admin, super_admin)
- Each call card has admin actions: Edit (pencil icon), Close/Reopen (toggle icon), Delete (trash icon)

**Create/Edit Call Modal:**
- Title (required), Description (required)
- Deadline: date picker, must be in the future
- Thematic Areas: text input (comma-separated keywords)
- Status dropdown: from `GET /api/lookups/call_statuses` (draft, open, closed)
- Academic Year dropdown: from `GET /api/academic-years`
- Guideline File: FileUpload component
- Create → `POST /api/calls`
- Edit → `PUT /api/calls/:id`

**Close/Reopen:**
- Close: `PUT /api/calls/:id` with `status_id` = closed
- Reopen: `PUT /api/calls/:id` with `status_id` = open
- Confirmation dialog before action

**Delete:**
- Confirmation: "This will permanently delete this call and cannot be undone. Proposals linked to this call will NOT be deleted."
- `DELETE /api/calls/:id`

### 13.2 Call Detail (`/calls/:id`)
- Full call information
- Mini statistics: proposal count by status for this call (pie chart)
- Proposals table: lists all proposals submitted to this call

---

## 14. PROJECT MANAGEMENT

### 14.1 Projects List (`/projects`)
**Scoping:** Same as proposals — API handles scope based on user role

**Filters:** Search, Status dropdown (from lookup), Academic Year dropdown

**Table Display:**
- Title (clickable → detail), PI (name + avatar), Status badge, Start Date, End Date, Budget, Progress bar (completed tasks / total tasks × 100), Outputs count

**"Create Project" Button:** Visible to research_admin, super_admin

**Create Project Modal:**
- Proposal dropdown: `GET /api/proposals?status=approved` (only approved proposals without existing projects)
- On select: auto-fills title, budget, PI from proposal
- Start Date, End Date (default: today, today + settings.default_project_duration_months)
- Create → `POST /api/projects`

### 14.2 Project Detail (`/projects/:id`)
**6 Tabs:**

**Overview Tab:**
- PI (avatar + name + department)
- Dates: Start → End (duration in months)
- Budget: Total with allocation bars (spent vs allocated)
- Proposal link (clickable → proposal detail)
- Milestone summaries: each with progress bar (tasks done/total)
- Recent expenses: last 5 expenses in a mini table

**Milestones Tab:**
- Milestone cards ordered by `display_order`:
  - Title, Status badge (from lookup), Due date (turns red if overdue), Description, Progress bar
  - Click to expand → shows tasks table
- Tasks table columns: Title, Assigned to (avatar + name), Due date, Status (inline toggle: not_started → in_progress → done), Estimated hours, Actual hours
- Inline status toggle: clicking the status badge cycles through the 3 statuses, auto-saves via `PUT /api/tasks/:id`
- Add Milestone button → modal: Title, Description, Due Date, Display Order
- Add Task button → modal: Title, Description, Assigned To (user dropdown), Due Date, Estimated Hours

**Expenses Tab:**
- Summary bar: Total Budget | Total Approved | Remaining | % Used (turns red if > 90%)
- Expenses table: Date, Amount (ETB), Category badge, Description, Approved by (name + avatar), Status
- Add Expense button → modal: Amount, Category dropdown (personnel/equipment/travel/other), Description, Receipt upload
- Approve button (visible to finance_officer): `PUT /api/expenses/:id/approve`

**Outputs Tab:**
- Lists outputs linked to this project
- Link Output button → modal with output search and select

**Files Tab:**
- FileUpload component
- File list with download/delete

**Timeline Tab:**
- Gantt chart (ApexCharts): Milestones as bars, Tasks as sub-bars, positioned by start date and duration
- Today marker (vertical red line)

---

## 15. PUBLICATIONS MODULE (Separate from Outputs)

### 15.1 Publications List (`/publications`)
**Data Fetching:** `GET /api/publications?page=X&search=Y&year=Z`

**Filters:** Search (title, journal, keywords, author), Year dropdown, Project dropdown

**Table:**
- Title (clickable), Authors (first 3 + "et al."), Journal, Date, DOI link, Citation count, Keywords chips, PDF download (if public)

**Create/Edit Publication Modal:**
- Title (required), Journal (required), Abstract, Keywords (tag input), DOI, Scholar URL, Publication Date (required), Citation Count, Project dropdown (optional), File upload (PDF)
- **Authors Section:**
  - Current authors list with order numbers and remove button
  - "Add Author":
    - Toggle: "Existing Researcher" / "External Author"
    - If existing: user search from `GET /api/users`
    - If external: Name input, Institution input
  - Author order can be changed by drag-and-drop or up/down buttons
- Create → `POST /api/publications`
- Edit → `PUT /api/publications/:id`

**Delete:** Confirmation → `DELETE /api/publications/:id`

### 15.2 Publication Detail (`/publications/:id`)
- Full metadata display
- Authors list with order numbers
- PDF download
- Linked project (clickable)
- External DOI link

---

## 16. EVENTS MANAGEMENT

### 16.1 Events List (`/events`)
**Tabs:** Upcoming / Past / All / My Registered

**"My Registered" tab:** `GET /api/events?registered_by=me`

**Cards:** Date badge, Image, Title, Venue, Date/Time, Capacity bar, Registration status

**Register Button Logic:**
```
If user already registered:
    Show "Registered ✅" (disabled)
Else if registration deadline has passed:
    Show "Registration Closed" (disabled)
Else if capacity reached:
    Show "Event Full" (disabled)
Else:
    Show "Register" button → POST /api/events/:id/register
```

**Admin Actions:** Create, Edit, Delete

**Create/Edit Event Modal:**
- Title, Start Date/Time, End Date/Time, Venue, Description, Capacity (optional), Registration Deadline (optional, must be before start date), Image upload (optional)

### 16.2 Event Detail (`/events/:id`)
- Full event information
- Registration button with same logic as above
- **Admin: Attendance Tab:**
  - Table of registered users: Name, Email, Registration Date, Attended checkbox
  - Save Attendance → `PUT /api/events/:id/attendance` with `user_id` and `attended` boolean
- **Admin: Certificates Tab:**
  - "Generate All Certificates" button → `POST /api/events/:id/certificates`
  - Per-user certificate download links

---

## 17. PARTNERS MANAGEMENT

### 17.1 Partners List (`/partners`)
**Data Fetching:** `GET /api/partners?page=X`

**Cards:** Name, Sector badge, Email (mailto link), Website (external link), MoU count

**Create/Edit Partner Modal:**
- Name, Sector, Contact Email, Website
- Create → `POST /api/partners`
- Edit → `PUT /api/partners/:id`

**Delete:** Confirmation → `DELETE /api/partners/:id`

### 17.2 Partner Detail (`/partners/:id`)
- Full partner information
- MoUs list: Start Date, End Date, Status (active/expired/expiring soon based on dates), Agreement file download
- Add/Edit MoU modal: Start Date, End Date, File upload
- Linked outputs (internships where this partner is linked)

---

## 18. OUTPUTS MODULE (Student + Research Center)

### 18.1 Key Distinction: Outputs vs Publications
- **Outputs** are all produced works: theses, dissertations, internship reports, semester projects, final year projects, research papers (pre-publication), datasets, reports, patents
- **Publications** are published papers in journals/conferences with DOI, citation count, and formal author lists
- An output CAN later become a publication, but they are separate records in the system

### 18.2 Student Output Types by Level
| Student Level | Available Output Subtypes |
|---------------|--------------------------|
| Undergraduate (Bachelor) | internship, semester_project, final_year_project |
| Graduate (MSc) | thesis, research_paper, dataset, report |
| PhD | thesis (dissertation), research_paper, dataset, report |

### 18.3 Student Output Workflow
```
draft → submitted → approved_by_supervisor → approved → (visible in repository)
                       ↓                        ↓
                   rejected                  rejected
```
- Student creates → status = draft
- Student submits → status = submitted
- Supervisor reviews → status = approved_by_supervisor OR rejected
- Department Head reviews → status = approved OR rejected

### 18.4 Research Center Output Workflow
```
draft → submitted → approved → (visible in repository)
              ↓
          rejected
```
- Researcher creates → status = draft
- Researcher submits → status = submitted
- Admin or Director reviews → status = approved OR rejected

### 18.5 Output Participants (Student Outputs Only)
| Participant Type | Description |
|-----------------|-------------|
| student | The primary student author |
| co_student | Additional student (for group projects) |
| supervisor | Main academic supervisor |
| co_supervisor | Additional supervisor |
| advisor | Academic advisor |

### 18.6 Outputs List (`/outputs`)
**Scoping:** API handles scope based on user role. Student sees own outputs. Supervisor sees outputs where they are listed as supervisor. Department head sees all outputs in their department.

**Filters (Dynamic by Category Selection):**
- Category radio/dropdown: All / Student / Research Center (from lookup `output_categories`)
- **If "Student" selected:** Show Student Level dropdown (from lookup `student_levels`)
- Subtype dropdown (from lookup `output_subtypes`) — **should be filtered by selected level** but since API handles this, frontend just sends the selected value
- Status dropdown (from lookup `output_statuses`)
- Academic Year dropdown
- Search: title, abstract, student name

**Table Columns (Dynamic by Category):**
| Column | Student Output | Research Center Output |
|--------|---------------|----------------------|
| Title (clickable) | ✅ | ✅ |
| Category badge | "Student" | "Research Center" |
| Level badge | UG/Grad/PhD | — |
| Subtype badge | Internship/Thesis/etc. | Paper/Dataset/etc. |
| Author | Student name | Researcher name |
| Supervisor | Supervisor name | — |
| Department | ✅ | ✅ |
| Status badge | ✅ (with workflow) | ✅ (simpler workflow) |
| Date | Start-End or Created | Start-End or Created |

**Action Buttons (Contextual):**
| Role | Conditions | Actions |
|------|-----------|---------|
| Student (owner) | status = draft | Edit, Submit, Delete |
| Student (owner) | status = submitted, approved, rejected | View only |
| Supervisor | status = submitted, user is listed as supervisor | Approve, Reject |
| Department Head | status = approved_by_supervisor, output belongs to their dept | Final Approve, Reject |
| Admin | Any status | Edit, Override Status, Delete |

**"New Output" Button:** Visible to student, researcher, research_admin, super_admin

### 18.7 Create Output (`/outputs/create`)
**Step 1 - Select Category:**
- Radio buttons: "Student Output" / "Research Center Output" (from lookup `output_categories`)

**If "Student Output" selected:**
- **Student Level:** Dropdown from `GET /api/lookups/student_levels` — Undergraduate, Graduate, PhD
- **Subtype:** Dropdown from `GET /api/lookups/output_subtypes`
  - **Frontend filtering logic:**
    ```
    If student_level = 'undergraduate':
        Show subtypes: internship, final_year_project, semester_project
    If student_level = 'graduate':
        Show subtypes: thesis, research_paper, dataset, report
    If student_level = 'phd':
        Show subtypes: thesis, research_paper, dataset, report
    ```
- **Title** (required)
- **Abstract** (required)
- **Academic Year** (dropdown from `GET /api/academic-years`)
- **Start Date, End Date** (optional)
- **Participants Section:**
  - "Add Participant" button
  - Each participant row: User search dropdown (`GET /api/users`), Participant Type dropdown (from lookup `participant_types`)
  - At minimum: 1 student + 1 supervisor required before submission
  - Remove button per participant
- **File Upload:** The actual thesis/project document

**If "Research Center Output" selected:**
- **Subtype:** Dropdown: research_paper, dataset, report, patent
- **Title** (required)
- **Abstract** (required)
- **Project** (optional, dropdown from `GET /api/projects`)
- **Proposal** (optional, dropdown from `GET /api/proposals`)
- **Partner** (optional, dropdown from `GET /api/partners`)
- **Budget** (optional)
- **Academic Year, Dates** (optional)
- **File Upload**

**Common:**
- Keywords (tag input)
- "Save as Draft" → `POST /api/outputs` with status = draft
- "Submit" → `POST /api/outputs` with status = draft, then `POST /api/outputs/:id/status` with status = submitted

### 18.8 Output Detail (`/outputs/:id`)
**Student Output Display:**
- Header: Title, Status badge with workflow timeline (showing all past statuses with dates)
- Level badge + Subtype badge
- Student name + avatar
- Supervisor(s): name + avatar + participant type badge
- Co-supervisor(s), Advisor(s) if any
- Abstract
- File download
- Academic Year, Start Date, End Date
- Feedback from supervisor (if any, displayed in a quote box)

**Research Center Output Display:**
- Header: Title, Status badge
- Subtype badge
- Author(s): name + avatar
- Research Center or Department
- Abstract
- File download
- Linked Project (clickable), Linked Proposal (clickable), Partner, Budget

**Action Buttons (Contextual, same logic as list page but with more detail):**
```
If student (owner) AND status = 'draft':
    [Edit] [Submit] [Delete]
If supervisor AND status = 'submitted' AND user is listed as supervisor:
    [Approve] [Reject] (opens modal with comment textarea)
If department_head AND status = 'approved_by_supervisor' AND output belongs to dept:
    [Final Approve] [Reject]
If admin:
    [Change Status] (dropdown with all statuses) [Edit] [Delete]
```

**Status History Timeline:**
- Vertical timeline showing each status change: date, status badge, changed by (name), comment (if any)

---

## 19. PATENTS & LICENSES

### 19.1 Patents List (`/patents`)
**Data Fetching:** `GET /api/patents?page=X&status=Y`

**Filters:** Status dropdown from lookup `patent_statuses`

**Table:** Title, Inventors, Filing Date, Patent Number, Status badge, Project link

**Create/Edit Patent Modal:**
- Title, Inventors (text, comma-separated), Filing Date, Patent Number, Status dropdown, Project dropdown (optional)
- Create → `POST /api/patents`
- Edit → `PUT /api/patents/:id`

### 19.2 Patent Detail (`/patents/:id`)
- Full patent information
- Licenses list: Company, Start Date, End Date, Royalty Rate, Status (active/expired)
- Add/Edit License modal: Company Name, Start Date, End Date, Royalty Rate (%)

---

## 20. COMMUNITY PROBLEMS

### 20.1 Community Problems List (`/community`)
**Data Fetching:** `GET /api/community-problems?page=X&status=Y`

**Tabs:** Open / Claimed / Completed (from lookup `community_problem_statuses`)

**Filters:** Search (title, location), University, Location

**Cards:**
- Title, Location with pin icon, Status badge, Description (3 lines)
- Submitted by: name or "Anonymous" (with ghost icon), date
- If claimed: "Being solved by [Researcher Name]" with avatar
- If completed: "Solved by [Researcher Name]" + Rating stars (if feedback given) + Feedback text

**Action Buttons (Contextual):**
```
If status = 'open' AND user has role 'researcher' or 'admin':
    [Claim] → POST /api/community-problems/:id/claim
If status = 'claimed' AND user is the claimant:
    [Mark Complete] → opens modal with optional project link → POST /api/community-problems/:id/complete
If status = 'completed':
    [Add Feedback] → opens modal with textarea + StarRating → POST /api/community-problems/:id/feedback
```

**Submit Problem Modal:**
- Title (required), Description (required), Location (required), Contact Info (optional), Anonymous toggle
- `POST /api/community-problems`

---

## 21. REPORTS

### 21.1 Reports Page (`/reports`)
**Access:** super_admin, research_admin, director, department_head, finance_officer

**Generate Report Section:**
- Report Name (text input)
- Report Type (dropdown): Projects, Outputs, Publications, Expenses, Community
- Filters (dynamic based on type):
  - Projects: Status, Academic Year, Department, Date Range
  - Outputs: Category, Level, Subtype, Status, Academic Year, Date Range
  - Publications: Year, Project, Author
  - Expenses: Project, Category, Date Range
  - Community: Status, Location, Date Range
- Preview button: fetches data based on filters and shows preview table
- Generate PDF button → `POST /api/reports/generate`

**Previous Reports Table:**
- Name, Generated by, Date, Download button, Delete button
- Download → `GET /api/reports/:id/download`

---

## 22. SETTINGS (Super Admin Only)

### 22.1 Settings Page (`/settings`)
**Data Fetching:** `GET /api/settings`

**Grouped Display:**
- General: app_name, default_language, app_description, contact_email, contact_phone, contact_address
- Research: max_proposal_budget, min_proposal_budget, ethics_required, auto_approve_below_budget, default_project_duration_months
- Review: max_reviewers_per_proposal, min_reviewers_per_proposal, proposal_review_deadline_days
- Plagiarism: plagiarism_threshold
- Files: max_file_upload_size_mb, allowed_file_types
- Registration: allow_public_registration, require_email_verification, allow_public_problem_submission
- Notifications: enable_notifications

**Editing:**
- Each row has: Key (read-only), Value (editable input of appropriate type: text, number, boolean toggle, textarea), Description (read-only), Save button
- Boolean settings use toggle switch that saves immediately on change
- Other settings: click Edit → input becomes editable → Save → `PUT /api/settings/:id`
- Undo toast appears for 5 seconds after save

---

## 23. USER MANAGEMENT

### 23.1 Users List (`/users`)
**Access:** super_admin (all users), research_admin (users in their university only)

**Filters:** Search (name, email), Role dropdown (from `/api/roles`), Department dropdown, University dropdown, Status (active/inactive)

**Table:** Avatar, Name, Email, Department, University, Roles (badges), Status (green dot = active, gray dot = inactive), Actions

**Actions:** View (→ detail), Edit (opens modal), Assign Roles (opens modal), Deactivate/Reactivate (confirmation)

**Create User Modal:**
- Name, Email, Password, Department dropdown, University dropdown
- Create → `POST /api/users`

**Edit User Modal:**
- Name, Email, Department dropdown, Bio
- Update → `PUT /api/users/:id`

**Assign Roles Modal:**
- Checkbox list of all roles from `GET /api/roles`
- Currently assigned roles pre-checked
- Save → `POST /api/users/:id/roles` with `role_ids` array

**Deactivate/Reactivate:**
- Deactivate: confirmation → `PUT /api/users/:id` with `is_active = false`
- Reactivate: confirmation → `PUT /api/users/:id` with `is_active = true`

### 23.2 User Detail (`/users/:id`)
**Tabs:**

**Profile Tab:**
- Avatar, Name, Email, Department→Faculty→Campus→University chain
- Bio, ORCID, Google Scholar, Scopus, LinkedIn
- Member since date
- Status (active/inactive)

**Roles Tab:**
- List of assigned roles with descriptions
- "Assign Roles" button

**Research Centers Tab:**
- List of research center memberships with center names and center role badges
- "Assign to Research Center" button → modal: center dropdown + center role dropdown

**Expertise Tab:**
- Expertise chips
- "Add Expertise" button → modal: search/select from `/api/expertise`
- Remove button per expertise chip

---

## 24. ROLES & PERMISSIONS (Super Admin Only)

### 24.1 Roles List (`/roles`)
**Table:** Name, Description, User count, Permission count
**Create/Edit Modal:** Name, Description
**Delete:** Confirmation (not allowed for roles with users)

**Sync Permissions Modal:**
- Opens on "Permissions" button per role
- Checkboxes grouped by category (User Management, Proposal Management, etc.)
- Current permissions pre-checked
- Save → `POST /api/roles/:id/permissions` with `permissions` array

### 24.2 Permissions List (`/permissions`)
**Table:** Name, Description
**Create/Edit Modal:** Name, Description
**Delete:** Confirmation

---

## 25. ADMIN HIERARCHY TABLES

All follow identical CRUD pattern:

| Page | API Endpoint | Parent Field | Parent Endpoint |
|------|-------------|-------------|-----------------|
| Universities | `/api/universities` | None | None |
| Campuses | `/api/campuses` | university_id | `/api/universities` |
| Faculties | `/api/faculties` | campus_id | `/api/campuses` |
| Departments | `/api/departments` | faculty_id | `/api/faculties` |
| Research Centers | `/api/research-centers` | parent_university_id, parent_campus_id, parent_faculty_id | All three |
| Academic Years | `/api/academic-years` | None | None |
| Review Criteria | `/api/review-criteria` | None | None |
| Expertise | `/api/expertise` | None | None |

**Each page has:**
- Table with columns: Name, Code (if applicable), Parent (if applicable), Actions
- Search input
- "Add New" button → modal with form
- Edit button per row → modal pre-filled
- Delete button per row → confirmation dialog

**Cascading Logic for Parent Dropdowns:**
- Campuses: University dropdown filters campus list
- Faculties: University dropdown → Campus dropdown (filtered) → Faculty list
- Departments: University → Campus → Faculty (filtered) → Department list

---

## 26. WORKFLOW PAGES

### 26.1 Finance Checks (`/finance-checks`)
**Access:** finance_officer, super_admin, research_admin

**List:** Proposals with status = finance_check
**Table:** Proposal title (link), Budget, Submitted by, Checker (if assigned), Status badge, Date
**Actions:** Approve/Reject (opens modal with comment textarea)

### 26.2 Ethics Requests (`/ethics-requests`)
**Access:** ethics_officer, super_admin, research_admin

**List:** Proposals with pending ethics requests
**Table:** Proposal title (link), Status badge, Version, Generated PDF (download), Date
**Actions:** Approve/Reject (opens modal with comment textarea)

### 26.3 Detection Requests (`/detection-requests`)
**Access:** super_admin, research_admin

**List:** All originality checks
**Table:** Document name, Service badge, Status badge, Similarity %, AI %, Requested by, Date
**View:** Shows full detection results (similarity gauge, AI gauge, raw response)

---

## 27. FILE REPOSITORY

### 27.1 File Repository (`/files`)
**Data Fetching:** `GET /api/files?page=X`

**Upload Zone:** Drag & drop or click to browse. Accept all file types. Max size from settings.

**Table:** File name (icon by extension), Uploaded by (avatar + name), Upload date, Size (formatted: KB/MB/GB), Public/Private toggle, Version number, Actions (Download, Upload New Version, Delete)

**Filters:** All Files / Public / Private / My Files

**Public/Private Toggle:** Switches immediately → `PUT /api/files/:id` with `is_public`

**Upload New Version:** Opens file picker → uploads → increments version

**Delete:** Confirmation → `DELETE /api/files/:id`

---

## 28. NOTIFICATIONS

### 28.1 Notifications Page (`/notifications`)
**Data Fetching:** `GET /api/notifications?page=X`

**Tabs:** All / Unread (filtered by `read_at = null`)

**List:**
- Each notification: Icon (by type), Message text, Time ago (relative), Blue dot (if unread)
- Click on notification: marks as read (`PUT /api/notifications/:id/read`), navigates to related page if applicable

**"Mark All Read" Button:** `PUT /api/notifications/read-all` (or loop through all unread)

**Unread Count in Sidebar:** Fetched on mount and refreshed every 60 seconds

---

## 29. AUDIT LOGS

### 29.1 Audit Logs (`/audit-logs`)
**Access:** super_admin (all), research_admin (university-scoped)

**Filters:** User dropdown, Table name dropdown, Action dropdown, Date range

**Table:** Timestamp, User (avatar + name), Action (color badge: create=green, update=blue, delete=red), Table name, Record ID, IP address

**Export CSV:** Downloads filtered results as CSV file

---

## 30. PROFILE PAGE

### 30.1 Profile (`/profile`)
**Data Fetching:** `GET /api/user`

**Profile Section:**
- Avatar (initials or uploaded image)
- Name, Email, Department chain, Roles badges, Member since date

**Edit Profile Form:**
- Name, ORCID, Google Scholar, Scopus, LinkedIn, Bio
- Profile image upload
- Save → `PUT /api/users/:id` (update own profile)

**Change Password Form:**
- Current Password, New Password, Confirm New Password
- Save → `POST /api/change-password`

**Language Preference:**
- Radio: English / አማርኛ
- Changes save immediately via `PUT /api/language-preference`

**Danger Zone:**
- "Deactivate Account" button → confirmation dialog → `PUT /api/users/:id` with `is_active = false` → logout

---

## 31. ERROR PAGES

### 31.1 Not Found (404)
- Shown when route doesn't match any defined route
- "Page Not Found" message
- "Go to Homepage" button → `/`

### 31.2 Forbidden (403)
- Shown when user tries to access a page they don't have permission for
- "Access Denied" message
- "You don't have the required permissions to view this page."
- "Go to Dashboard" button → `/dashboard`

### 31.3 Server Error (500)
- Shown when API returns a 500 error
- "Something went wrong" message
- "Please try again later."
- "Contact Support" button (links to contact email from settings)

---

## 32. IMPLEMENTATION ORDER

| Phase | What to Build | Depends On |
|-------|---------------|------------|
| 1 | Project setup, API service, Auth store, Notification store, Lookup store, Composables | Nothing |
| 2 | All 25 shared components | Phase 1 |
| 3 | PublicLayout, PublicNavbar, PublicFooter | Phase 2 |
| 4 | Public Homepage | Phase 3 + Public API endpoints |
| 5 | Login, Register, Forgot/Reset Password | Phase 1 |
| 6 | MainLayout, AppSidebar, AppTopBar | Phase 2 + Phase 5 |
| 7 | Dashboard (all role variants) | Phase 6 |
| 8 | Proposals (list, create 4-step, detail with all tabs and modals) | Phase 6 |
| 9 | Reviewer views (list, blind review) | Phase 6 |
| 10 | Calls, Projects (with milestones, tasks, expenses, timeline) | Phase 6 |
| 11 | Publications (separate from outputs) | Phase 6 |
| 12 | Events, Partners | Phase 6 |
| 13 | Outputs (student workflow, research center workflow, level-based subtypes, participants) | Phase 6 |
| 14 | Patents, Community Problems | Phase 6 |
| 15 | Reports, Settings | Phase 6 |
| 16 | User Management, Roles, Permissions | Phase 6 |
| 17 | Admin hierarchy tables (8 CRUD pages) | Phase 6 |
| 18 | Workflow pages (Finance, Ethics, Detection), File Repository, Notifications, Audit Logs, Profile | Phase 6 |
| 19 | Error pages (404, 403, 500) | Phase 1 |
| 20 | Public sub-pages (Calls, Events, Publications, Researchers, Community) | Phase 3 |
| 21 | Final testing all workflows end-to-end, responsive testing, edge case handling | All |

---

**This is the complete, unabridged Frontend Functional Specification covering every feature, workflow, rule, permission, data flow, and API integration. Nothing is omitted.**
