<?php

/*
|--------------------------------------------------------------------------
| RDRIMS API Architecture
|--------------------------------------------------------------------------
|
| Public Routes
|     │
|     ▼
| Authentication
|     │
|     ▼
| auth:sanctum
|     │
| tenant middleware
|     │
| role middleware
|     │
| Controller
|     │
| Policy
|     │
| Service
|     │
| Resource
|
| Tenant isolation is enforced by:
| • Tenant middleware
| • Authorization policies
| • Controller query filtering
| • Model scopes (where applicable)
*/

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| This file defines all API endpoints for RDRIMS.
|
| Architecture:
| - Public routes: Accessible without authentication
| - Authenticated routes: Protected using Sanctum authentication
| - Tenant middleware: Ensures university-level data isolation
| - Role middleware: Controls access based on user roles
*/

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AgreementFileController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\CommunityProblemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DetectionController;
use App\Http\Controllers\EthicsRequestController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpertiseController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FinanceCheckController;
use App\Http\Controllers\FundingController;
use App\Http\Controllers\FundingExpenseController;
use App\Http\Controllers\LanguagePreferenceController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\MoUController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OutputController;
use App\Http\Controllers\OutputFileController;
use App\Http\Controllers\OutputParticipantController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PatentController;
use App\Http\Controllers\PatentFileController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\ProjectInvestigatorController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ProposalFileController;
use App\Http\Controllers\ProposalInvestigatorController;
use App\Http\Controllers\ProposalReviewerController;
use App\Http\Controllers\PublicationAuthorController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResearchCenterController;
use App\Http\Controllers\ReviewCriterionController;
use App\Http\Controllers\ReviewerProposalController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\InstitutionSettingController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ThematicAreaController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserExpertiseController;
use App\Http\Controllers\UserResearchCenterController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ProjectExpenseController;

use Illuminate\Support\Facades\Route;

// ============================================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================================

// ---------------------------------------------------------------------------
// Authentication (public entry points)
// ---------------------------------------------------------------------------
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

// ---------------------------------------------------------------------------
// System Health (monitoring)
// ---------------------------------------------------------------------------
Route::get('system/health', [HealthController::class, 'ping']);

// ---------------------------------------------------------------------------
// Public Settings & Lookup Data
// ---------------------------------------------------------------------------
Route::get('lookups/{table}', [LookupController::class, 'index']);
Route::get('settings', [SettingController::class, 'index']);
Route::post('email-config/test', [\App\Http\Controllers\EmailConfigurationController::class, 'testEmail']);

// ---------------------------------------------------------------------------
// Public Academic Organization Data
// ---------------------------------------------------------------------------
Route::get('universities', [UniversityController::class, 'index']);
Route::get('universities/{university}', [UniversityController::class, 'show']);
Route::get('campuses', [CampusController::class, 'index']);
Route::get('faculties', [FacultyController::class, 'index']);
Route::get('departments', [DepartmentController::class, 'index']);
Route::get('departments/{department}', [DepartmentController::class, 'show']);
Route::get('research-centers', [ResearchCenterController::class, 'index']);
Route::get('research-centers/{research_center}', [ResearchCenterController::class, 'show']);

// ---------------------------------------------------------------------------
// Public Calls (Research Funding Announcements)
// ---------------------------------------------------------------------------
Route::get('calls', [CallController::class, 'index']);
Route::get('calls/{call}', [CallController::class, 'show']);

// ---------------------------------------------------------------------------
// Public Events (Conferences, Workshops)
// ---------------------------------------------------------------------------
Route::get('events', [EventController::class, 'index']);
Route::get('events/{event}', [EventController::class, 'show']);

// ---------------------------------------------------------------------------
// Public Publications
// ---------------------------------------------------------------------------
Route::get('publications', [PublicationController::class, 'index']);
Route::get('publications/statistics', [PublicationController::class, 'statistics'])
    ->middleware(['auth:sanctum', 'tenant']);
Route::get('publications/{publication}', [PublicationController::class, 'show']);
Route::get('student-outputs', [OutputController::class, 'publicIndex']);

// ---------------------------------------------------------------------------
// Community Problems (Public Submission)
// ---------------------------------------------------------------------------
Route::get('community-problems', [CommunityProblemController::class, 'index']);
Route::get('community-problems/{community_problem}', [CommunityProblemController::class, 'show']);
Route::post('community-problems', [CommunityProblemController::class, 'store']);
Route::get('public/research-centers', [ResearchCenterController::class, 'publicOptions']);

// ---------------------------------------------------------------------------
// Public Researchers Directory
// ---------------------------------------------------------------------------
Route::get('public/researchers', [UserController::class, 'publicIndex']);
Route::get('public/researchers/{user}', [UserController::class, 'publicShow']);

// ============================================================================
// AUTHENTICATED ROUTES (Require auth:sanctum + tenant middleware)
// ============================================================================
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {

    // -----------------------------------------------------------------------
    // Authentication & User Profile
    // -----------------------------------------------------------------------
    Route::get('user', [AuthController::class, 'user']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::post('profile/complete', [AuthController::class, 'completeProfile']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Authenticated call/publication views (tenant‑aware)
    Route::get('management/calls', [CallController::class, 'index']);
    Route::get('management/calls/{call}', [CallController::class, 'show']);
    Route::get('management/publications', [PublicationController::class, 'index']);
    Route::get('management/publications/{publication}', [PublicationController::class, 'show']);
    Route::get('management/research-centers/options', [ResearchCenterController::class, 'hierarchyOptions']);
    Route::get('management/research-centers', [ResearchCenterController::class, 'index']);

    // -----------------------------------------------------------------------
    // File Management (Central Repository)
    // -----------------------------------------------------------------------
    Route::post('files', [FileController::class, 'upload']);
    Route::get('files', [FileController::class, 'index']);
    Route::put('files/{file}', [FileController::class, 'update']);
    Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::delete('files/{file}', [FileController::class, 'destroy']);
    Route::get('files/{file}/versions', [FileController::class, 'versions']);
    Route::post('files/{file}/versions', [FileController::class, 'uploadNewVersion']);

    // -----------------------------------------------------------------------
    // Language Preference
    // -----------------------------------------------------------------------
    Route::get('language-preference', [LanguagePreferenceController::class, 'show']);
    Route::put('language-preference', [LanguagePreferenceController::class, 'update']);

    // -----------------------------------------------------------------------
    // Notifications
    // -----------------------------------------------------------------------
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // -----------------------------------------------------------------------
    // Audit Logs (Only for privileged roles)
    // -----------------------------------------------------------------------
    Route::get('audit-logs', [AuditLogController::class, 'index'])
        ->middleware('role:super_admin,research_admin,campus_admin,faculty_admin,department_head,director');

    // -----------------------------------------------------------------------
    // Super Admin System Management
    // -----------------------------------------------------------------------
    Route::middleware('role:super_admin')->group(function () {
        Route::get('system-health', [HealthController::class, 'index']);
        Route::get('email-config', [\App\Http\Controllers\EmailConfigurationController::class, 'show']);
        Route::post('email-config', [\App\Http\Controllers\EmailConfigurationController::class, 'update']);
    });

    // -----------------------------------------------------------------------
    // Roles Listing (for frontend role selection)
    // -----------------------------------------------------------------------
    Route::get('roles', [RoleController::class, 'index']);

    // -----------------------------------------------------------------------
    // Academic Hierarchy Management (CRUD – protected by roles)
    // -----------------------------------------------------------------------
    Route::apiResource('universities', UniversityController::class)
        ->except(['index', 'show'])
        ->middleware('role:super_admin');

    Route::apiResource('campuses', CampusController::class)
        ->except(['index'])
        ->middleware('role:super_admin,research_admin,campus_admin');

    Route::apiResource('faculties', FacultyController::class)
        ->except(['index'])
        ->middleware('role:super_admin,research_admin,campus_admin,faculty_admin');

    Route::apiResource('departments', DepartmentController::class)
        ->except(['index', 'show'])
        ->middleware('role:super_admin,research_admin,campus_admin,faculty_admin');

    Route::apiResource('research-centers', ResearchCenterController::class)
        ->except(['index', 'show'])
        ->middleware('role:super_admin,research_admin,campus_admin,faculty_admin');

    // Academic Years
    Route::get('academic-years', [AcademicYearController::class, 'index']);
    Route::apiResource('academic-years', AcademicYearController::class)
        ->except(['index'])
        ->middleware('role:super_admin,research_admin');
    Route::post('academic-years/{academic_year}/set-current', [AcademicYearController::class, 'setCurrent'])
        ->middleware('role:super_admin,research_admin');

    // -----------------------------------------------------------------------
    // User Management
    // -----------------------------------------------------------------------
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/roles', [UserRoleController::class, 'assign']);
    Route::delete('users/{user}/roles/{role}', [UserRoleController::class, 'revoke']);
    Route::post('users/{user}/research-centers', [UserResearchCenterController::class, 'attach']);
    Route::delete('users/{user}/research-centers/{research_center}', [UserResearchCenterController::class, 'detach']);
    Route::post('users/{user}/expertise', [UserExpertiseController::class, 'attach']);
    Route::delete('users/{user}/expertise/{expertise}', [UserExpertiseController::class, 'detach']);

    // -----------------------------------------------------------------------
    // Platform Roles & Permissions (Super Admin only)
    // -----------------------------------------------------------------------
    Route::prefix('admin')->middleware('role:super_admin')->group(function () {
        Route::get('roles', [\App\Http\Controllers\Admin\RoleController::class, 'index']);
        Route::post('roles', [\App\Http\Controllers\Admin\RoleController::class, 'store']);
        Route::put('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update']);
        Route::delete('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy']);
        Route::post('roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'syncPermissions']);
        Route::get('permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'index']);
        Route::post('permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'store']);
        Route::put('permissions/{permission}', [\App\Http\Controllers\Admin\PermissionController::class, 'update']);
        Route::delete('permissions/{permission}', [\App\Http\Controllers\Admin\PermissionController::class, 'destroy']);
    });

    // -----------------------------------------------------------------------
    // Institution Role Overrides
    // -----------------------------------------------------------------------
    Route::prefix('institution')
        ->middleware('role:research_admin,campus_admin,faculty_admin')
        ->group(function () {
            Route::get('roles', [\App\Http\Controllers\Institution\RoleController::class, 'index']);
            Route::post('roles', [\App\Http\Controllers\Institution\RoleController::class, 'store']);
            Route::get('roles/{role}/permissions', [\App\Http\Controllers\Institution\RoleController::class, 'permissions']);
            Route::post('roles/{role}/permissions', [\App\Http\Controllers\Institution\RoleController::class, 'syncOverrides']);
            Route::get('permissions', [\App\Http\Controllers\Institution\PermissionController::class, 'index']);
        });

    // -----------------------------------------------------------------------
    // System Settings (Super Admin)
    // -----------------------------------------------------------------------
    Route::post('settings/bulk', [SettingController::class, 'bulk'])->middleware('role:super_admin');
    Route::post('settings', [SettingController::class, 'store'])->middleware('role:super_admin');
    Route::put('settings/{setting}', [SettingController::class, 'update'])->middleware('role:super_admin');
    Route::delete('settings/{setting}', [SettingController::class, 'destroy'])->middleware('role:super_admin');

    // -----------------------------------------------------------------------
    // Institution Settings
    // -----------------------------------------------------------------------
    Route::apiResource('institution-settings', InstitutionSettingController::class)
        ->middleware('role:research_admin,campus_admin,faculty_admin,department_head,director');

    // -----------------------------------------------------------------------
    // Thematic Areas
    // -----------------------------------------------------------------------
    Route::get('thematic-areas', [ThematicAreaController::class, 'index']);
    Route::post('thematic-areas', [ThematicAreaController::class, 'store'])->middleware('role:research_admin');
    Route::get('thematic-areas/{thematic_area}', [ThematicAreaController::class, 'show']);
    Route::put('thematic-areas/{thematic_area}', [ThematicAreaController::class, 'update'])->middleware('role:research_admin');
    Route::delete('thematic-areas/{thematic_area}', [ThematicAreaController::class, 'destroy'])->middleware('role:research_admin');

    // -----------------------------------------------------------------------
    // Expertise Management
    // -----------------------------------------------------------------------
    Route::get('expertise', [ExpertiseController::class, 'index']);
    Route::post('expertise', [ExpertiseController::class, 'store'])->middleware('role:super_admin,research_admin');
    Route::put('expertise/{expertise}', [ExpertiseController::class, 'update'])->middleware('role:super_admin,research_admin');
    Route::delete('expertise/{expertise}', [ExpertiseController::class, 'destroy'])->middleware('role:super_admin,research_admin');

    // -----------------------------------------------------------------------
    // Review Criteria
    // -----------------------------------------------------------------------
    Route::get('review-criteria', [ReviewCriterionController::class, 'index']);
    Route::post('review-criteria', [ReviewCriterionController::class, 'store'])->middleware('role:super_admin,research_admin');
    Route::put('review-criteria/{review_criterion}', [ReviewCriterionController::class, 'update'])->middleware('role:super_admin,research_admin');
    Route::delete('review-criteria/{review_criterion}', [ReviewCriterionController::class, 'destroy'])->middleware('role:super_admin,research_admin');

    // ========================================================================
    // RESEARCH MANAGEMENT MODULES
    // ========================================================================

    // -----------------------------------------------------------------------
    // Calls (Protected CRUD)
    // -----------------------------------------------------------------------
    Route::apiResource('calls', CallController::class)->except(['index', 'show']);

    // -----------------------------------------------------------------------
    // Proposals
    // -----------------------------------------------------------------------
    Route::apiResource('proposals', ProposalController::class);
    Route::post('proposals/{proposal}/submit', [ProposalController::class, 'submit']);
    Route::post('proposals/{proposal}/check', [ProposalController::class, 'runChecks']);
    Route::post('proposals/{proposal}/approve', [ProposalController::class, 'approve']);
    Route::post('proposals/{proposal}/reject', [ProposalController::class, 'reject']);
    Route::post('proposals/{proposal}/assign-reviewers', [ProposalController::class, 'assignReviewers']);
    Route::get('proposals/{proposal}/suggest-reviewers', [ProposalController::class, 'suggestReviewers']);
    Route::post('proposals/{proposal}/upload-document', [ProposalController::class, 'uploadDocument']);

    // Proposal Files
    Route::post('proposals/{proposal}/files', [ProposalFileController::class, 'attach']);
    Route::delete('proposals/{proposal}/files/{file}', [ProposalFileController::class, 'detach']);

    // Proposal Investigators
    Route::apiResource('proposals.investigators', ProposalInvestigatorController::class)
        ->only(['index', 'store', 'destroy']);

    // Proposal Reviewers
    Route::get('proposals/{proposal}/reviewers/recommendations', [ProposalReviewerController::class, 'recommendations']);
    Route::post('proposals/{proposal}/reviewers/{reviewer}/reopen', [ProposalReviewerController::class, 'reopen'])
        ->middleware('role:super_admin,research_admin');
    Route::apiResource('proposals.reviewers', ProposalReviewerController::class)
        ->only(['index', 'store', 'destroy']);

    // Reviewer Workspace
    Route::get('reviewer/proposals', [ReviewerProposalController::class, 'index']);
    Route::get('reviewer/proposals/{proposal}', [ReviewerProposalController::class, 'show']);
    Route::get('reviewer/proposals/{proposal}/template', [ReviewerProposalController::class, 'downloadTemplate']);
    Route::post('reviewer/proposals/{proposal}/import', [ReviewerProposalController::class, 'importReview']);
    Route::post('reviewer/proposals/{proposal}/review', [ReviewerProposalController::class, 'storeReview']);

    // Finance Checks
    Route::post('proposals/{proposal}/finance-checks', [FinanceCheckController::class, 'store']);
    Route::apiResource('finance-checks', FinanceCheckController::class)->only(['index', 'show', 'update']);
    Route::post('finance-checks/{financeCheck}/approve', [FinanceCheckController::class, 'approve']);
    Route::post('finance-checks/{financeCheck}/reject', [FinanceCheckController::class, 'reject']);

    // Ethics Requests
    Route::post('proposals/{proposal}/ethics-requests', [EthicsRequestController::class, 'store']);
    Route::apiResource('ethics-requests', EthicsRequestController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::post('ethics-requests/{ethicsRequest}/mark-submitted', [EthicsRequestController::class, 'markSubmitted']);
    Route::post('ethics-requests/{ethicsRequest}/approve', [EthicsRequestController::class, 'approve']);
    Route::post('ethics-requests/{ethicsRequest}/reject', [EthicsRequestController::class, 'reject']);
    Route::post('ethics-requests/{ethicsRequest}/request-revision', [EthicsRequestController::class, 'requestRevision']);

    // Detection Services
    Route::get('detection/requests', [DetectionController::class, 'index']);
    Route::post('detection/requests', [DetectionController::class, 'store']);
    Route::get('detection/requests/{id}', [DetectionController::class, 'show']);
    Route::post('detection/requests/{id}/complete', [DetectionController::class, 'complete']);
    Route::post('detection/requests/{id}/mark-reviewed', [DetectionController::class, 'markReviewed']);
    Route::post('detection/requests/{id}/retry', [DetectionController::class, 'retry']);
    Route::delete('detection/requests/{id}', [DetectionController::class, 'destroy']);
    Route::post('detection/requests/{id}/restore', [DetectionController::class, 'restore']);
    Route::get('detection/services', [DetectionController::class, 'services']);

    // ========================================================================
    // PROJECT MANAGEMENT
    // ========================================================================

    // -----------------------------------------------------------------------
    // Projects (Full CRUD + Workflow)
    // -----------------------------------------------------------------------
    Route::apiResource('projects', ProjectController::class);

    // Create project from approved proposal (two aliases for compatibility)
    Route::post('projects/create-from-proposal/{proposal}', [ProjectController::class, 'createFromProposal']);
    Route::post('proposals/{proposal}/create-project', [ProjectController::class, 'createFromProposal']);

    // Workflow endpoints
    Route::post('projects/{project}/submit', [ProjectController::class, 'submit']);
    Route::post('projects/{project}/approve', [ProjectController::class, 'approve']);
    Route::post('projects/{project}/reject', [ProjectController::class, 'reject']);
    Route::post('projects/{project}/suspend', [ProjectController::class, 'suspend']);
    Route::post('projects/{project}/reactivate', [ProjectController::class, 'reactivate']);
    Route::post('projects/{project}/complete', [ProjectController::class, 'complete']);
    // Legacy status change (kept for compatibility)
    Route::put('projects/{project}/status', [ProjectController::class, 'changeStatus']);

    // Analytics
    Route::get('projects/{project}/progress', [ProjectController::class, 'progress']);
    Route::get('projects/{project}/budget-stats', [ProjectController::class, 'budgetStats']);
    Route::get('projects/{project}/timeline', [ProjectController::class, 'timeline']);

    // Team management
    Route::post('projects/{project}/investigators', [ProjectController::class, 'addInvestigator']);
    Route::delete('projects/{project}/investigators/{investigatorId}', [ProjectController::class, 'removeInvestigator']);

    // -----------------------------------------------------------------------
    // Project Milestones & Tasks
    // -----------------------------------------------------------------------
    Route::apiResource('projects.milestones', MilestoneController::class);
    Route::apiResource('milestones.tasks', TaskController::class);
    Route::post('tasks', [TaskController::class, 'storeStandalone']);
    Route::put('tasks/{task}', [TaskController::class, 'update']);

    // -----------------------------------------------------------------------
    // Project Files
    // -----------------------------------------------------------------------
    Route::post('projects/{project}/files', [ProjectFileController::class, 'attach']);
    Route::delete('projects/{project}/files/{file}', [ProjectFileController::class, 'detach']);

    // -----------------------------------------------------------------------
    // Project Expenses
    // -----------------------------------------------------------------------
    Route::apiResource('projects.expenses', ExpenseController::class);
    Route::post('projects/{project}/expenses/{expense}/approve', [ExpenseController::class, 'approve']);

    // -----------------------------------------------------------------------
    // Project Investigators (dedicated resource)
    // -----------------------------------------------------------------------
    Route::apiResource('projects.investigators', ProjectInvestigatorController::class)
        ->only(['index', 'store', 'destroy']);

    // -----------------------------------------------------------------------
    // Funding Management (Grants & Budgets)
    // -----------------------------------------------------------------------
    Route::apiResource('fundings', FundingController::class);
    Route::post('fundings/{funding}/submit', [FundingController::class, 'submit']);
    Route::post('fundings/{funding}/approve', [FundingController::class, 'approve']);
    Route::post('fundings/{funding}/reject', [FundingController::class, 'reject']);
    Route::get('fundings/{funding}/budget-stats', [FundingController::class, 'budgetStats']);
    Route::apiResource('fundings.expenses', FundingExpenseController::class);
    Route::post('fundings/{funding}/expenses/{expense}/approve', [FundingExpenseController::class, 'approve']);
    Route::post('fundings/{funding}/expenses/{expense}/reject', [FundingExpenseController::class, 'reject']);

    // ========================================================================
    // RESEARCH OUTPUTS
    // ========================================================================

    Route::apiResource('outputs', OutputController::class);
    Route::post('outputs/{output}/submit', [OutputController::class, 'submit']);
    Route::post('outputs/{output}/verify', [OutputController::class, 'verify']);
    Route::post('outputs/{output}/approve', [OutputController::class, 'approve']);
    Route::post('outputs/{output}/reject', [OutputController::class, 'reject']);
    Route::post('outputs/{output}/publish', [OutputController::class, 'publish']);
    Route::post('outputs/{output}/status', [OutputController::class, 'changeStatus']);
    Route::get('outputs/subtypes-by-level', [OutputController::class, 'getSubtypesByLevel']);

    Route::apiResource('outputs.participants', OutputParticipantController::class)
        ->only(['index', 'store', 'destroy']);

    Route::post('outputs/{output}/files', [OutputFileController::class, 'attach']);
    Route::delete('outputs/{output}/files/{file}', [OutputFileController::class, 'detach']);

    // -----------------------------------------------------------------------
    // Patents & Licenses
    // -----------------------------------------------------------------------
    Route::apiResource('patents', PatentController::class);
    Route::apiResource('patents.licenses', LicenseController::class)->shallow();
    Route::post('patents/{patent}/files', [PatentFileController::class, 'attach']);
    Route::delete('patents/{patent}/files/{file}', [PatentFileController::class, 'detach']);

    // -----------------------------------------------------------------------
    // Partners & MoUs
    // -----------------------------------------------------------------------
    Route::apiResource('partners', PartnerController::class);
    Route::apiResource('partners.mo-us', MoUController::class)->shallow();

    // -----------------------------------------------------------------------
    // Agreement Files
    // -----------------------------------------------------------------------
    Route::get('agreement-files', [AgreementFileController::class, 'index']);
    Route::post('agreement-files', [AgreementFileController::class, 'attach']);
    Route::delete('agreement-files/{agreement_file}', [AgreementFileController::class, 'detach']);

    // ========================================================================
    // EVENTS
    // ========================================================================
    Route::apiResource('events', EventController::class)->except(['index', 'show']);
    Route::post('events/{event}/register', [EventRegistrationController::class, 'register']);
    Route::delete('events/{event}/registrations/{registration}', [EventRegistrationController::class, 'destroy']);
    Route::put('events/{event}/attendance', [EventRegistrationController::class, 'markAttendance']);
    Route::post('events/{event}/certificates', [EventRegistrationController::class, 'generateCertificate']);

    // ========================================================================
    // PUBLICATIONS (Protected CRUD)
    // ========================================================================
    Route::apiResource('publications', PublicationController::class)->except(['index', 'show']);
    Route::post('publications/{publication}/submit', [PublicationController::class, 'submit']);
    Route::post('publications/{publication}/verify', [PublicationController::class, 'verify']);
    Route::post('publications/{publication}/approve', [PublicationController::class, 'approve']);
    Route::post('publications/{publication}/reject', [PublicationController::class, 'reject']);
    Route::post('publications/{publication}/publish', [PublicationController::class, 'publish']);
    Route::post('publications/{publication}/update-citations', [PublicationController::class, 'updateCitations']);

    Route::apiResource('publications.authors', PublicationAuthorController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    // ========================================================================
    // COMMUNITY PROBLEMS (Protected CRUD)
    // ========================================================================
    Route::apiResource('community-problems', CommunityProblemController::class)
        ->except(['index', 'show', 'store']);
    Route::post('community-problems/{community_problem}/claim', [CommunityProblemController::class, 'claim']);
    Route::post('community-problems/{community_problem}/complete', [CommunityProblemController::class, 'complete']);
    Route::post('community-problems/{community_problem}/feedback', [CommunityProblemController::class, 'addFeedback']);

    // ========================================================================
    // REPORTING
    // ========================================================================
    Route::get('reports', [ReportController::class, 'index']);
    Route::get('reports/types', [ReportController::class, 'types']);
    Route::post('reports/generate', [ReportController::class, 'generate']);
    Route::get('reports/{report}/download', [ReportController::class, 'download']);
    Route::delete('reports/{report}', [ReportController::class, 'destroy']);

    // ========================================================================
    // GLOBAL SEARCH
    // ========================================================================
    Route::get('search', [SearchController::class, 'search']);

    // ========================================================================
    // DASHBOARD (Authenticated user summary)
    // ========================================================================
    Route::get('dashboard', [DashboardController::class, 'index']);
});
