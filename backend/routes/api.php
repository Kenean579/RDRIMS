<?php

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
use Illuminate\Support\Facades\Route;

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// PUBLIC ROUTES (no authentication)
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

// System Health
Route::get('system/health', function () {
    $mailConfigured = !empty(config('mail.mailers.smtp.host')) && config('mail.mailers.smtp.host') !== '127.0.0.1';
    return response()->json([
        'status' => 'ok',
        'smtp_configured' => $mailConfigured,
        'warnings' => $mailConfigured ? [] : ['SMTP is not configured properly. Email notifications will fail.']
    ]);
});

// Lookups & settings (public read)
Route::get('lookups/{table}', [LookupController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:super_admin,research_admin,campus_admin,faculty_admin,department_head'])->get('settings', [SettingController::class, 'index']);

// Academic hierarchy (public read)
Route::get('universities', [UniversityController::class, 'index']);
Route::get('universities/{university}', [UniversityController::class, 'show']);
Route::get('campuses', [CampusController::class, 'index']);
Route::get('faculties', [FacultyController::class, 'index']);
Route::get('departments', [DepartmentController::class, 'index']);
Route::get('departments/{department}', [DepartmentController::class, 'show']);
Route::get('research-centers', [ResearchCenterController::class, 'index']);
Route::get('research-centers/{research_center}', [ResearchCenterController::class, 'show']);

// Calls (public read)
Route::get('calls', [CallController::class, 'index']);
Route::get('calls/{call}', [CallController::class, 'show']);

// Events (public read)
Route::get('events', [EventController::class, 'index']);
Route::get('events/{event}', [EventController::class, 'show']);

// Publications (public read)
Route::get('publications', [PublicationController::class, 'index']);
Route::get('publications/{publication}', [PublicationController::class, 'show']);
Route::get('student-outputs', [OutputController::class, 'publicIndex']);

// Community problems (public read + submit)
Route::get('community-problems', [CommunityProblemController::class, 'index']);
Route::get('community-problems/{community_problem}', [CommunityProblemController::class, 'show']);
Route::post('community-problems', [CommunityProblemController::class, 'store']);

// Public researchers directory
Route::get('public/researchers', [UserController::class, 'publicIndex']);
Route::get('public/researchers/{user}', [UserController::class, 'publicShow']);

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// AUTHENTICATED ROUTES
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
Route::middleware('auth:sanctum')->group(function () {

    // â”€â”€ Auth â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::get('user', [AuthController::class, 'user']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::post('logout', [AuthController::class, 'logout']);

    // â”€â”€ Files â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::post('files', [FileController::class, 'upload']);
    Route::get('files', [FileController::class, 'index']);
    Route::get('files/{file}/download', [FileController::class, 'download']);
    Route::delete('files/{file}', [FileController::class, 'destroy']);
    Route::get('files/{file}/versions', [FileController::class, 'versions']);
    Route::post('files/{file}/versions', [FileController::class, 'uploadNewVersion']);

    // â”€â”€ Language â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::get('language-preference', [LanguagePreferenceController::class, 'show']);
    Route::put('language-preference', [LanguagePreferenceController::class, 'update']);

    // ── Notifications ─────────────────────────────────────────────────────
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // ── Audit logs ────────────────────────────────────────────────────────────
    Route::get('audit-logs', [AuditLogController::class, 'index'])
        ->middleware('role:super_admin,research_admin,campus_admin,faculty_admin,department_head,director');

    // ── Super Admin Specific Routes ───────────────────────────────────────────
    Route::middleware('role:super_admin')->group(function () {
        // System Health
        Route::get('system-health', [\App\Http\Controllers\HealthController::class, 'index']);
        
        // Email Configuration
        Route::get('email-config', [\App\Http\Controllers\EmailConfigurationController::class, 'show']);
        Route::post('email-config', [\App\Http\Controllers\EmailConfigurationController::class, 'update']);
        Route::post('email-config/test', [\App\Http\Controllers\EmailConfigurationController::class, 'testEmail']);
    });

    // ── Roles (read-only listing for all auth users, e.g. role-assignment dropdowns)
    Route::get('roles', [RoleController::class, 'index']);

    // ── Academic hierarchy (write) ────────────────────────────────────────
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

    Route::get('academic-years', [AcademicYearController::class, 'index']);
    Route::apiResource('academic-years', AcademicYearController::class)
        ->except(['index'])
        ->middleware('role:super_admin,research_admin');
    Route::post('academic-years/{academic_year}/set-current', [AcademicYearController::class, 'setCurrent'])
        ->middleware('role:super_admin,research_admin');

    // â”€â”€ Users â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/roles', [UserRoleController::class, 'assign']);
    Route::delete('users/{user}/roles/{role}', [UserRoleController::class, 'revoke']);
    Route::post('users/{user}/research-centers', [UserResearchCenterController::class, 'attach']);
    Route::delete('users/{user}/research-centers/{research_center}', [UserResearchCenterController::class, 'detach']);
    Route::post('users/{user}/expertise', [UserExpertiseController::class, 'attach']);
    Route::delete('users/{user}/expertise/{expertise}', [UserExpertiseController::class, 'detach']);

    // â”€â”€ Roles & Permissions (platform level - super admin only) â”€â”€â”€â”€â”€
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

    // â”€â”€ Institutional role overrides â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::prefix('institution')->middleware('role:research_admin,campus_admin,faculty_admin')->group(function () {
        Route::get('roles', [\App\Http\Controllers\Institution\RoleController::class, 'index']);
        Route::post('roles', [\App\Http\Controllers\Institution\RoleController::class, 'store']);
        Route::get('roles/{role}/permissions', [\App\Http\Controllers\Institution\RoleController::class, 'permissions']);
        Route::post('roles/{role}/permissions', [\App\Http\Controllers\Institution\RoleController::class, 'syncOverrides']);

        Route::get('permissions', [\App\Http\Controllers\Institution\PermissionController::class, 'index']);
    });

    // ── Settings (write) ─────────────────────────────────────────────────────
    Route::post('settings/bulk', [SettingController::class, 'bulk'])->middleware('role:super_admin');
    Route::post('settings', [SettingController::class, 'store'])->middleware('role:super_admin');
    Route::put('settings/{setting}', [SettingController::class, 'update'])->middleware('role:super_admin');
    Route::delete('settings/{setting}', [SettingController::class, 'destroy'])->middleware('role:super_admin');

    // ── Institution Settings (org-level overrides) ──────────────────────────
    Route::apiResource('institution-settings', InstitutionSettingController::class)
        ->middleware('role:research_admin,campus_admin,faculty_admin,department_head,director');

    // ── Thematic Areas ──────────────────────────────────────────────────────
    Route::get('thematic-areas', [ThematicAreaController::class, 'index']);
    Route::post('thematic-areas', [ThematicAreaController::class, 'store'])->middleware('role:research_admin');
    Route::get('thematic-areas/{thematic_area}', [ThematicAreaController::class, 'show']);
    Route::put('thematic-areas/{thematic_area}', [ThematicAreaController::class, 'update'])->middleware('role:research_admin');
    Route::delete('thematic-areas/{thematic_area}', [ThematicAreaController::class, 'destroy'])->middleware('role:research_admin');

    // â”€â”€ Expertise â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::get('expertise', [ExpertiseController::class, 'index']);
    Route::post('expertise', [ExpertiseController::class, 'store'])->middleware('role:super_admin,research_admin');
    Route::put('expertise/{expertise}', [ExpertiseController::class, 'update'])->middleware('role:super_admin,research_admin');
    Route::delete('expertise/{expertise}', [ExpertiseController::class, 'destroy'])->middleware('role:super_admin,research_admin');

    // â”€â”€ Review criteria â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::get('review-criteria', [ReviewCriterionController::class, 'index']);
    Route::post('review-criteria', [ReviewCriterionController::class, 'store'])->middleware('role:super_admin,research_admin');
    Route::put('review-criteria/{review_criterion}', [ReviewCriterionController::class, 'update'])->middleware('role:super_admin,research_admin');
    Route::delete('review-criteria/{review_criterion}', [ReviewCriterionController::class, 'destroy'])->middleware('role:super_admin,research_admin');

    // â”€â”€ Calls â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::apiResource('calls', CallController::class)->except(['index', 'show']);

    // â”€â”€ Proposals â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::apiResource('proposals', ProposalController::class);
    Route::post('proposals/{proposal}/submit', [ProposalController::class, 'submit']);
    Route::post('proposals/{proposal}/check', [ProposalController::class, 'runChecks']);
    Route::post('proposals/{proposal}/approve', [ProposalController::class, 'approve']);
    Route::post('proposals/{proposal}/reject', [ProposalController::class, 'reject']);
    Route::post('proposals/{proposal}/assign-reviewers', [ProposalController::class, 'assignReviewers']);
    Route::get('proposals/{proposal}/suggest-reviewers', [ProposalController::class, 'suggestReviewers']);
    Route::post('proposals/{proposal}/upload-document', [ProposalController::class, 'uploadDocument']);
    Route::post('proposals/{proposal}/files', [ProposalFileController::class, 'attach']);
    Route::delete('proposals/{proposal}/files/{file}', [ProposalFileController::class, 'detach']);
    Route::apiResource('proposals.investigators', ProposalInvestigatorController::class)->only(['index', 'store', 'destroy']);
    Route::get('proposals/{proposal}/reviewers/recommendations', [ProposalReviewerController::class, 'recommendations']);
    Route::apiResource('proposals.reviewers', ProposalReviewerController::class)->only(['index', 'store', 'destroy']);

    
    Route::get('reviewer/proposals', [ReviewerProposalController::class, 'index']);
    Route::get('reviewer/proposals/{proposal}', [ReviewerProposalController::class, 'show']);
    Route::get('reviewer/proposals/{proposal}/template', [ReviewerProposalController::class, 'downloadTemplate']);
    Route::post('reviewer/proposals/{proposal}/import', [ReviewerProposalController::class, 'importReview']);
    Route::post('reviewer/proposals/{proposal}/review', [ReviewerProposalController::class, 'storeReview']);

  
    Route::post('proposals/{proposal}/finance-checks', [FinanceCheckController::class, 'store']);
    Route::apiResource('finance-checks', FinanceCheckController::class)->only(['index', 'show', 'update']);

    
    Route::post('proposals/{proposal}/ethics-requests', [EthicsRequestController::class, 'store']);
    Route::apiResource('ethics-requests', EthicsRequestController::class)->only(['index', 'show', 'update']);
    Route::post('ethics-requests/{ethics_request}/mark-submitted', [EthicsRequestController::class, 'markSubmitted']);
    Route::post('ethics-requests/{ethics_request}/decision', [EthicsRequestController::class, 'decision']);

    // â”€â”€ Detection â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::get('detection/requests', [DetectionController::class, 'index']);
    Route::post('detection/requests', [DetectionController::class, 'store']);
    Route::get('detection/requests/{id}', [DetectionController::class, 'show']);
    Route::get('detection/services', [DetectionController::class, 'services']);

    // â”€â”€ Projects â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::apiResource('projects', ProjectController::class);
    Route::post('projects/create-from-proposal/{proposal}', [ProjectController::class, 'createFromProposal']);
    Route::put('projects/{project}/status', [ProjectController::class, 'changeStatus']);
    Route::apiResource('projects.milestones', MilestoneController::class);
    Route::apiResource('milestones.tasks', TaskController::class);
    Route::post('tasks', [TaskController::class, 'storeStandalone']);
    Route::put('tasks/{task}', [TaskController::class, 'update']);
    Route::post('projects/{project}/files', [ProjectFileController::class, 'attach']);
    Route::delete('projects/{project}/files/{file}', [ProjectFileController::class, 'detach']);
    Route::apiResource('projects.investigators', ProjectInvestigatorController::class)->only(['index', 'store', 'destroy']);

    // â”€â”€ Outputs â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::apiResource('outputs', OutputController::class);
    Route::post('outputs/{output}/status', [OutputController::class, 'changeStatus']);
    Route::get('outputs/subtypes-by-level', [OutputController::class, 'getSubtypesByLevel']);
    Route::apiResource('outputs.participants', OutputParticipantController::class)->only(['index', 'store', 'destroy']);
    Route::post('outputs/{output}/files', [OutputFileController::class, 'attach']);
    Route::delete('outputs/{output}/files/{file}', [OutputFileController::class, 'detach']);

    // â”€â”€ Patents â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::apiResource('patents', PatentController::class);
    Route::apiResource('patents.licenses', LicenseController::class)->shallow();
    Route::post('patents/{patent}/files', [PatentFileController::class, 'attach']);
    Route::delete('patents/{patent}/files/{file}', [PatentFileController::class, 'detach']);

    // â”€â”€ Partners & MoUs â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::apiResource('partners', PartnerController::class);
    Route::apiResource('partners.mo-us', MoUController::class)->shallow();

    // â”€â”€ Agreement files â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::get('agreement-files', [AgreementFileController::class, 'index']);
    Route::post('agreement-files', [AgreementFileController::class, 'attach']);
    Route::delete('agreement-files/{agreement_file}', [AgreementFileController::class, 'detach']);

    // â”€â”€ Expenses â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::apiResource('projects.expenses', ExpenseController::class);
    Route::put('expenses/{expense}/approve', [ExpenseController::class, 'approve']);

    // â”€â”€ Events â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::apiResource('events', EventController::class)->except(['index', 'show']);
    Route::post('events/{event}/register', [EventRegistrationController::class, 'register']);
    Route::delete('events/{event}/registrations/{registration}', [EventRegistrationController::class, 'destroy']);
    Route::put('events/{event}/attendance', [EventRegistrationController::class, 'markAttendance']);
    Route::post('events/{event}/certificates', [EventRegistrationController::class, 'generateCertificate']);

    // â”€â”€ Publications â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::apiResource('publications', PublicationController::class)->except(['index', 'show']);
    Route::apiResource('publications.authors', PublicationAuthorController::class)->only(['index', 'store', 'update', 'destroy']);

    // â”€â”€ Community Problems â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::apiResource('community-problems', CommunityProblemController::class)->except(['index', 'show', 'store']);
    Route::post('community-problems/{community_problem}/claim', [CommunityProblemController::class, 'claim']);
    Route::post('community-problems/{community_problem}/complete', [CommunityProblemController::class, 'complete']);
    Route::post('community-problems/{community_problem}/feedback', [CommunityProblemController::class, 'addFeedback']);

    // â”€â”€ Reports â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::get('reports', [ReportController::class, 'index']);
    Route::post('reports/generate', [ReportController::class, 'generate']);
    Route::get('reports/{report}/download', [ReportController::class, 'download']);

    // â”€â”€ Search â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::get('search', [SearchController::class, 'search']);

    // â”€â”€ Dashboard â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::get('dashboard', [DashboardController::class, 'index']);
});
