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

use Illuminate\Support\Facades\Route;

// ============================================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================================

// ---------------------------------------------------------------------------
// Authentication (public entry points)
// ---------------------------------------------------------------------------
Route::post('register', [AuthController::class, 'register']);                // Create a new user account
Route::post('login', [AuthController::class, 'login']);                      // Authenticate and receive token
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);   // Request password reset link
Route::post('reset-password', [AuthController::class, 'resetPassword']);     // Reset password using token

// ---------------------------------------------------------------------------
// System Health (monitoring)
// ---------------------------------------------------------------------------
Route::get('system/health', [HealthController::class, 'ping']);              // Simple ping to verify API is running

// ---------------------------------------------------------------------------
// Public Settings & Lookup Data
// ---------------------------------------------------------------------------
Route::get('lookups/{table}', [LookupController::class, 'index']);           // Get values from any lookup table by name
Route::get('settings', [SettingController::class, 'index']);                 // List all public system settings
Route::post('email-config/test', [\App\Http\Controllers\EmailConfigurationController::class, 'testEmail']); // Test email configuration (public for testing)

// ---------------------------------------------------------------------------
// Public Academic Organization Data
// ---------------------------------------------------------------------------
Route::get('universities', [UniversityController::class, 'index']);          // List all universities
Route::get('universities/{university}', [UniversityController::class, 'show']); // Show a single university

// Hierarchy: Campus → Faculty → Department → Research Center
Route::get('campuses', [CampusController::class, 'index']);                  // List all campuses
Route::get('faculties', [FacultyController::class, 'index']);                // List all faculties
Route::get('departments', [DepartmentController::class, 'index']);           // List all departments
Route::get('departments/{department}', [DepartmentController::class, 'show']); // Show a single department
Route::get('research-centers', [ResearchCenterController::class, 'index']);  // List all research centers
Route::get('research-centers/{research_center}', [ResearchCenterController::class, 'show']); // Show a single research center

// ---------------------------------------------------------------------------
// Public Calls (Research Funding Announcements)
// ---------------------------------------------------------------------------
Route::get('calls', [CallController::class, 'index']);                       // List all public calls
Route::get('calls/{call}', [CallController::class, 'show']);                 // Show a single call (public)

// ---------------------------------------------------------------------------
// Public Events (Conferences, Workshops)
// ---------------------------------------------------------------------------
Route::get('events', [EventController::class, 'index']);                     // List all upcoming public events
Route::get('events/{event}', [EventController::class, 'show']);              // Show a single event

// ---------------------------------------------------------------------------
// Public Publications
// ---------------------------------------------------------------------------
Route::get('publications', [PublicationController::class, 'index']);         // List all public publications
Route::get('publications/statistics', [PublicationController::class, 'statistics'])
    ->middleware(['auth:sanctum', 'tenant']);                                // Get publication statistics (requires auth)
Route::get('publications/{publication}', [PublicationController::class, 'show']); // Show a single publication
Route::get('student-outputs', [OutputController::class, 'publicIndex']);     // List student research outputs (public)

// ---------------------------------------------------------------------------
// Community Problems (Public Submission)
// ---------------------------------------------------------------------------
Route::get('community-problems', [CommunityProblemController::class, 'index']); // List community problems
Route::get('community-problems/{community_problem}', [CommunityProblemController::class, 'show']); // Show a problem
Route::post('community-problems', [CommunityProblemController::class, 'store']); // Submit a new community problem (public)
Route::get('public/research-centers', [ResearchCenterController::class, 'publicOptions']); // Safe options for public forms

// ---------------------------------------------------------------------------
// Public Researchers Directory
// ---------------------------------------------------------------------------
Route::get('public/researchers', [UserController::class, 'publicIndex']);    // List researchers (public)
Route::get('public/researchers/{user}', [UserController::class, 'publicShow']); // Show researcher profile (public)

// ============================================================================
// AUTHENTICATED ROUTES (Require auth:sanctum + tenant middleware)
// ============================================================================
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {

    // -----------------------------------------------------------------------
    // Authentication & User Profile
    // -----------------------------------------------------------------------
    Route::get('user', [AuthController::class, 'user']);                     // Get authenticated user details
    Route::put('profile', [AuthController::class, 'updateProfile']);         // Update user profile
    Route::post('profile/complete', [AuthController::class, 'completeProfile']); // Complete profile (onboarding)
    Route::post('logout', [AuthController::class, 'logout']);                // Invalidate current token

    // Authenticated call views. Public GET /calls remains limited to
    // published public calls, while these endpoints use tenant visibility.
    Route::get('management/calls', [CallController::class, 'index']);
    Route::get('management/calls/{call}', [CallController::class, 'show']);
    Route::get('management/publications', [PublicationController::class, 'index']);
    Route::get('management/publications/{publication}', [PublicationController::class, 'show']);
    Route::get('management/research-centers/options', [ResearchCenterController::class, 'hierarchyOptions']);
    Route::get('management/research-centers', [ResearchCenterController::class, 'index']);

    // -----------------------------------------------------------------------
    // File Management (Central Repository)
    // -----------------------------------------------------------------------
    Route::post('files', [FileController::class, 'upload']);                 // Upload a new file
    Route::get('files', [FileController::class, 'index']);                   // List uploaded files (filterable)
    Route::put('files/{file}', [FileController::class, 'update']);           // Update file metadata/visibility
    Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download'); // Download a file
    Route::delete('files/{file}', [FileController::class, 'destroy']);       // Delete a file
    Route::get('files/{file}/versions', [FileController::class, 'versions']); // List all versions of a file
    Route::post('files/{file}/versions', [FileController::class, 'uploadNewVersion']); // Upload a new version

    // -----------------------------------------------------------------------
    // Language Preference
    // -----------------------------------------------------------------------
    Route::get('language-preference', [LanguagePreferenceController::class, 'show']); // Get user's language preference
    Route::put('language-preference', [LanguagePreferenceController::class, 'update']); // Update language preference

    // -----------------------------------------------------------------------
    // Notifications
    // -----------------------------------------------------------------------
    Route::get('notifications', [NotificationController::class, 'index']);   // List user notifications
    Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead']); // Mark all as read
    Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']); // Mark single as read

    // -----------------------------------------------------------------------
    // Audit Logs (Only for privileged roles)
    // -----------------------------------------------------------------------
    Route::get('audit-logs', [AuditLogController::class, 'index'])
        ->middleware('role:super_admin,research_admin,campus_admin,faculty_admin,department_head,director'); // View system audit logs

    // -----------------------------------------------------------------------
    // Super Admin System Management
    // -----------------------------------------------------------------------
    Route::middleware('role:super_admin')->group(function () {
        Route::get('system-health', [HealthController::class, 'index']);     // Detailed system health status
        Route::get('email-config', [\App\Http\Controllers\EmailConfigurationController::class, 'show']); // Get email config
        Route::post('email-config', [\App\Http\Controllers\EmailConfigurationController::class, 'update']); // Update email config
    });

    // -----------------------------------------------------------------------
    // Roles Listing (for frontend role selection)
    // -----------------------------------------------------------------------
    Route::get('roles', [RoleController::class, 'index']);                   // List all available system roles

    // -----------------------------------------------------------------------
    // Academic Hierarchy Management (CRUD – protected by roles)
    // -----------------------------------------------------------------------
    // Hierarchy: University → Campus → Faculty → Department → Research Center
    // Public GET endpoints are outside this group; protected methods require admin roles.

    Route::apiResource('universities', UniversityController::class)
        ->except(['index', 'show'])
        ->middleware('role:super_admin');                                    // Only super_admin can create/update/delete universities

    Route::apiResource('campuses', CampusController::class)
        ->except(['index'])
        ->middleware('role:super_admin,research_admin,campus_admin');        // Manage campuses

    Route::apiResource('faculties', FacultyController::class)
        ->except(['index'])
        ->middleware('role:super_admin,research_admin,campus_admin,faculty_admin'); // Manage faculties

    Route::apiResource('departments', DepartmentController::class)
        ->except(['index', 'show'])
        ->middleware('role:super_admin,research_admin,campus_admin,faculty_admin'); // Manage departments

    Route::apiResource('research-centers', ResearchCenterController::class)
        ->except(['index', 'show'])
        ->middleware('role:super_admin,research_admin,campus_admin,faculty_admin'); // Manage research centers

    // Academic Years
    Route::get('academic-years', [AcademicYearController::class, 'index']);  // List academic years (authenticated)
    Route::apiResource('academic-years', AcademicYearController::class)
        ->except(['index'])
        ->middleware('role:super_admin,research_admin');                    // Manage academic years
    Route::post('academic-years/{academic_year}/set-current', [AcademicYearController::class, 'setCurrent'])
        ->middleware('role:super_admin,research_admin');                    // Set current academic year

    // -----------------------------------------------------------------------
    // User Management
    // -----------------------------------------------------------------------
    Route::apiResource('users', UserController::class);                      // Full CRUD for users
    Route::post('users/{user}/roles', [UserRoleController::class, 'assign']);   // Assign role to user
    Route::delete('users/{user}/roles/{role}', [UserRoleController::class, 'revoke']); // Remove role from user
    Route::post('users/{user}/research-centers', [UserResearchCenterController::class, 'attach']); // Assign research center
    Route::delete('users/{user}/research-centers/{research_center}', [UserResearchCenterController::class, 'detach']); // Remove research center
    Route::post('users/{user}/expertise', [UserExpertiseController::class, 'attach']); // Add expertise
    Route::delete('users/{user}/expertise/{expertise}', [UserExpertiseController::class, 'detach']); // Remove expertise

    // -----------------------------------------------------------------------
    // Platform Roles & Permissions (Super Admin only)
    // -----------------------------------------------------------------------
    Route::prefix('admin')->middleware('role:super_admin')->group(function () {
        // Global Roles
        Route::get('roles', [\App\Http\Controllers\Admin\RoleController::class, 'index']);
        Route::post('roles', [\App\Http\Controllers\Admin\RoleController::class, 'store']);
        Route::put('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update']);
        Route::delete('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy']);
        Route::post('roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'syncPermissions']);

        // Global Permissions
        Route::get('permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'index']);
        Route::post('permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'store']);
        Route::put('permissions/{permission}', [\App\Http\Controllers\Admin\PermissionController::class, 'update']);
        Route::delete('permissions/{permission}', [\App\Http\Controllers\Admin\PermissionController::class, 'destroy']);
    });

    // -----------------------------------------------------------------------
    // Institution Role Overrides (Research/Campus/Faculty Admin)
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
    Route::post('settings/bulk', [SettingController::class, 'bulk'])->middleware('role:super_admin'); // Bulk update settings
    Route::post('settings', [SettingController::class, 'store'])->middleware('role:super_admin'); // Create setting
    Route::put('settings/{setting}', [SettingController::class, 'update'])->middleware('role:super_admin'); // Update setting
    Route::delete('settings/{setting}', [SettingController::class, 'destroy'])->middleware('role:super_admin'); // Delete setting

    // -----------------------------------------------------------------------
    // Institution Settings (Multi-tenant)
    // -----------------------------------------------------------------------
    Route::apiResource('institution-settings', InstitutionSettingController::class)
        ->middleware('role:research_admin,campus_admin,faculty_admin,department_head,director'); // Manage institution-level settings

    // -----------------------------------------------------------------------
    // Thematic Areas (Research classification)
    // -----------------------------------------------------------------------
    Route::get('thematic-areas', [ThematicAreaController::class, 'index']); // List thematic areas
    Route::post('thematic-areas', [ThematicAreaController::class, 'store'])
        ->middleware('role:research_admin'); // Create thematic area
    Route::get('thematic-areas/{thematic_area}', [ThematicAreaController::class, 'show']); // Show thematic area
    Route::put('thematic-areas/{thematic_area}', [ThematicAreaController::class, 'update'])
        ->middleware('role:research_admin'); // Update
    Route::delete('thematic-areas/{thematic_area}', [ThematicAreaController::class, 'destroy'])
        ->middleware('role:research_admin'); // Delete

    // -----------------------------------------------------------------------
    // Expertise Management (Researcher skills)
    // -----------------------------------------------------------------------
    Route::get('expertise', [ExpertiseController::class, 'index']);          // List expertise areas
    Route::post('expertise', [ExpertiseController::class, 'store'])
        ->middleware('role:super_admin,research_admin');                    // Create expertise
    Route::put('expertise/{expertise}', [ExpertiseController::class, 'update'])
        ->middleware('role:super_admin,research_admin');                    // Update
    Route::delete('expertise/{expertise}', [ExpertiseController::class, 'destroy'])
        ->middleware('role:super_admin,research_admin');                    // Delete

    // -----------------------------------------------------------------------
    // Review Criteria (Proposal evaluation rubrics)
    // -----------------------------------------------------------------------
    Route::get('review-criteria', [ReviewCriterionController::class, 'index']); // List criteria
    Route::post('review-criteria', [ReviewCriterionController::class, 'store'])
        ->middleware('role:super_admin,research_admin');                    // Create
    Route::put('review-criteria/{review_criterion}', [ReviewCriterionController::class, 'update'])
        ->middleware('role:super_admin,research_admin');                    // Update
    Route::delete('review-criteria/{review_criterion}', [ReviewCriterionController::class, 'destroy'])
        ->middleware('role:super_admin,research_admin');                    // Delete

    // ========================================================================
    // RESEARCH MANAGEMENT MODULES (Full Lifecycle)
    // ========================================================================

    // -----------------------------------------------------------------------
    // Calls (Protected CRUD – public GET already defined)
    // -----------------------------------------------------------------------
    Route::apiResource('calls', CallController::class)
        ->except(['index', 'show']);                                        // Create, update, delete calls

    // -----------------------------------------------------------------------
    // Proposals
    // -----------------------------------------------------------------------
    Route::apiResource('proposals', ProposalController::class);              // Full proposal CRUD

    // Workflow endpoints
    Route::post('proposals/{proposal}/submit', [ProposalController::class, 'submit']); // Submit proposal for review
    Route::post('proposals/{proposal}/check', [ProposalController::class, 'runChecks']); // Run validation checks
    Route::post('proposals/{proposal}/approve', [ProposalController::class, 'approve']); // Approve proposal
    Route::post('proposals/{proposal}/reject', [ProposalController::class, 'reject']);   // Reject proposal

    // Reviewer management
    Route::post('proposals/{proposal}/assign-reviewers', [ProposalController::class, 'assignReviewers']); // Assign reviewers
    Route::get('proposals/{proposal}/suggest-reviewers', [ProposalController::class, 'suggestReviewers']); // Auto-suggest reviewers

    // Document upload
    Route::post('proposals/{proposal}/upload-document', [ProposalController::class, 'uploadDocument']); // Upload proposal doc

    // -----------------------------------------------------------------------
    // Proposal Files
    // -----------------------------------------------------------------------
    Route::post('proposals/{proposal}/files', [ProposalFileController::class, 'attach']);   // Attach file to proposal
    Route::delete('proposals/{proposal}/files/{file}', [ProposalFileController::class, 'detach']); // Remove file

    // -----------------------------------------------------------------------
    // Proposal Investigators
    // -----------------------------------------------------------------------
    Route::apiResource('proposals.investigators', ProposalInvestigatorController::class)
        ->only(['index', 'store', 'destroy']);                              // Manage co-investigators

    // -----------------------------------------------------------------------
    // Proposal Reviewers (Assignment)
    // -----------------------------------------------------------------------
    Route::get('proposals/{proposal}/reviewers/recommendations', [ProposalReviewerController::class, 'recommendations']);
    Route::post('proposals/{proposal}/reviewers/{reviewer}/reopen', [ProposalReviewerController::class, 'reopen'])
        ->middleware('role:super_admin,research_admin');                    // Reopen a completed review
    Route::apiResource('proposals.reviewers', ProposalReviewerController::class)
        ->only(['index', 'store', 'destroy']);                              // Assign/unassign reviewers

    // -----------------------------------------------------------------------
    // Reviewer Workspace (for assigned reviewers)
    // -----------------------------------------------------------------------
    Route::get('reviewer/proposals', [ReviewerProposalController::class, 'index']); // List assigned proposals
    Route::get('reviewer/proposals/{proposal}', [ReviewerProposalController::class, 'show']); // View anonymised proposal
    Route::get('reviewer/proposals/{proposal}/template', [ReviewerProposalController::class, 'downloadTemplate']); // Download review template
    Route::post('reviewer/proposals/{proposal}/import', [ReviewerProposalController::class, 'importReview']); // Import review
    Route::post('reviewer/proposals/{proposal}/review', [ReviewerProposalController::class, 'storeReview']); // Submit review

    // -----------------------------------------------------------------------
    // Finance Checks
    // -----------------------------------------------------------------------
    Route::post('proposals/{proposal}/finance-checks', [FinanceCheckController::class, 'store']); // Submit for finance check
    Route::apiResource('finance-checks', FinanceCheckController::class)
        ->only(['index', 'show', 'update']);                                // Manage finance checks
    Route::post('finance-checks/{financeCheck}/approve', [FinanceCheckController::class, 'approve']);
    Route::post('finance-checks/{financeCheck}/reject', [FinanceCheckController::class, 'reject']);

    // -----------------------------------------------------------------------
    // Ethics Requests
    // -----------------------------------------------------------------------
    Route::post('proposals/{proposal}/ethics-requests', [EthicsRequestController::class, 'store']); // Submit ethics request
    Route::apiResource('ethics-requests', EthicsRequestController::class)
        ->only(['index', 'show', 'update', 'destroy']);                    // Manage ethics requests
    Route::post('ethics-requests/{ethicsRequest}/mark-submitted', [EthicsRequestController::class, 'markSubmitted']);
    Route::post('ethics-requests/{ethicsRequest}/approve', [EthicsRequestController::class, 'approve']);
    Route::post('ethics-requests/{ethicsRequest}/reject', [EthicsRequestController::class, 'reject']);
    Route::post('ethics-requests/{ethicsRequest}/request-revision', [EthicsRequestController::class, 'requestRevision']);

    // -----------------------------------------------------------------------
    // AI Detection Services (Plagiarism, AI)
    // -----------------------------------------------------------------------
    Route::get('detection/requests', [DetectionController::class, 'index']); // List detection requests
    Route::post('detection/requests', [DetectionController::class, 'store']); // Submit detection request
    Route::get('detection/requests/{id}', [DetectionController::class, 'show']); // Get detection result
    Route::post('detection/requests/{id}/complete', [DetectionController::class, 'complete']);
    Route::post('detection/requests/{id}/mark-reviewed', [DetectionController::class, 'markReviewed']);
    Route::post('detection/requests/{id}/retry', [DetectionController::class, 'retry']);
    Route::delete('detection/requests/{id}', [DetectionController::class, 'destroy']);
    Route::post('detection/requests/{id}/restore', [DetectionController::class, 'restore']);
    Route::get('detection/services', [DetectionController::class, 'services']); // List available detection services

    // ========================================================================
    // PROJECT MANAGEMENT
    // ========================================================================

    // -----------------------------------------------------------------------
    // Projects (Full CRUD + Workflow)
    // -----------------------------------------------------------------------
    Route::apiResource('projects', ProjectController::class);
    Route::post('projects/create-from-proposal/{proposal}', [ProjectController::class, 'createFromProposal']); // Create project from approved proposal

    // Workflow endpoints
    Route::post('projects/{project}/submit', [ProjectController::class, 'submit']);
    Route::post('projects/{project}/approve', [ProjectController::class, 'approve']);
    Route::post('projects/{project}/reject', [ProjectController::class, 'reject']);
    Route::post('projects/{project}/suspend', [ProjectController::class, 'suspend']);
    Route::post('projects/{project}/reactivate', [ProjectController::class, 'reactivate']);
    Route::post('projects/{project}/complete', [ProjectController::class, 'complete']);

    // Analytics
    Route::get('projects/{project}/progress', [ProjectController::class, 'progress']);
    Route::get('projects/{project}/budget-stats', [ProjectController::class, 'budgetStats']);
    Route::get('projects/{project}/timeline', [ProjectController::class, 'timeline']);

    // Team management
    Route::post('projects/{project}/investigators', [ProjectController::class, 'addInvestigator']);
    Route::delete('projects/{project}/investigators/{investigatorId}', [ProjectController::class, 'removeInvestigator']);

    // Deprecated status endpoint (kept for compatibility)
    Route::put('projects/{project}/status', [ProjectController::class, 'changeStatus']);

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

    // -----------------------------------------------------------------------
    // Project Milestones & Tasks
    // -----------------------------------------------------------------------
    Route::apiResource('projects.milestones', MilestoneController::class);
    Route::apiResource('milestones.tasks', TaskController::class);
    Route::post('tasks', [TaskController::class, 'storeStandalone']);       // Create standalone task
    Route::put('tasks/{task}', [TaskController::class, 'update']);          // Update task

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
    // Project Investigators
    // -----------------------------------------------------------------------
    Route::apiResource('projects.investigators', ProjectInvestigatorController::class)
        ->only(['index', 'store', 'destroy']);

    // ========================================================================
    // RESEARCH OUTPUTS
    // ========================================================================

    // -----------------------------------------------------------------------
    // Outputs (Unified research outputs: publications, patents, theses, etc.)
    // -----------------------------------------------------------------------
    Route::apiResource('outputs', OutputController::class);
    Route::post('outputs/{output}/submit', [OutputController::class, 'submit']);
    Route::post('outputs/{output}/verify', [OutputController::class, 'verify']);
    Route::post('outputs/{output}/approve', [OutputController::class, 'approve']);
    Route::post('outputs/{output}/reject', [OutputController::class, 'reject']);
    Route::post('outputs/{output}/publish', [OutputController::class, 'publish']);
    Route::post('outputs/{output}/status', [OutputController::class, 'changeStatus']);
    Route::get('outputs/subtypes-by-level', [OutputController::class, 'getSubtypesByLevel']); // Get subtypes by student level

    // Output participants (multi-student/supervisor)
    Route::apiResource('outputs.participants', OutputParticipantController::class)
        ->only(['index', 'store', 'destroy']);

    // Output files
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
    Route::post('events/{event}/register', [EventRegistrationController::class, 'register']); // Register for event
    Route::delete('events/{event}/registrations/{registration}', [EventRegistrationController::class, 'destroy']); // Cancel registration
    Route::put('events/{event}/attendance', [EventRegistrationController::class, 'markAttendance']); // Mark attendance
    Route::post('events/{event}/certificates', [EventRegistrationController::class, 'generateCertificate']); // Generate certificates

    // ========================================================================
    // PUBLICATIONS (Protected CRUD – public GET already defined)
    // ========================================================================
    Route::apiResource('publications', PublicationController::class)->except(['index', 'show']); // Manage publications
    Route::post('publications/{publication}/submit', [PublicationController::class, 'submit']);
    Route::post('publications/{publication}/verify', [PublicationController::class, 'verify']);
    Route::post('publications/{publication}/approve', [PublicationController::class, 'approve']);
    Route::post('publications/{publication}/reject', [PublicationController::class, 'reject']);
    Route::post('publications/{publication}/publish', [PublicationController::class, 'publish']);
    Route::post('publications/{publication}/update-citations', [PublicationController::class, 'updateCitations']); // Refresh citation count
    Route::apiResource('publications.authors', PublicationAuthorController::class)
        ->only(['index', 'store', 'update', 'destroy']);                    // Manage publication authors

    // ========================================================================
    // COMMUNITY PROBLEMS (Protected CRUD – public GET/store already defined)
    // ========================================================================
    Route::apiResource('community-problems', CommunityProblemController::class)
        ->except(['index', 'show', 'store']);                               // Manage problems
    Route::post('community-problems/{community_problem}/claim', [CommunityProblemController::class, 'claim']); // Claim a problem
    Route::post('community-problems/{community_problem}/complete', [CommunityProblemController::class, 'complete']); // Mark as completed
    Route::post('community-problems/{community_problem}/feedback', [CommunityProblemController::class, 'addFeedback']); // Add feedback

    // ========================================================================
    // REPORTING
    // ========================================================================
    Route::get('reports', [ReportController::class, 'index']);              // List generated reports
    Route::get('reports/types', [ReportController::class, 'types']);        // Supported report types
    Route::post('reports/generate', [ReportController::class, 'generate']); // Generate new report
    Route::get('reports/{report}/download', [ReportController::class, 'download']); // Download report PDF
    Route::delete('reports/{report}', [ReportController::class, 'destroy']); // Delete generated report

    // ========================================================================
    // GLOBAL SEARCH
    // ========================================================================
    Route::get('search', [SearchController::class, 'search']);              // Unified search across resources

    // ========================================================================
    // DASHBOARD (Authenticated user summary)
    // ========================================================================
    Route::get('dashboard', [DashboardController::class, 'index']);         // Get role-based dashboard stats
});
