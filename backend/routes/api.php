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
use App\Http\Controllers\PublicController;
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
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserExpertiseController;
use App\Http\Controllers\UserResearchCenterController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\ThematicAreaController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

// Lookups and Basic Config (public read)
Route::get('lookups/{table}', [LookupController::class, 'index']);
Route::get('settings', [SettingController::class, 'index']);
Route::get('universities', [UniversityController::class, 'index']);
Route::get('calls', [CallController::class, 'index']);
Route::get('calls/{call}', [CallController::class, 'show']);
Route::get('publications', [PublicationController::class, 'index']);
Route::get('publications/{publication}', [PublicationController::class, 'show']);
Route::get('community-problems', [CommunityProblemController::class, 'index']);
Route::get('community-problems/{community_problem}', [CommunityProblemController::class, 'show']);
Route::get('events', [EventController::class, 'index']);
Route::get('events/{event}', [EventController::class, 'show']);
Route::get('departments', [DepartmentController::class, 'index']);
Route::get('departments/{department}', [DepartmentController::class, 'show']);
Route::get('users', [UserController::class, 'index']);
Route::get('users/{user}', [UserController::class, 'show']);

// Public-facing endpoints (no auth required)
Route::prefix('public')->group(function () {
    Route::get('projects', [PublicController::class, 'projects']);
    Route::get('projects/{project}', [PublicController::class, 'projectDetails']);
    Route::get('publications', [PublicController::class, 'publications']);
    Route::get('events', [PublicController::class, 'events']);
});

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('user', [AuthController::class, 'user']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Language
    Route::get('language-preference', [LanguagePreferenceController::class, 'show']);
    Route::put('language-preference', [LanguagePreferenceController::class, 'update']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // Audit logs (admin only)
    Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('role:super_admin,research_admin,admin');

    // Settings (admin only management)
    Route::apiResource('settings', SettingController::class)->only(['store', 'update', 'destroy']);

    // Academic hierarchy & Thematic Areas
    Route::apiResource('universities', UniversityController::class)->except(['index', 'show']);
    Route::apiResource('campuses', CampusController::class);
    Route::apiResource('faculties', FacultyController::class);
    Route::apiResource('departments', DepartmentController::class)->except(['index', 'show']);
    Route::apiResource('research-centers', ResearchCenterController::class);
    Route::apiResource('thematic-areas', ThematicAreaController::class);
    Route::apiResource('academic-years', AcademicYearController::class);
    Route::post('academic-years/{academic_year}/set-current', [AcademicYearController::class, 'setCurrent']);

    // Users & Roles (admin only where needed)
    Route::apiResource('users', UserController::class)->except(['index', 'show']);
    Route::post('users/{user}/roles', [UserRoleController::class, 'assign']);
    Route::delete('users/{user}/roles/{role}', [UserRoleController::class, 'revoke']);
    Route::post('users/{user}/research-centers', [UserResearchCenterController::class, 'attach']);
    Route::delete('users/{user}/research-centers/{research_center}', [UserResearchCenterController::class, 'detach']);
    Route::post('users/{user}/expertise', [UserExpertiseController::class, 'attach']);
    Route::delete('users/{user}/expertise/{expertise}', [UserExpertiseController::class, 'detach']);

    // Roles & Permissions
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::post('roles/{role}/permissions', [RolePermissionController::class, 'sync']);

    // Expertise
    Route::apiResource('expertise', ExpertiseController::class);

    // Calls
    Route::apiResource('calls', CallController::class)->except(['index', 'show']);

    // Review criteria
    Route::apiResource('review-criteria', ReviewCriterionController::class);

    // Proposals
    Route::apiResource('proposals', ProposalController::class);
    Route::post('proposals/{proposal}/submit', [ProposalController::class, 'submit']);
    Route::post('proposals/{proposal}/approve', [ProposalController::class, 'approve']);
    Route::post('proposals/{proposal}/reject', [ProposalController::class, 'reject']);
    Route::post('proposals/{proposal}/assign-reviewers', [ProposalController::class, 'assignReviewers']);
    Route::post('proposals/{proposal}/create-project', [ProjectController::class, 'createFromProposal']);
    Route::get('proposals/{proposal}/suggest-reviewers', [ProposalController::class, 'suggestReviewers']);
    Route::apiResource('proposals.investigators', ProposalInvestigatorController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('proposals.reviewers', ProposalReviewerController::class)->only(['index', 'store', 'destroy']);
    Route::post('proposals/{proposal}/files', [ProposalFileController::class, 'attach']);
    Route::delete('proposals/{proposal}/files/{file}', [ProposalFileController::class, 'detach']);

    // Reviewer endpoints
    Route::get('reviewer/proposals', [ReviewerProposalController::class, 'index']);
    Route::get('reviewer/proposals/{proposal}', [ReviewerProposalController::class, 'show']);
    Route::post('reviewer/proposals/{proposal}/review', [ReviewerProposalController::class, 'storeReview']);

    // Finance checks
    Route::post('proposals/{proposal}/finance-checks', [FinanceCheckController::class, 'store']);
    Route::put('finance-checks/{finance_check}', [FinanceCheckController::class, 'update']);

    // Ethics requests
    Route::get('ethics-requests', [EthicsRequestController::class, 'index']);
    Route::get('ethics-requests/{ethics_request}', [EthicsRequestController::class, 'show']);
    Route::post('proposals/{proposal}/ethics-requests', [EthicsRequestController::class, 'store']);
    Route::put('ethics-requests/{ethics_request}', [EthicsRequestController::class, 'update']);
    Route::post('ethics-requests/{ethics_request}/decision', [EthicsRequestController::class, 'decision']);

    // Detection
    Route::get('detection/requests', [DetectionController::class, 'index']);
    Route::post('detection/requests', [DetectionController::class, 'store']);
    Route::get('detection/requests/{id}', [DetectionController::class, 'show']);

    // Files
    Route::post('files/upload', [FileController::class, 'upload']);
    Route::get('files', [FileController::class, 'index']);
    Route::get('files/{file}/download', [FileController::class, 'download']);
    Route::put('files/{file}', [FileController::class, 'update']);
    Route::delete('files/{file}', [FileController::class, 'destroy']);
    Route::get('files/{file}/versions', [FileController::class, 'versions']);
    Route::post('files/{file}/versions', [FileController::class, 'uploadNewVersion']);

    // Projects
    Route::apiResource('projects', ProjectController::class);
    Route::post('projects/create-from-proposal/{proposal}', [ProjectController::class, 'createFromProposal']);
    Route::put('projects/{project}/status', [ProjectController::class, 'changeStatus']);
    Route::apiResource('projects.milestones', MilestoneController::class);
    Route::apiResource('milestones.tasks', TaskController::class);
    // Standalone task routes (used by frontend for quick task updates)
    Route::post('tasks', [TaskController::class, 'storeStandalone']);
    Route::put('tasks/{task}', [TaskController::class, 'update']);
    Route::post('projects/{project}/files', [ProjectFileController::class, 'attach']);
    Route::delete('projects/{project}/files/{file}', [ProjectFileController::class, 'detach']);
    Route::apiResource('projects.investigators', ProjectInvestigatorController::class)->only(['index', 'store', 'destroy']);

    // Outputs
    Route::apiResource('outputs', OutputController::class);
    Route::post('outputs/{output}/status', [OutputController::class, 'changeStatus']);
    Route::apiResource('outputs.participants', OutputParticipantController::class)->only(['index', 'store', 'destroy']);
    Route::post('outputs/{output}/files', [OutputFileController::class, 'attach']);
    Route::delete('outputs/{output}/files/{file}', [OutputFileController::class, 'detach']);

    // Patents
    Route::apiResource('patents', PatentController::class);
    Route::apiResource('patents.licenses', LicenseController::class)->shallow();
    Route::post('patents/{patent}/files', [PatentFileController::class, 'attach']);
    Route::delete('patents/{patent}/files/{file}', [PatentFileController::class, 'detach']);

    // Partners & MoUs
    Route::apiResource('partners', PartnerController::class);
    Route::apiResource('partners.mo-us', MoUController::class)->shallow();

    // Agreement files
    Route::get('agreement-files', [AgreementFileController::class, 'index']);
    Route::post('agreement-files', [AgreementFileController::class, 'attach']);
    Route::delete('agreement-files/{agreement_file}', [AgreementFileController::class, 'detach']);

    // Expenses
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('projects.expenses', ExpenseController::class);
    Route::put('expenses/{expense}/approve', [ExpenseController::class, 'approve']);

    // Events (index is public — registered above outside auth group)
    Route::apiResource('events', EventController::class)->except(['index', 'show']);
    Route::post('events/{event}/register', [EventRegistrationController::class, 'register']);
    Route::delete('events/{event}/registrations/{registration}', [EventRegistrationController::class, 'destroy']);
    Route::put('events/{event}/attendance', [EventRegistrationController::class, 'markAttendance']);
    Route::post('events/{event}/certificates', [EventRegistrationController::class, 'generateCertificate']);

    // Publications
    Route::apiResource('publications', PublicationController::class)->except(['index', 'show']);
    Route::apiResource('publications.authors', PublicationAuthorController::class)->only(['index', 'store', 'update', 'destroy']);

    // Community Problems (index is public — registered above outside auth group)
    Route::apiResource('community-problems', CommunityProblemController::class)->except(['index', 'show']);
    Route::post('community-problems/{community_problem}/claim', [CommunityProblemController::class, 'claim']);
    Route::post('community-problems/{community_problem}/complete', [CommunityProblemController::class, 'complete']);
    Route::post('community-problems/{community_problem}/feedback', [CommunityProblemController::class, 'addFeedback']);

    // Reports
    Route::get('reports', [ReportController::class, 'index']);
    Route::post('reports/generate', [ReportController::class, 'generate']);
    Route::get('reports/{report}/download', [ReportController::class, 'download']);

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Search
    Route::get('search', [SearchController::class, 'search']);
});
