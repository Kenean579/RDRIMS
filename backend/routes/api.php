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
|
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
|
*/


/*
|--------------------------------------------------------------------------
| Controller Imports
|--------------------------------------------------------------------------
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


/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
|
| These endpoints do not require authentication.
|
| Used for:
| - Authentication
| - Public university information
| - Public research information
| - Public website data
|
*/


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::post('register', [AuthController::class, 'register']);

Route::post('login', [AuthController::class, 'login']);

Route::post('forgot-password', [AuthController::class, 'forgotPassword']);

Route::post('reset-password', [AuthController::class, 'resetPassword']);



/*
|--------------------------------------------------------------------------
| System Health Check
|--------------------------------------------------------------------------
|
| Public endpoint used for monitoring whether the API is running.
|
*/

Route::get('system/health', [HealthController::class, 'ping']);

/*
|--------------------------------------------------------------------------
| Public Settings & Lookup Data
|--------------------------------------------------------------------------
|
| Read-only configuration data required by the frontend.
|
*/

Route::get('lookups/{table}', [LookupController::class, 'index']);

Route::get('settings', [SettingController::class, 'index']);

Route::post(
    'email-config/test',
    [\App\Http\Controllers\EmailConfigurationController::class, 'testEmail']
);



/*
|--------------------------------------------------------------------------
| Public Academic Organization Data
|--------------------------------------------------------------------------
|
| These endpoints expose institutional hierarchy information.
|
| Hierarchy:
|
| University
|    |
|    └── Campus
|          |
|          └── Faculty
|                |
|                └── Department
|                      |
|                      └── Research Center
|
*/

Route::get('universities', [UniversityController::class, 'index']);

Route::get('universities/{university}', [UniversityController::class, 'show']);

Route::get('campuses', [CampusController::class, 'index']);

Route::get('faculties', [FacultyController::class, 'index']);

Route::get('departments', [DepartmentController::class, 'index']);

Route::get('departments/{department}', [DepartmentController::class, 'show']);

Route::get('research-centers', [ResearchCenterController::class, 'index']);

Route::get(
    'research-centers/{research_center}',
    [ResearchCenterController::class, 'show']
);

/*
|--------------------------------------------------------------------------
| Public Calls
|--------------------------------------------------------------------------
|
| Research calls available for public viewing.
|
*/

Route::get('calls', [CallController::class, 'index']);

Route::get('calls/{call}', [CallController::class, 'show']);



/*
|--------------------------------------------------------------------------
| Public Events
|--------------------------------------------------------------------------
*/

Route::get('events', [EventController::class, 'index']);

Route::get('events/{event}', [EventController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Public Publications
|--------------------------------------------------------------------------
*/

Route::get('publications', [PublicationController::class, 'index']);

Route::get(
    'publications/{publication}',
    [PublicationController::class, 'show']
);

Route::get(
    'student-outputs',
    [OutputController::class, 'publicIndex']
);



/*
|--------------------------------------------------------------------------
| Community Problems
|--------------------------------------------------------------------------
|
| Public users can:
| - View problems
| - Submit new problems
|
*/

Route::get(
    'community-problems',
    [CommunityProblemController::class, 'index']
);

Route::get(
    'community-problems/{community_problem}',
    [CommunityProblemController::class, 'show']
);

Route::post(
    'community-problems',
    [CommunityProblemController::class, 'store']
);

/*
|--------------------------------------------------------------------------
| Public Researchers Directory
|--------------------------------------------------------------------------
*/

Route::get(
    'public/researchers',
    [UserController::class, 'publicIndex']
);

Route::get(
    'public/researchers/{user}',
    [UserController::class, 'publicShow']
);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
|
| All routes inside this group require:
|
| 1. auth:sanctum
|    - User must be authenticated using Laravel Sanctum token
|
| 2. tenant
|    - Resolves current institution/university context
|    - Helps enforce multi-tenant data isolation
|
*/

Route::middleware(['auth:sanctum', 'tenant'])->group(function () {



    /*
    |--------------------------------------------------------------------------
    | Authentication & User Profile
    |--------------------------------------------------------------------------
    |
    | Logged-in user operations:
    | - Get current user
    | - Update profile
    | - Complete profile
    | - Logout
    |
    */

    Route::get(
        'user',
        [AuthController::class, 'user']
    );

    Route::put(
        'profile',
        [AuthController::class, 'updateProfile']
    );

    Route::post(
        'profile/complete',
        [AuthController::class, 'completeProfile']
    );

    Route::post(
        'logout',
        [AuthController::class, 'logout']
    );

    /*
    |--------------------------------------------------------------------------
    | File Management
    |--------------------------------------------------------------------------
    |
    | Handles:
    | - File upload
    | - File listing
    | - Downloads
    | - Version management
    | - File deletion
    |
    */

    Route::post(
        'files',
        [FileController::class, 'upload']
    );

    Route::get(
        'files',
        [FileController::class, 'index']
    );

    Route::get(
        'files/{file}/download',
        [FileController::class, 'download']
    );

    Route::delete(
        'files/{file}',
        [FileController::class, 'destroy']
    );

    Route::get(
        'files/{file}/versions',
        [FileController::class, 'versions']
    );

    Route::post(
        'files/{file}/versions',
        [FileController::class, 'uploadNewVersion']
    );


    /*
    |--------------------------------------------------------------------------
    | Language Preference
    |--------------------------------------------------------------------------
    |
    | User-specific language settings.
    |
    */

    Route::get(
        'language-preference',
        [LanguagePreferenceController::class, 'show']
    );

    Route::put(
        'language-preference',
        [LanguagePreferenceController::class, 'update']
    );

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | User notification management.
    |
    */

    Route::get(
        'notifications',
        [NotificationController::class, 'index']
    );

    Route::put(
        'notifications/read-all',
        [NotificationController::class, 'markAllAsRead']
    );

    Route::put(
        'notifications/{id}/read',
        [NotificationController::class, 'markAsRead']
    );

    /*
    |--------------------------------------------------------------------------
    | Audit Logs
    |--------------------------------------------------------------------------
    |
    | Records and displays system activities.
    |
    | Access controlled by roles:
    | - Super Admin
    | - Research Admin
    | - Campus Admin
    | - Faculty Admin
    | - Department Head
    | - Director
    |
    */

    Route::get(
        'audit-logs',
        [AuditLogController::class, 'index']
    )
    ->middleware(
        'role:super_admin,research_admin,campus_admin,faculty_admin,department_head,director'
    );

    /*
    |--------------------------------------------------------------------------
    | Super Admin System Management
    |--------------------------------------------------------------------------
    |
    | Platform-level configuration.
    |
    | Only available for:
    | super_admin
    |
    */

    Route::middleware('role:super_admin')->group(function () {


        /*
        |--------------------------------------------------------------------------
        | System Monitoring
        |--------------------------------------------------------------------------
        */

        Route::get(
            'system-health',
            [HealthController::class, 'index']
        );

        /*
        |--------------------------------------------------------------------------
        | Email Configuration
        |--------------------------------------------------------------------------
        */

        Route::get(
            'email-config',
            [\App\Http\Controllers\EmailConfigurationController::class, 'show']
        );


        Route::post(
            'email-config',
            [\App\Http\Controllers\EmailConfigurationController::class, 'update']
        );


    });

    /*
    |--------------------------------------------------------------------------
    | Roles Listing
    |--------------------------------------------------------------------------
    |
    | Used by frontend role selection components.
    |
    */

    Route::get(
        'roles',
        [RoleController::class, 'index']
    );

      /*
    |--------------------------------------------------------------------------
    | Academic Hierarchy Management
    |--------------------------------------------------------------------------
    |
    | These resources define the institutional structure of RDRIMS.
    |
    | Hierarchy:
    |
    | University
    |    └── Campus
    |          └── Faculty
    |                └── Department
    |                      └── Research Center
    |
    | Multi-Tenant Notes:
    |
    | • Super Admin can manage all universities.
    | • Research Admin manages resources within their institution.
    | • Campus Admin manages resources within their campus.
    | • Faculty Admin manages resources within their faculty.
    |
    | Controllers and policies are responsible for enforcing tenant
    | isolation and preventing cross-university access.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Universities
    |--------------------------------------------------------------------------
    |
    | Public:
    |   GET /universities
    |   GET /universities/{university}
    |
    | Protected:
    |   POST
    |   PUT
    |   DELETE
    |
    | Access:
    |   Super Admin only
    |
    */

    Route::apiResource('universities', UniversityController::class)
        ->except(['index', 'show'])
        ->middleware('role:super_admin');



    /*
    |--------------------------------------------------------------------------
    | Campuses
    |--------------------------------------------------------------------------
    |
    | Public:
    |   GET /campuses
    |
    | Protected:
    |   POST
    |   SHOW
    |   UPDATE
    |   DELETE
    |
    | Access:
    |   • Super Admin
    |   • Research Admin
    |   • Campus Admin
    |
    | Notes:
    |   Tenant isolation is enforced inside CampusController and
    |   CampusPolicy.
    |
    */

    Route::apiResource('campuses', CampusController::class)
        ->except(['index'])
        ->middleware(
            'role:super_admin,research_admin,campus_admin'
        );


    /*
    |--------------------------------------------------------------------------
    | Faculties
    |--------------------------------------------------------------------------
    |
    | Public:
    |   GET /faculties
    |
    | Protected:
    |   POST
    |   SHOW
    |   UPDATE
    |   DELETE
    |
    | Access:
    |   • Super Admin
    |   • Research Admin
    |   • Campus Admin
    |   • Faculty Admin
    |
    */

    Route::apiResource('faculties', FacultyController::class)
        ->except(['index'])
        ->middleware(
            'role:super_admin,research_admin,campus_admin,faculty_admin'
        );



    /*
    |--------------------------------------------------------------------------
    | Departments
    |--------------------------------------------------------------------------
    |
    | Public:
    |   GET /departments
    |   GET /departments/{department}
    |
    | Protected:
    |   POST
    |   UPDATE
    |   DELETE
    |
    | Access:
    |   • Super Admin
    |   • Research Admin
    |   • Campus Admin
    |   • Faculty Admin
    |
    */

    Route::apiResource('departments', DepartmentController::class)
        ->except(['index', 'show'])
        ->middleware(
            'role:super_admin,research_admin,campus_admin,faculty_admin'
        );



    /*
    |--------------------------------------------------------------------------
    | Research Centers
    |--------------------------------------------------------------------------
    |
    | Public:
    |   GET /research-centers
    |   GET /research-centers/{research_center}
    |
    | Protected:
    |   POST
    |   UPDATE
    |   DELETE
    |
    | Access:
    |   • Super Admin
    |   • Research Admin
    |   • Campus Admin
    |   • Faculty Admin
    |
    */

    Route::apiResource('research-centers', ResearchCenterController::class)
        ->except(['index', 'show'])
        ->middleware(
            'role:super_admin,research_admin,campus_admin,faculty_admin'
        );



    /*
    |--------------------------------------------------------------------------
    | Academic Years
    |--------------------------------------------------------------------------
    |
    | Public:
    |   GET /academic-years
    |
    | Protected:
    |   CREATE
    |   UPDATE
    |   DELETE
    |   SET CURRENT
    |
    | Access:
    |   • Super Admin
    |   • Research Admin
    |
    */

    Route::get(
        'academic-years',
        [AcademicYearController::class, 'index']
    );

    Route::apiResource(
        'academic-years',
        AcademicYearController::class
    )
        ->except(['index'])
        ->middleware('role:super_admin,research_admin');

    Route::post(
        'academic-years/{academic_year}/set-current',
        [AcademicYearController::class, 'setCurrent']
    )
        ->middleware('role:super_admin,research_admin');

            /*
    |--------------------------------------------------------------------------
    | USER MANAGEMENT
    |--------------------------------------------------------------------------
    |
    | Handles user accounts inside RDRIMS.
    |
    | Includes:
    | - User listing
    | - User creation
    | - User updates
    | - User deletion
    | - Role assignment
    | - Research center assignment
    | - Expertise assignment
    |
    | Important:
    | UserController and related policies must enforce tenant
    | isolation to prevent users from accessing another institution's users.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Users CRUD
    |--------------------------------------------------------------------------
    |
    | Full user resource management.
    |
    | Access control is handled inside:
    | - UserPolicy
    | - Role permissions
    | - Tenant scope
    |
    */

    Route::apiResource(
        'users',
        UserController::class
    );



    /*
    |--------------------------------------------------------------------------
    | User Role Management
    |--------------------------------------------------------------------------
    |
    | Assign or remove roles from users.
    |
    | Examples:
    |
    | Research Admin
    |      |
    |      └── Assign Faculty Admin
    |
    | Campus Admin
    |      |
    |      └── Assign Campus-level roles
    |
    */

    Route::post(
        'users/{user}/roles',
        [UserRoleController::class, 'assign']
    );

    Route::delete(
        'users/{user}/roles/{role}',
        [UserRoleController::class, 'revoke']
    );


    /*
    |--------------------------------------------------------------------------
    | User Research Center Assignment
    |--------------------------------------------------------------------------
    |
    | Connects users with research centers.
    |
    | Example:
    |
    | Researcher
    |      |
    |      └── Artificial Intelligence Research Center
    |
    */

    Route::post(
        'users/{user}/research-centers',
        [UserResearchCenterController::class, 'attach']
    );


    Route::delete(
        'users/{user}/research-centers/{research_center}',
        [UserResearchCenterController::class, 'detach']
    );

    /*
    |--------------------------------------------------------------------------
    | User Expertise Management
    |--------------------------------------------------------------------------
    |
    | Assigns expertise areas to users.
    |
    | Example:
    |
    | User:
    |   Machine Learning
    |   Data Science
    |   Software Engineering
    |
    */

    Route::post(
        'users/{user}/expertise',
        [UserExpertiseController::class, 'attach']
    );


    Route::delete(
        'users/{user}/expertise/{expertise}',
        [UserExpertiseController::class, 'detach']
    );

    /*
    |--------------------------------------------------------------------------
    | PLATFORM ROLES & PERMISSIONS
    |--------------------------------------------------------------------------
    |
    | Platform-level authorization management.
    |
    | Only available for:
    |
    | super_admin
    |
    |
    | These roles and permissions apply globally across RDRIMS.
    |
    */

    Route::prefix('admin')
        ->middleware('role:super_admin')
        ->group(function () {


            /*
            |--------------------------------------------------------------------------
            | Global Roles
            |--------------------------------------------------------------------------
            */

            Route::get(
                'roles',
                [\App\Http\Controllers\Admin\RoleController::class, 'index']
            );


            Route::post(
                'roles',
                [\App\Http\Controllers\Admin\RoleController::class, 'store']
            );


            Route::put(
                'roles/{role}',
                [\App\Http\Controllers\Admin\RoleController::class, 'update']
            );


            Route::delete(
                'roles/{role}',
                [\App\Http\Controllers\Admin\RoleController::class, 'destroy']
            );


            Route::post(
                'roles/{role}/permissions',
                [
                    \App\Http\Controllers\Admin\RoleController::class,
                    'syncPermissions'
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Global Permissions
            |--------------------------------------------------------------------------
            */

            Route::get(
                'permissions',
                [
                    \App\Http\Controllers\Admin\PermissionController::class,
                    'index'
                ]
            );

            Route::post(
                'permissions',
                [
                    \App\Http\Controllers\Admin\PermissionController::class,
                    'store'
                ]
            );

            Route::put(
                'permissions/{permission}',
                [
                    \App\Http\Controllers\Admin\PermissionController::class,
                    'update'
                ]
            );

            Route::delete(
                'permissions/{permission}',
                [
                    \App\Http\Controllers\Admin\PermissionController::class,
                    'destroy'
                ]
            );

        });


    /*
    |--------------------------------------------------------------------------
    | INSTITUTION ROLE OVERRIDES
    |--------------------------------------------------------------------------
    |
    | Institution-level customization of permissions.
    |
    | Allows:
    |
    | Research Admin
    | Campus Admin
    | Faculty Admin
    |
    | to manage roles inside their own organization scope.
    |
    | Different from platform roles:
    |
    | Platform Roles:
    |   Managed by Super Admin
    |
    | Institution Roles:
    |   Managed by individual institutions
    |
    */

    Route::prefix('institution')
        ->middleware('role:research_admin,campus_admin,faculty_admin')
        ->group(function () {


            /*
            |--------------------------------------------------------------------------
            | Institution Roles
            |--------------------------------------------------------------------------
            */

            Route::get(
                'roles',
                [
                    \App\Http\Controllers\Institution\RoleController::class,
                    'index'
                ]
            );

            Route::post(
                'roles',
                [
                    \App\Http\Controllers\Institution\RoleController::class,
                    'store'
                ]
            );

            Route::get(
                'roles/{role}/permissions',
                [
                    \App\Http\Controllers\Institution\RoleController::class,
                    'permissions'
                ]
            );

            Route::post(
                'roles/{role}/permissions',
                [
                    \App\Http\Controllers\Institution\RoleController::class,
                    'syncOverrides'
                ]
            );

          /*
            |--------------------------------------------------------------------------
            | Institution Permissions
            |--------------------------------------------------------------------------
            */

            Route::get(
                'permissions',
                [
                    \App\Http\Controllers\Institution\PermissionController::class,
                    'index'
                ]
            );

        });

    /*
    |--------------------------------------------------------------------------
    | SYSTEM SETTINGS
    |--------------------------------------------------------------------------
    |
    | Global platform configuration.
    |
    | These settings affect the whole RDRIMS platform.
    |
    | Access:
    | - Super Admin only
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Settings Management
    |--------------------------------------------------------------------------
    |
    | Operations:
    |
    | POST   /settings/bulk
    | POST   /settings
    | PUT    /settings/{setting}
    | DELETE /settings/{setting}
    |
    */

    Route::post(
        'settings/bulk',
        [SettingController::class, 'bulk']
    )
    ->middleware('role:super_admin');

    Route::post(
        'settings',
        [SettingController::class, 'store']
    )
    ->middleware('role:super_admin');

    Route::put(
        'settings/{setting}',
        [SettingController::class, 'update']
    )
    ->middleware('role:super_admin');
    Route::delete(
        'settings/{setting}',
        [SettingController::class, 'destroy']
    )
    ->middleware('role:super_admin');

    /*
    |--------------------------------------------------------------------------
    | INSTITUTION SETTINGS
    |--------------------------------------------------------------------------
    |
    | Organization-level configuration.
    |
    | Unlike global settings:
    |
    | Global Settings:
    |      Platform-wide
    |
    | Institution Settings:
    |      Specific university/institution
    |
    |
    | Access:
    | - Research Admin
    | - Campus Admin
    | - Faculty Admin
    | - Department Head
    | - Director
    |
    |
    | Tenant isolation must be enforced by:
    | - Controller
    | - Policy
    | - Tenant scope
    |
    */

    Route::apiResource(
        'institution-settings',
        InstitutionSettingController::class
    )
    ->middleware(
        'role:research_admin,campus_admin,faculty_admin,department_head,director'
    );

    /*
    |--------------------------------------------------------------------------
    | THEMATIC AREAS
    |--------------------------------------------------------------------------
    |
    | Research classification areas.
    |
    | Examples:
    |
    | - Artificial Intelligence
    | - Agriculture Technology
    | - Health Innovation
    |
    |
    | Public:
    |   GET thematic areas
    |
    | Write Access:
    |   Research Admin
    |
    */

    Route::get(
        'thematic-areas',
        [ThematicAreaController::class, 'index']
    );

    Route::post(
        'thematic-areas',
        [ThematicAreaController::class, 'store']
    )
    ->middleware('role:research_admin');

    Route::get(
        'thematic-areas/{thematic_area}',
        [ThematicAreaController::class, 'show']
    );

    Route::put(
        'thematic-areas/{thematic_area}',
        [ThematicAreaController::class, 'update']
    )
    ->middleware('role:research_admin');

    Route::delete(
        'thematic-areas/{thematic_area}',
        [ThematicAreaController::class, 'destroy']
    )
    ->middleware('role:research_admin');

    /*
    |--------------------------------------------------------------------------
    | EXPERTISE MANAGEMENT
    |--------------------------------------------------------------------------
    |
    | Defines researcher expertise categories.
    |
    | Examples:
    |
    | - Software Engineering
    | - Machine Learning
    | - Biotechnology
    |
    |
    | Read:
    |   All authenticated users
    |
    | Write:
    |   Super Admin
    |   Research Admin
    |
    */

    Route::get(
        'expertise',
        [ExpertiseController::class, 'index']
    );

    Route::post(
        'expertise',
        [ExpertiseController::class, 'store']
    )
    ->middleware('role:super_admin,research_admin');


    Route::put(
        'expertise/{expertise}',
        [ExpertiseController::class, 'update']
    )
    ->middleware('role:super_admin,research_admin');

    Route::delete(
        'expertise/{expertise}',
        [ExpertiseController::class, 'destroy']
    )
    ->middleware('role:super_admin,research_admin');

    /*
    |--------------------------------------------------------------------------
    | REVIEW CRITERIA
    |--------------------------------------------------------------------------
    |
    | Defines evaluation criteria used during proposal review.
    |
    | Examples:
    |
    | - Innovation level
    | - Technical quality
    | - Expected impact
    |
    |
    | Read:
    |   Authenticated users
    |
    | Write:
    |   Super Admin
    |   Research Admin
    |
    */

    Route::get(
        'review-criteria',
        [ReviewCriterionController::class, 'index']
    );

    Route::post(
        'review-criteria',
        [ReviewCriterionController::class, 'store']
    )
    ->middleware('role:super_admin,research_admin');

    Route::put(
        'review-criteria/{review_criterion}',
        [ReviewCriterionController::class, 'update']
    )
    ->middleware('role:super_admin,research_admin');


    Route::delete(
        'review-criteria/{review_criterion}',
        [ReviewCriterionController::class, 'destroy']
    )
    ->middleware('role:super_admin,research_admin');

    /*
|--------------------------------------------------------------------------
| RESEARCH MANAGEMENT MODULES
|--------------------------------------------------------------------------
|
| These modules implement the complete research lifecycle in RDRIMS.
|
| Research Workflow
|
|  Call
|    │
|    ▼
|  Proposal
|    │
|    ├── Investigators
|    ├── Files
|    ├── Finance Check
|    ├── Ethics Review
|    ├── Reviewer Assignment
|    │
|    ▼
|  Approved Proposal
|    │
|    ▼
|  Project
|    │
|    ├── Milestones
|    ├── Tasks
|    ├── Expenses
|    ├── Files
|    └── Investigators
|    │
|    ▼
|  Outputs
|    │
|    ├── Publications
|    ├── Patents
|    ├── Licenses
|    └── Student Outputs
|
| Controllers, Policies and Tenant Middleware are responsible for
| enforcing authorization and multi-tenant isolation.
|
*/

/*
|--------------------------------------------------------------------------
| Calls
|--------------------------------------------------------------------------
|
| Research funding announcements.
|
| Public:
|   GET calls
|   GET calls/{call}
|
| Protected:
|   Create
|   Update
|   Delete
|
*/

Route::apiResource('calls', CallController::class)
    ->except(['index', 'show']);

/*
|--------------------------------------------------------------------------
| Proposals
|--------------------------------------------------------------------------
|
| Research proposals submitted in response to calls.
|
| Includes:
| - CRUD
| - Submission
| - Validation
| - Approval
| - Reviewer assignment
| - File management
| - Investigator management
|
*/

Route::apiResource('proposals', ProposalController::class);

Route::post(
    'proposals/{proposal}/submit',
    [ProposalController::class, 'submit']
);

Route::post(
    'proposals/{proposal}/check',
    [ProposalController::class, 'runChecks']
);

Route::post(
    'proposals/{proposal}/approve',
    [ProposalController::class, 'approve']
);

Route::post(
    'proposals/{proposal}/reject',
    [ProposalController::class, 'reject']
);

Route::post(
    'proposals/{proposal}/assign-reviewers',
    [ProposalController::class, 'assignReviewers']
);

Route::get(
    'proposals/{proposal}/suggest-reviewers',
    [ProposalController::class, 'suggestReviewers']
);

Route::post(
    'proposals/{proposal}/upload-document',
    [ProposalController::class, 'uploadDocument']
);

/*
|--------------------------------------------------------------------------
| Proposal Files
|--------------------------------------------------------------------------
*/

Route::post(
    'proposals/{proposal}/files',
    [ProposalFileController::class, 'attach']
);

Route::delete(
    'proposals/{proposal}/files/{file}',
    [ProposalFileController::class, 'detach']
);

/*
|--------------------------------------------------------------------------
| Proposal Investigators
|--------------------------------------------------------------------------
*/
Route::apiResource(
    'proposals.investigators',
    ProposalInvestigatorController::class
)->only([
    'index',
    'store',
    'destroy'
]);

/*
|--------------------------------------------------------------------------
| Proposal Reviewers
|--------------------------------------------------------------------------
*/

Route::get(
    'proposals/{proposal}/reviewers/recommendations',
    [ProposalReviewerController::class, 'recommendations']
);

Route::post(
    'proposals/{proposal}/reviewers/{reviewer}/reopen',
    [ProposalReviewerController::class, 'reopen']
)
->middleware('role:super_admin,research_admin');

Route::apiResource(
    'proposals.reviewers',
    ProposalReviewerController::class
)->only([
    'index',
    'store',
    'destroy'
]);

/*
|--------------------------------------------------------------------------
| Reviewer Workspace
|--------------------------------------------------------------------------
|
| Endpoints used by assigned reviewers.
|
*/

Route::get(
    'reviewer/proposals',
    [ReviewerProposalController::class, 'index']
);

Route::get(
    'reviewer/proposals/{proposal}',
    [ReviewerProposalController::class, 'show']
);

Route::get(
    'reviewer/proposals/{proposal}/template',
    [ReviewerProposalController::class, 'downloadTemplate']
);

Route::post(
    'reviewer/proposals/{proposal}/import',
    [ReviewerProposalController::class, 'importReview']
);

Route::post(
    'reviewer/proposals/{proposal}/review',
    [ReviewerProposalController::class, 'storeReview']
);

/*
|--------------------------------------------------------------------------
| Finance Checks
|--------------------------------------------------------------------------
*/

Route::post(
    'proposals/{proposal}/finance-checks',
    [FinanceCheckController::class, 'store']
);

Route::apiResource(
    'finance-checks',
    FinanceCheckController::class
)->only([
    'index',
    'show',
    'update'
]);

/*
|--------------------------------------------------------------------------
| Ethics Requests
|--------------------------------------------------------------------------
*/

Route::post(
    'proposals/{proposal}/ethics-requests',
    [EthicsRequestController::class, 'store']
);

Route::apiResource(
    'ethics-requests',
    EthicsRequestController::class
)->only([
    'index',
    'show',
    'update'
]);

Route::post(
    'ethics-requests/{ethics_request}/mark-submitted',
    [EthicsRequestController::class, 'markSubmitted']
);

Route::post(
    'ethics-requests/{ethics_request}/decision',
    [EthicsRequestController::class, 'decision']
);

/*
|--------------------------------------------------------------------------
| AI Detection Services
|--------------------------------------------------------------------------
|
| Similarity checking, AI detection, plagiarism detection,
| or other external verification services.
|
*/

Route::get(
    'detection/requests',
    [DetectionController::class, 'index']
);

Route::post(
    'detection/requests',
    [DetectionController::class, 'store']
);

Route::get(
    'detection/requests/{id}',
    [DetectionController::class, 'show']
);

Route::get(
    'detection/services',
    [DetectionController::class, 'services']
);

    /*
    |--------------------------------------------------------------------------
    | PROJECT MANAGEMENT
    |--------------------------------------------------------------------------
    |
    | Projects are created from approved proposals and represent the
    | execution phase of research.
    |
    | Project Lifecycle
    |
    | Approved Proposal
    |        │
    |        ▼
    |     Project
    |        │
    |        ├── Milestones
    |        ├── Tasks
    |        ├── Investigators
    |        ├── Files
    |        └── Expenses
    |
    */

    Route::apiResource('projects', ProjectController::class);

    Route::post(
        'projects/create-from-proposal/{proposal}',
        [ProjectController::class, 'createFromProposal']
    );

    Route::put(
        'projects/{project}/status',
        [ProjectController::class, 'changeStatus']
    );

    /*
    |--------------------------------------------------------------------------
    | Project Milestones
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'projects.milestones',
        MilestoneController::class
    );

    /*
    |--------------------------------------------------------------------------
    | Project Tasks
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'milestones.tasks',
        TaskController::class
    );

    Route::post(
        'tasks',
        [TaskController::class, 'storeStandalone']
    );

    Route::put(
        'tasks/{task}',
        [TaskController::class, 'update']
    );

    /*
    |--------------------------------------------------------------------------
    | Project Files
    |--------------------------------------------------------------------------
    */

    Route::post(
        'projects/{project}/files',
        [ProjectFileController::class, 'attach']
    );

    Route::delete(
        'projects/{project}/files/{file}',
        [ProjectFileController::class, 'detach']
    );

    /*
    |--------------------------------------------------------------------------
    | Project Investigators
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'projects.investigators',
        ProjectInvestigatorController::class
    )->only([
        'index',
        'store',
        'destroy'
    ]);

   /*
    |--------------------------------------------------------------------------
    | RESEARCH OUTPUTS
    |--------------------------------------------------------------------------
    |
    | Captures research outcomes generated from projects.
    |
    | Examples:
    | - Publications
    | - Patents
    | - Student outputs
    | - Technologies
    |
    */

    Route::apiResource('outputs', OutputController::class);

    Route::post(
        'outputs/{output}/status',
        [OutputController::class, 'changeStatus']
    );

    Route::get(
        'outputs/subtypes-by-level',
        [OutputController::class, 'getSubtypesByLevel']
    );

    Route::apiResource(
        'outputs.participants',
        OutputParticipantController::class
    )->only([
        'index',
        'store',
        'destroy'
    ]);

    Route::post(
        'outputs/{output}/files',
        [OutputFileController::class, 'attach']
    );

    Route::delete(
        'outputs/{output}/files/{file}',
        [OutputFileController::class, 'detach']
    );

    /*
    |--------------------------------------------------------------------------
    | PATENTS & LICENSES
    |--------------------------------------------------------------------------
    |
    | Intellectual property management.
    |
    */

    Route::apiResource('patents', PatentController::class);

    Route::apiResource(
        'patents.licenses',
        LicenseController::class
    )->shallow();

    Route::post(
        'patents/{patent}/files',
        [PatentFileController::class, 'attach']
    );

    Route::delete(
        'patents/{patent}/files/{file}',
        [PatentFileController::class, 'detach']
    );

    /*
    |--------------------------------------------------------------------------
    | PARTNERS & MEMORANDA OF UNDERSTANDING (MoUs)
    |--------------------------------------------------------------------------
    |
    | External organizations collaborating with the institution.
    |
    */

    Route::apiResource('partners', PartnerController::class);

    Route::apiResource(
        'partners.mo-us',
        MoUController::class
    )->shallow();

    /*
    |--------------------------------------------------------------------------
    | AGREEMENT FILES
    |--------------------------------------------------------------------------
    */

    Route::get(
        'agreement-files',
        [AgreementFileController::class, 'index']
    );

    Route::post(
        'agreement-files',
        [AgreementFileController::class, 'attach']
    );

    Route::delete(
        'agreement-files/{agreement_file}',
        [AgreementFileController::class, 'detach']
    );

    /*
    |--------------------------------------------------------------------------
    | PROJECT EXPENSES
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'projects.expenses',
        ExpenseController::class
    );

    Route::put(
        'expenses/{expense}/approve',
        [ExpenseController::class, 'approve']
    );

    /*
    |--------------------------------------------------------------------------
    | EVENTS
    |--------------------------------------------------------------------------
    |
    | Research conferences, workshops, seminars and training events.
    |
    */

    Route::apiResource('events', EventController::class)
        ->except(['index', 'show']);

    Route::post(
        'events/{event}/register',
        [EventRegistrationController::class, 'register']
    );

    Route::delete(
        'events/{event}/registrations/{registration}',
        [EventRegistrationController::class, 'destroy']
    );

    Route::put(
        'events/{event}/attendance',
        [EventRegistrationController::class, 'markAttendance']
    );

    Route::post(
        'events/{event}/certificates',
        [EventRegistrationController::class, 'generateCertificate']
    );

   /*
    |--------------------------------------------------------------------------
    | PUBLICATIONS
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'publications',
        PublicationController::class
    )->except([
        'index',
        'show'
    ]);

    Route::apiResource(
        'publications.authors',
        PublicationAuthorController::class
    )->only([
        'index',
        'store',
        'update',
        'destroy'
    ]);

    /*
    |--------------------------------------------------------------------------
    | COMMUNITY PROBLEMS
    |--------------------------------------------------------------------------
    |
    | Community engagement and problem-solving initiatives.
    |
    */

    Route::apiResource(
        'community-problems',
        CommunityProblemController::class
    )->except([
        'index',
        'show',
        'store'
    ]);

    Route::post(
        'community-problems/{community_problem}/claim',
        [CommunityProblemController::class, 'claim']
    );

    Route::post(
        'community-problems/{community_problem}/complete',
        [CommunityProblemController::class, 'complete']
    );

    Route::post(
        'community-problems/{community_problem}/feedback',
        [CommunityProblemController::class, 'addFeedback']
    );

    /*
    |--------------------------------------------------------------------------
    | REPORTING
    |--------------------------------------------------------------------------
    |
    | Generates institutional and research reports.
    |
    */

    Route::get(
        'reports',
        [ReportController::class, 'index']
    );

    Route::post(
        'reports/generate',
        [ReportController::class, 'generate']
    );

    Route::get(
        'reports/{report}/download',
        [ReportController::class, 'download']
    );

    /*
    |--------------------------------------------------------------------------
    | GLOBAL SEARCH
    |--------------------------------------------------------------------------
    |
    | Unified search across accessible resources.
    |
    */

    Route::get(
        'search',
        [SearchController::class, 'search']
    );

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    |
    | Returns summary statistics, analytics and dashboard widgets for
    | the authenticated user based on their role and tenant scope.
    |
    */

    Route::get(
        'dashboard',
        [DashboardController::class, 'index']
    );

}); // End authenticated routes



