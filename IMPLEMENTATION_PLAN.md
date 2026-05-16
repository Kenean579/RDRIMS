
**Project:** Research and Technology Transfer Database and Research Information Management System (RDRIMS)  
**Framework:** Laravel 13, PHP 8.4+  
**Authentication:** Laravel Sanctum  
**Database:** MySQL 8.0 / laragon
**Team:** 3 developers (Kenean, Burite, Hermela)  
**Architecture:** Thin controllers, Form Requests, Policies, Services, Jobs  
**Base namespace:** `App`

---

## CRITICAL INSTRUCTIONS FOR THE AI ASSISTANT

1. **Do not skip any file.** Every file listed below must be created with the complete code provided.
2. **Do not abbreviate or summarize.** Write the full content of each file.
3. **Follow the exact file paths** specified.
4. **Preserve all existing code** if I mention a file already exists; only add what's missing.
5. **All models already exist** with `HasFactory`, `$fillable`, `$casts`, and relationships. Do not recreate them unless instructed.
6. **Implement files in the order given** – some files depend on earlier ones.
7. **Use PHP 8.4 features** where appropriate (typed properties, readonly classes, etc.).
8. **All API responses** must use `response()->json()` with proper HTTP status codes.
9. **All exceptions** must be handled with try/catch and return meaningful error messages.
10. **Do not use wildcard route definitions.** Each route must be explicit.

---

## PHASE 1: PROJECT CONFIGURATION & FOUNDATION

### File 1: `.env` (append these lines to existing .env)
```
SANCTUM_STATEFUL_DOMAINS=localhost:5173,localhost
SESSION_DOMAIN=localhost
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://localhost:3000

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@wollouniversity.edu.et
MAIL_FROM_NAME="Wollo University RDRIMS"

FILESYSTEM_DISK=local
MEDIA_LIBRARY_DISK=local

APP_TIMEZONE=Africa/Addis_Ababa
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
```

---

### File 2: `config/auth.php` (find and update the guards section)

Replace the guards array with:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],
```

Ensure the providers section has:

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
],
```

---

### File 3: `config/cors.php` (create if not exists)

```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173')),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

---

### File 4: `app/Http/Kernel.php` (update the api middleware group)

Find the `api` middleware group and ensure it contains:

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

---

### File 5: `app/Providers/AuthServiceProvider.php`

Create this file with the following content:

```php
<?php

namespace App\Providers;

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CommunityProblem;
use App\Models\Department;
use App\Models\DetectionRequest;
use App\Models\EthicsRequest;
use App\Models\Event;
use App\Models\Expense;
use App\Models\File;
use App\Models\FinanceCheck;
use App\Models\License;
use App\Models\MoU;
use App\Models\Output;
use App\Models\Partner;
use App\Models\Patent;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Publication;
use App\Models\Report;
use App\Models\ResearchCenter;
use App\Models\ReviewCriterion;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use App\Policies\AcademicYearPolicy;
use App\Policies\CallPolicy;
use App\Policies\CommunityProblemPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\DetectionRequestPolicy;
use App\Policies\EthicsRequestPolicy;
use App\Policies\EventPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\FilePolicy;
use App\Policies\FinanceCheckPolicy;
use App\Policies\LicensePolicy;
use App\Policies\MoUPolicy;
use App\Policies\OutputPolicy;
use App\Policies\PartnerPolicy;
use App\Policies\PatentPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\ProposalPolicy;
use App\Policies\PublicationPolicy;
use App\Policies\ReportPolicy;
use App\Policies\ResearchCenterPolicy;
use App\Policies\ReviewCriterionPolicy;
use App\Policies\RolePolicy;
use App\Policies\SettingPolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        AcademicYear::class => AcademicYearPolicy::class,
        Department::class => DepartmentPolicy::class,
        ResearchCenter::class => ResearchCenterPolicy::class,
        Setting::class => SettingPolicy::class,
        Call::class => CallPolicy::class,
        ReviewCriterion::class => ReviewCriterionPolicy::class,
        Proposal::class => ProposalPolicy::class,
        FinanceCheck::class => FinanceCheckPolicy::class,
        EthicsRequest::class => EthicsRequestPolicy::class,
        DetectionRequest::class => DetectionRequestPolicy::class,
        File::class => FilePolicy::class,
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
        Output::class => OutputPolicy::class,
        Patent::class => PatentPolicy::class,
        License::class => LicensePolicy::class,
        Partner::class => PartnerPolicy::class,
        MoU::class => MoUPolicy::class,
        Expense::class => ExpensePolicy::class,
        Event::class => EventPolicy::class,
        Publication::class => PublicationPolicy::class,
        CommunityProblem::class => CommunityProblemPolicy::class,
        Report::class => ReportPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
```

---

### File 6: `app/Providers/AppServiceProvider.php` (update the boot method)

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }
    }
}
```

---

## PHASE 2: BASE CLASSES & TRAITS

### File 7: `app/Http/Controllers/Controller.php` (update if not already present)

```php
<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
}
```

---

### File 8: `app/Http/Middleware/RoleMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $userRoles = $request->user()->roles()->pluck('name')->toArray();

        if (empty(array_intersect($roles, $userRoles))) {
            return response()->json(['message' => 'Forbidden. Insufficient role.'], 403);
        }

        return $next($request);
    }
}
```

---

### File 9: `app/Http/Middleware/PermissionMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $hasPermission = $request->user()->roles()
            ->whereHas('permissions', fn($q) => $q->where('name', $permission))
            ->exists();

        if (! $hasPermission) {
            return response()->json(['message' => 'Forbidden. Missing permission: ' . $permission], 403);
        }

        return $next($request);
    }
}
```

---

### File 10: `app/Http/Middleware/ForceJsonResponse.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');
        return $next($request);
    }
}
```

---

### File 11: `bootstrap/app.php` (update the middleware section)

Replace the `->withMiddleware(function (Middleware $middleware) {` callback with:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(prepend: [
        \App\Http\Middleware\ForceJsonResponse::class,
    ]);

    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        'permission' => \App\Http\Middleware\PermissionMiddleware::class,
    ]);
})
```

---

### File 12: `app/Traits/HasRoles.php`

```php
<?php

namespace App\Traits;

trait HasRoles
{
    public function hasRole(string ...$roles): bool
    {
        $userRoles = $this->roles()->pluck('name')->toArray();
        return ! empty(array_intersect($roles, $userRoles));
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', fn($q) => $q->where('name', $permission))
            ->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('super_admin', 'research_admin');
    }
}
```

---

### File 13: Update `app/Models/User.php` – add the trait

Add this line inside the class body, after the existing `use` statements:

```php
use \App\Traits\HasRoles;
```

---

### File 14: `app/Enums/UserRole.php`

```php
<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case RESEARCH_ADMIN = 'research_admin';
    case DIRECTOR = 'director';
    case DEPARTMENT_HEAD = 'department_head';
    case RESEARCHER = 'researcher';
    case REVIEWER = 'reviewer';
    case FINANCE_OFFICER = 'finance_officer';
    case ETHICS_OFFICER = 'ethics_officer';
    case STUDENT = 'student';
    case GUEST = 'guest';

    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Administrator',
            self::RESEARCH_ADMIN => 'Research Administrator',
            self::DIRECTOR => 'Director',
            self::DEPARTMENT_HEAD => 'Department Head',
            self::RESEARCHER => 'Researcher',
            self::REVIEWER => 'Reviewer',
            self::FINANCE_OFFICER => 'Finance Officer',
            self::ETHICS_OFFICER => 'Ethics Officer',
            self::STUDENT => 'Student',
            self::GUEST => 'Guest',
        };
    }
}
```

---

### File 15: `app/Enums/ProposalStatusEnum.php`

```php
<?php

namespace App\Enums;

enum ProposalStatusEnum: int
{
    case DRAFT = 1;
    case SUBMITTED = 2;
    case UNDER_REVIEW = 3;
    case FINANCE_CHECK = 4;
    case APPROVED = 5;
    case REJECTED = 6;

    public function name(): string
    {
        return match($this) {
            self::DRAFT => 'draft',
            self::SUBMITTED => 'submitted',
            self::UNDER_REVIEW => 'under_review',
            self::FINANCE_CHECK => 'finance_check',
            self::APPROVED => 'approved',
            self::REJECTED => 'rejected',
        };
    }
}
```

---

### File 16: `app/Enums/ProjectStatusEnum.php`

```php
<?php

namespace App\Enums;

enum ProjectStatusEnum: int
{
    case ACTIVE = 1;
    case COMPLETED = 2;
    case SUSPENDED = 3;

    public function name(): string
    {
        return match($this) {
            self::ACTIVE => 'active',
            self::COMPLETED => 'completed',
            self::SUSPENDED => 'suspended',
        };
    }
}
```

---

### File 17: `app/Enums/TaskStatusEnum.php`

```php
<?php

namespace App\Enums;

enum TaskStatusEnum: int
{
    case NOT_STARTED = 1;
    case IN_PROGRESS = 2;
    case DONE = 3;

    public function name(): string
    {
        return match($this) {
            self::NOT_STARTED => 'not_started',
            self::IN_PROGRESS => 'in_progress',
            self::DONE => 'done',
        };
    }
}
```

---

### File 18: `app/Enums/OutputStatusEnum.php`

```php
<?php

namespace App\Enums;

enum OutputStatusEnum: int
{
    case DRAFT = 1;
    case SUBMITTED = 2;
    case APPROVED_BY_SUPERVISOR = 3;
    case APPROVED = 4;
    case REJECTED = 5;

    public function name(): string
    {
        return match($this) {
            self::DRAFT => 'draft',
            self::SUBMITTED => 'submitted',
            self::APPROVED_BY_SUPERVISOR => 'approved_by_supervisor',
            self::APPROVED => 'approved',
            self::REJECTED => 'rejected',
        };
    }
}
```

---

## PHASE 3: SERVICES

### File 19: `app/Services/UserService.php`

```php
<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'department_id' => $data['department_id'] ?? null,
            'is_active' => true,
        ]);

        // Assign default guest role
        $guestRole = Role::where('name', UserRole::GUEST->value)->first();
        if ($guestRole) {
            $user->roles()->attach($guestRole->id, [
                'assigned_by' => null,
                'assigned_at' => now(),
            ]);
        }

        return $user->load('roles');
    }

    public function assignRole(User $user, Role $role, User $assignedBy): void
    {
        if ($user->roles()->where('role_id', $role->id)->exists()) {
            throw ValidationException::withMessages([
                'role' => 'User already has this role.',
            ]);
        }

        $user->roles()->attach($role->id, [
            'assigned_by' => $assignedBy->id,
            'assigned_at' => now(),
        ]);
    }

    public function revokeRole(User $user, Role $role): void
    {
        $user->roles()->detach($role->id);
    }

    public function deactivate(User $user): void
    {
        $user->update(['is_active' => false]);
        $user->tokens()->delete();
    }

    public function activate(User $user): void
    {
        $user->update(['is_active' => true]);
    }
}
```

---

### File 20: `app/Services/AuditLogService.php`

```php
<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogService
{
    public function log(string $action, string $tableName, int $recordId, ?Request $request = null): void
    {
        AuditLog::create([
            'user_id' => $request?->user()?->id,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'ip_address' => $request?->ip(),
            'created_at' => now(),
        ]);
    }
}
```

---

### File 21: `app/Services/ProposalService.php`

```php
<?php

namespace App\Services;

use App\Enums\ProposalStatusEnum;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ProposalService
{
    public function __construct(
        private AuditLogService $auditLogService,
    ) {}

    public function submit(Proposal $proposal, User $user): void
    {
        if ($proposal->status_id !== ProposalStatusEnum::DRAFT->value) {
            throw ValidationException::withMessages([
                'status' => 'Only draft proposals can be submitted.',
            ]);
        }

        if (empty($proposal->investigators) || $proposal->investigators->count() === 0) {
            throw ValidationException::withMessages([
                'investigators' => 'At least one investigator is required.',
            ]);
        }

        $proposal->update([
            'status_id' => ProposalStatusEnum::SUBMITTED->value,
            'submitted_at' => now(),
            'submitted_by' => $user->id,
        ]);

        $this->auditLogService->log('submitted', 'proposals', $proposal->id, request());
    }

    public function approve(Proposal $proposal, User $approvedBy): void
    {
        if ($proposal->status_id !== ProposalStatusEnum::UNDER_REVIEW->value) {
            throw ValidationException::withMessages([
                'status' => 'Only proposals under review can be approved.',
            ]);
        }

        $proposal->update([
            'status_id' => ProposalStatusEnum::APPROVED->value,
            'approved_by' => $approvedBy->id,
            'approved_at' => now(),
        ]);

        // Automatically create a project from approved proposal
        $proposal->project()->create([
            'title' => $proposal->title,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'total_budget' => $proposal->budget,
            'status_id' => 1, // active
            'pi_id' => $proposal->submitted_by,
            'academic_year_id' => $proposal->academic_year_id,
        ]);

        $this->auditLogService->log('approved', 'proposals', $proposal->id, request());
    }

    public function reject(Proposal $proposal, User $rejectedBy, string $comment): void
    {
        if (! in_array($proposal->status_id, [ProposalStatusEnum::SUBMITTED->value, ProposalStatusEnum::UNDER_REVIEW->value])) {
            throw ValidationException::withMessages([
                'status' => 'Only submitted or under-review proposals can be rejected.',
            ]);
        }

        $proposal->update([
            'status_id' => ProposalStatusEnum::REJECTED->value,
            'status_change_comment' => $comment,
        ]);

        $this->auditLogService->log('rejected', 'proposals', $proposal->id, request());
    }

    public function assignReviewers(Proposal $proposal, array $reviewerIds, User $assignedBy): void
    {
        foreach ($reviewerIds as $reviewerId) {
            $proposal->reviewers()->attach($reviewerId, [
                'assigned_by' => $assignedBy->id,
                'assigned_at' => now(),
            ]);
        }

        $proposal->update(['status_id' => ProposalStatusEnum::UNDER_REVIEW->value]);

        $this->auditLogService->log('reviewers_assigned', 'proposals', $proposal->id, request());
    }
}
```

---

### File 22: `app/Services/ReviewerSuggestionService.php`

```php
<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Collection;

class ReviewerSuggestionService
{
    public function suggest(Proposal $proposal, int $limit = 5): Collection
    {
        $keywords = array_filter(explode(',', $proposal->keywords));
        $keywords = array_map('trim', $keywords);

        if (empty($keywords)) {
            return User::whereHas('roles', fn($q) => $q->where('name', 'reviewer'))
                ->where('id', '!=', $proposal->submitted_by)
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        }

        return User::whereHas('roles', fn($q) => $q->where('name', 'reviewer'))
            ->where('id', '!=', $proposal->submitted_by)
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('expertise_keywords', 'LIKE', "%{$keyword}%");
                }
            })
            ->orWhereHas('expertise', function ($query) use ($keywords) {
                $query->whereIn('name', $keywords);
            })
            ->limit($limit)
            ->get();
    }
}
```

---

### File 23: `app/Services/FileService.php`

```php
<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function upload(UploadedFile $uploadedFile, int $uploadedBy, bool $isPublic = false): File
    {
        $originalName = $uploadedFile->getClientOriginalName();
        $path = $uploadedFile->store('files/' . date('Y/m'), 'local');
        $mimeType = $uploadedFile->getMimeType();
        $size = $uploadedFile->getSize();

        return File::create([
            'file_path' => $path,
            'version' => 1,
            'uploaded_by' => $uploadedBy,
            'is_public' => $isPublic,
            'created_at' => now(),
        ]);
    }

    public function uploadNewVersion(File $file, UploadedFile $uploadedFile): File
    {
        $path = $uploadedFile->store('files/' . date('Y/m'), 'local');

        $newVersion = $file->version + 1;

        $file->update([
            'file_path' => $path,
            'version' => $newVersion,
        ]);

        return $file;
    }

    public function delete(File $file): void
    {
        if (Storage::disk('local')->exists($file->file_path)) {
            Storage::disk('local')->delete($file->file_path);
        }
        $file->delete();
    }

    public function download(File $file): mixed
    {
        if (! Storage::disk('local')->exists($file->file_path)) {
            abort(404, 'File not found on storage.');
        }
        return Storage::disk('local')->download($file->file_path, $file->file_path);
    }
}
```

---

### File 24: `app/Services/ProjectService.php`

```php
<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;

class ProjectService
{
    public function createFromProposal(Proposal $proposal, User $creator): Project
    {
        if ($proposal->status_id !== 5) { // approved
            abort(422, 'Only approved proposals can be converted to projects.');
        }

        if ($proposal->project()->exists()) {
            abort(422, 'A project already exists for this proposal.');
        }

        return $proposal->project()->create([
            'title' => $proposal->title,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'total_budget' => $proposal->budget,
            'budget_allocation' => $proposal->budget_allocation,
            'status_id' => 1, // active
            'pi_id' => $proposal->submitted_by,
            'academic_year_id' => $proposal->academic_year_id,
        ]);
    }
}
```

---

### File 25: `app/Services/OutputService.php`

```php
<?php

namespace App\Services;

use App\Enums\OutputStatusEnum;
use App\Models\Output;
use App\Models\User;

class OutputService
{
    public function changeStatus(Output $output, int $newStatusId, User $user): void
    {
        $currentStatus = $output->status_id;

        $allowedTransitions = [
            OutputStatusEnum::DRAFT->value => [OutputStatusEnum::SUBMITTED->value],
            OutputStatusEnum::SUBMITTED->value => [OutputStatusEnum::APPROVED_BY_SUPERVISOR->value, OutputStatusEnum::REJECTED->value],
            OutputStatusEnum::APPROVED_BY_SUPERVISOR->value => [OutputStatusEnum::APPROVED->value, OutputStatusEnum::REJECTED->value],
        ];

        if (! isset($allowedTransitions[$currentStatus])) {
            abort(422, 'No further status transitions allowed.');
        }

        if (! in_array($newStatusId, $allowedTransitions[$currentStatus])) {
            abort(422, 'Invalid status transition.');
        }

        $output->update(['status_id' => $newStatusId]);
    }
}
```

---

### File 26: `app/Services/ReportService.php`

```php
<?php

namespace App\Services;

use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    public function generate(string $name, string $view, array $data, int $generatedBy): Report
    {
        $pdf = Pdf::loadView($view, $data);
        $fileName = 'reports/' . now()->format('Y-m-d') . '/' . \Str::slug($name) . '-' . time() . '.pdf';
        Storage::disk('local')->put($fileName, $pdf->output());

        return Report::create([
            'name' => $name,
            'file_path' => $fileName,
            'generated_by' => $generatedBy,
            'generated_at' => now(),
            'parameters' => $data,
        ]);
    }
}
```

---

### File 27: `app/Services/EthicsService.php`

```php
<?php

namespace App\Services;

use App\Models\EthicsRequest;
use App\Models\Proposal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class EthicsService
{
    public function generatePdf(Proposal $proposal): EthicsRequest
    {
        $data = [
            'title' => $proposal->title,
            'abstract' => $proposal->abstract,
            'objectives' => $proposal->objectives,
            'methodology' => $proposal->methodology,
            'submitted_by' => $proposal->submittedBy->name,
            'date' => now()->format('F j, Y'),
        ];

        $pdf = Pdf::loadView('pdfs.ethics_request', $data);
        $fileName = 'ethics/' . $proposal->id . '/' . time() . '.pdf';
        Storage::disk('local')->put($fileName, $pdf->output());

        return EthicsRequest::create([
            'proposal_id' => $proposal->id,
            'generated_pdf_path' => $fileName,
            'submitted_to_irb' => false,
            'approval_status_id' => 1, // pending
            'version' => 1,
        ]);
    }
}
```

---

### File 28: `app/Services/EventService.php`

```php
<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class EventService
{
    public function register(Event $event, int $userId): EventRegistration
    {
        if ($event->registrations()->where('user_id', $userId)->exists()) {
            abort(422, 'Already registered for this event.');
        }

        if ($event->capacity && $event->registrations()->count() >= $event->capacity) {
            abort(422, 'Event has reached maximum capacity.');
        }

        if ($event->registration_deadline && now()->gt($event->registration_deadline)) {
            abort(422, 'Registration deadline has passed.');
        }

        return $event->registrations()->create([
            'user_id' => $userId,
            'attended' => false,
        ]);
    }

    public function markAttendance(Event $event, int $userId): void
    {
        $registration = $event->registrations()->where('user_id', $userId)->first();

        if (! $registration) {
            abort(404, 'User is not registered for this event.');
        }

        $registration->update(['attended' => true]);
    }

    public function generateCertificate(Event $event, int $userId): string
    {
        $registration = $event->registrations()->where('user_id', $userId)->first();

        if (! $registration || ! $registration->attended) {
            abort(422, 'Certificate can only be generated for attendees.');
        }

        $pdf = Pdf::loadView('pdfs.event_certificate', [
            'user_name' => $registration->user->name,
            'event_title' => $event->title,
            'event_date' => $event->start_date->format('F j, Y'),
            'venue' => $event->venue,
        ]);

        $fileName = 'certificates/' . $event->id . '/' . $userId . '-' . time() . '.pdf';
        Storage::disk('local')->put($fileName, $pdf->output());

        return $fileName;
    }
}
```

---

### File 29: `app/Services/CommunityProblemService.php`

```php
<?php

namespace App\Services;

use App\Models\CommunityProblem;
use App\Models\User;

class CommunityProblemService
{
    public function claim(CommunityProblem $problem, User $user): void
    {
        if ($problem->status_id !== 1) { // open
            abort(422, 'Only open problems can be claimed.');
        }

        if ($problem->claimed_by) {
            abort(422, 'Problem is already claimed by another user.');
        }

        $problem->update([
            'status_id' => 2, // claimed
            'claimed_by' => $user->id,
            'claimed_at' => now(),
        ]);
    }

    public function complete(CommunityProblem $problem, User $user): void
    {
        if ($problem->status_id !== 2) { // claimed
            abort(422, 'Only claimed problems can be completed.');
        }

        if ($problem->claimed_by !== $user->id) {
            abort(403, 'Only the claimant can mark as complete.');
        }

        $problem->update([
            'status_id' => 3, // completed
            'completed_at' => now(),
        ]);
    }

    public function addFeedback(CommunityProblem $problem, string $feedback, int $rating): void
    {
        if ($problem->status_id !== 3) { // completed
            abort(422, 'Feedback can only be added to completed problems.');
        }

        $problem->update([
            'feedback' => $feedback,
            'rating' => $rating,
        ]);
    }
}
```

---

## PHASE 4: FORM REQUESTS (DEVELOPER A – Kenean)

### File 30: `app/Http/Requests/Auth/RegisterRequest.php`

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Anyone can register
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'department_id' => 'nullable|exists:departments,id',
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => 'The password confirmation does not match.',
            'email.unique' => 'This email address is already registered.',
        ];
    }
}
```

---

### File 31: `app/Http/Requests/Auth/LoginRequest.php`

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];
    }
}
```

---

### File 32: `app/Http/Requests/StoreUserRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\User::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ];
    }
}
```

---

### File 33: `app/Http/Requests/UpdateUserRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $userId,
            'password' => 'sometimes|string|min:8',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
            'orcid_id' => 'nullable|string|max:255',
            'google_scholar_id' => 'nullable|string|max:255',
            'scopus_id' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'bio' => 'nullable|string',
        ];
    }
}
```

---

### File 34: `app/Http/Requests/StoreRoleRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Role::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:roles',
            'description' => 'nullable|string',
        ];
    }
}
```

---

### File 35: `app/Http/Requests/UpdateRoleRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('role'));
    }

    public function rules(): array
    {
        $roleId = $this->route('role')->id;

        return [
            'name' => 'sometimes|string|max:100|unique:roles,name,' . $roleId,
            'description' => 'nullable|string',
        ];
    }
}
```

---

### File 36: `app/Http/Requests/StorePermissionRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Permission::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:permissions',
            'description' => 'nullable|string',
        ];
    }
}
```

---

### File 37: `app/Http/Requests/UpdatePermissionRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('permission'));
    }

    public function rules(): array
    {
        $permissionId = $this->route('permission')->id;

        return [
            'name' => 'sometimes|string|max:100|unique:permissions,name,' . $permissionId,
            'description' => 'nullable|string',
        ];
    }
}
```

---

### File 38: `app/Http/Requests/StoreAcademicYearRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\AcademicYear::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50|unique:academic_years',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ];
    }
}
```

---

### File 39: `app/Http/Requests/UpdateAcademicYearRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('academic_year'));
    }

    public function rules(): array
    {
        $id = $this->route('academic_year')->id;

        return [
            'name' => 'sometimes|string|max:50|unique:academic_years,name,' . $id,
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'is_current' => 'boolean',
        ];
    }
}
```

---

### File 40: `app/Http/Requests/StoreUniversityRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUniversityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\University::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:universities',
        ];
    }
}
```

---

### File 41: `app/Http/Requests/UpdateUniversityRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUniversityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('university'));
    }

    public function rules(): array
    {
        $id = $this->route('university')->id;

        return [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:universities,code,' . $id,
        ];
    }
}
```

---

### File 42: `app/Http/Requests/StoreCampusRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Campus::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:campuses',
            'university_id' => 'required|exists:universities,id',
        ];
    }
}
```

---

### File 43: `app/Http/Requests/UpdateCampusRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('campus'));
    }

    public function rules(): array
    {
        $id = $this->route('campus')->id;

        return [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:campuses,code,' . $id,
            'university_id' => 'sometimes|exists:universities,id',
        ];
    }
}
```

---

### File 44: `app/Http/Requests/StoreFacultyRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacultyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Faculty::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:faculties',
            'campus_id' => 'required|exists:campuses,id',
        ];
    }
}
```

---

### File 45: `app/Http/Requests/UpdateFacultyRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFacultyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('faculty'));
    }

    public function rules(): array
    {
        $id = $this->route('faculty')->id;

        return [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:faculties,code,' . $id,
            'campus_id' => 'sometimes|exists:campuses,id',
        ];
    }
}
```

---

### File 46: `app/Http/Requests/StoreDepartmentRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Department::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments',
            'faculty_id' => 'required|exists:faculties,id',
        ];
    }
}
```

---

### File 47: `app/Http/Requests/UpdateDepartmentRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('department'));
    }

    public function rules(): array
    {
        $id = $this->route('department')->id;

        return [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:departments,code,' . $id,
            'faculty_id' => 'sometimes|exists:faculties,id',
        ];
    }
}
```

---

### File 48: `app/Http/Requests/StoreResearchCenterRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResearchCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\ResearchCenter::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:research_centers',
            'director_id' => 'nullable|exists:users,id',
            'parent_university_id' => 'nullable|exists:universities,id',
            'parent_campus_id' => 'nullable|exists:campuses,id',
            'parent_faculty_id' => 'nullable|exists:faculties,id',
            'description' => 'nullable|string',
        ];
    }
}
```

---

### File 49: `app/Http/Requests/UpdateResearchCenterRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResearchCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('research_center'));
    }

    public function rules(): array
    {
        $id = $this->route('research_center')->id;

        return [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:research_centers,code,' . $id,
            'director_id' => 'nullable|exists:users,id',
            'parent_university_id' => 'nullable|exists:universities,id',
            'parent_campus_id' => 'nullable|exists:campuses,id',
            'parent_faculty_id' => 'nullable|exists:faculties,id',
            'description' => 'nullable|string',
        ];
    }
}
```

---

### File 50: `app/Http/Requests/StoreExpertiseRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpertiseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Expertise::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:expertise',
        ];
    }
}
```

---

### File 51: `app/Http/Requests/UpdateExpertiseRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpertiseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('expertise'));
    }

    public function rules(): array
    {
        $id = $this->route('expertise')->id;

        return [
            'name' => 'sometimes|string|max:100|unique:expertise,name,' . $id,
        ];
    }
}
```

---

### File 52: `app/Http/Requests/StoreSettingRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Setting::class);
    }

    public function rules(): array
    {
        return [
            'key' => 'required|string|max:255|unique:settings',
            'value' => 'required|string',
            'description' => 'nullable|string',
        ];
    }
}
```

---

### File 53: `app/Http/Requests/UpdateSettingRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('setting'));
    }

    public function rules(): array
    {
        $id = $this->route('setting')->id;

        return [
            'key' => 'sometimes|string|max:255|unique:settings,key,' . $id,
            'value' => 'sometimes|string',
            'description' => 'nullable|string',
        ];
    }
}
```

---

### File 54: `app/Http/Requests/SyncRolePermissionsRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ];
    }
}
```

---

### File 55: `app/Http/Requests/AssignUserRoleRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'role_id' => 'required|exists:roles,id',
        ];
    }
}
```

---

## PHASE 5: FORM REQUESTS (DEVELOPER B – Burite)

### File 56: `app/Http/Requests/StoreCallRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Call::class);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'required|date|after:today',
            'thematic_areas' => 'nullable|string',
            'status_id' => 'required|exists:call_statuses,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'guideline_file_id' => 'nullable|exists:files,id',
        ];
    }
}
```

---

### File 57: `app/Http/Requests/UpdateCallRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('call'));
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'deadline' => 'sometimes|date',
            'thematic_areas' => 'nullable|string',
            'status_id' => 'sometimes|exists:call_statuses,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'guideline_file_id' => 'nullable|exists:files,id',
        ];
    }
}
```

---

### File 58: `app/Http/Requests/StoreReviewCriterionRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewCriterionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\ReviewCriterion::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_score' => 'required|integer|min:1|max:100',
            'is_active' => 'boolean',
        ];
    }
}
```

---

### File 59: `app/Http/Requests/UpdateReviewCriterionRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewCriterionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('review_criterion'));
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'max_score' => 'sometimes|integer|min:1|max:100',
            'is_active' => 'boolean',
        ];
    }
}
```

---

### File 60: `app/Http/Requests/StoreProposalRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Proposal::class);
    }

    public function rules(): array
    {
        return [
            'call_id' => 'nullable|exists:calls,id',
            'type_id' => 'required|exists:proposal_types,id',
            'title' => 'required|string|max:255',
            'abstract' => 'required|string',
            'objectives' => 'required|string',
            'methodology' => 'required|string',
            'keywords' => 'required|string',
            'budget' => 'required|numeric|min:0',
            'budget_allocation' => 'nullable|json',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'file_id' => 'nullable|exists:files,id',
            'investigators' => 'required|array|min:1',
            'investigators.*.user_id' => 'nullable|exists:users,id',
            'investigators.*.name' => 'required_without:investigators.*.user_id|string|max:255',
            'investigators.*.email' => 'required_without:investigators.*.user_id|email|max:255',
            'investigators.*.institution' => 'nullable|string|max:255',
            'investigators.*.role_id' => 'required|exists:investigator_roles,id',
        ];
    }
}
```

---

### File 61: `app/Http/Requests/UpdateProposalRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('proposal'));
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'abstract' => 'sometimes|string',
            'objectives' => 'sometimes|string',
            'methodology' => 'sometimes|string',
            'keywords' => 'sometimes|string',
            'budget' => 'sometimes|numeric|min:0',
            'budget_allocation' => 'nullable|json',
            'file_id' => 'nullable|exists:files,id',
        ];
    }
}
```

---

### File 62: `app/Http/Requests/SubmitProposalRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('proposal'));
    }

    public function rules(): array
    {
        return []; // No extra data required to submit
    }
}
```

---

### File 63: `app/Http/Requests/AssignReviewersRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignReviewersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'reviewer_ids' => 'required|array|min:1',
            'reviewer_ids.*' => 'required|exists:users,id',
        ];
    }
}
```

---

### File 64: `app/Http/Requests/SubmitReviewRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('reviewer');
    }

    public function rules(): array
    {
        return [
            'scores' => 'required|array|min:1',
            'scores.*.criterion_id' => 'required|exists:review_criteria,id',
            'scores.*.score' => 'required|integer|min:0',
            'scores.*.comments' => 'nullable|string',
            'overall_score' => 'required|numeric|min:0',
            'overall_comments' => 'nullable|string',
            'decision_id' => 'required|exists:review_decisions,id',
        ];
    }
}
```

---

### File 65: `app/Http/Requests/StoreFinanceCheckRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinanceCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('finance_officer');
    }

    public function rules(): array
    {
        return [
            'status_id' => 'required|exists:finance_check_statuses,id',
            'comments' => 'nullable|string',
        ];
    }
}
```

---

### File 66: `app/Http/Requests/UpdateFinanceCheckRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFinanceCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('finance_officer');
    }

    public function rules(): array
    {
        return [
            'status_id' => 'sometimes|exists:finance_check_statuses,id',
            'comments' => 'nullable|string',
        ];
    }
}
```

---

### File 67: `app/Http/Requests/StoreEthicsRequestRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEthicsRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\EthicsRequest::class);
    }

    public function rules(): array
    {
        return [];
    }
}
```

---

### File 68: `app/Http/Requests/UpdateEthicsRequestRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEthicsRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('ethics_officer', 'super_admin');
    }

    public function rules(): array
    {
        return [
            'approval_status_id' => 'required|exists:ethics_approval_statuses,id',
            'comments' => 'nullable|string',
        ];
    }
}
```

---

### File 69: `app/Http/Requests/StoreDetectionRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDetectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can request detection
    }

    public function rules(): array
    {
        return [
            'detectable_type' => 'required|string|in:App\\Models\\Proposal,App\\Models\\Output',
            'detectable_id' => 'required|integer',
            'file_id' => 'required|exists:files,id',
            'service_id' => 'required|exists:detection_services,id',
        ];
    }
}
```

---

### File 70: `app/Http/Requests/UploadFileRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\File::class);
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|max:10240', // 10 MB max
            'is_public' => 'boolean',
        ];
    }
}
```

---

### File 71: `app/Http/Requests/UpdateFileRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('file'));
    }

    public function rules(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }
}
```

---

## PHASE 6: FORM REQUESTS (DEVELOPER C – Hermela)

### File 72: `app/Http/Requests/StoreProjectRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Project::class);
    }

    public function rules(): array
    {
        return [
            'proposal_id' => 'nullable|exists:proposals,id',
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_budget' => 'required|numeric|min:0',
            'budget_allocation' => 'nullable|json',
            'status_id' => 'required|exists:project_statuses,id',
            'pi_id' => 'required|exists:users,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ];
    }
}
```

---

### File 73: `app/Http/Requests/UpdateProjectRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'total_budget' => 'sometimes|numeric|min:0',
            'budget_allocation' => 'nullable|json',
            'status_id' => 'sometimes|exists:project_statuses,id',
            'pi_id' => 'sometimes|exists:users,id',
        ];
    }
}
```

---

### File 74: `app/Http/Requests/StoreMilestoneRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'display_order' => 'integer|min:0',
            'status_id' => 'required|exists:milestone_statuses,id',
        ];
    }
}
```

---

### File 75: `app/Http/Requests/UpdateMilestoneRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'sometimes|date',
            'display_order' => 'sometimes|integer|min:0',
            'status_id' => 'sometimes|exists:milestone_statuses,id',
        ];
    }
}
```

---

### File 76: `app/Http/Requests/StoreTaskRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_hours' => 'nullable|integer|min:1',
            'actual_hours' => 'nullable|integer|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'required|date',
            'status_id' => 'required|exists:task_statuses,id',
        ];
    }
}
```

---

### File 77: `app/Http/Requests/UpdateTaskRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'estimated_hours' => 'nullable|integer|min:1',
            'actual_hours' => 'nullable|integer|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'sometimes|date',
            'status_id' => 'sometimes|exists:task_statuses,id',
        ];
    }
}
```

---

### File 78: `app/Http/Requests/StoreOutputRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOutputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Output::class);
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:output_categories,id',
            'student_level_id' => 'nullable|exists:student_levels,id',
            'subtype_id' => 'required|exists:output_subtypes,id',
            'proposal_id' => 'nullable|exists:proposals,id',
            'title' => 'required|string|max:255',
            'abstract' => 'required|string',
            'partner_id' => 'nullable|exists:partners,id',
            'project_id' => 'nullable|exists:projects,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'budget' => 'nullable|numeric|min:0',
        ];
    }
}
```

---

### File 79: `app/Http/Requests/UpdateOutputRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOutputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('output'));
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'abstract' => 'sometimes|string',
            'student_level_id' => 'nullable|exists:student_levels,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'budget' => 'nullable|numeric|min:0',
            'feedback' => 'nullable|string',
        ];
    }
}
```

---

### File 80: `app/Http/Requests/ChangeOutputStatusRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeOutputStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('output'));
    }

    public function rules(): array
    {
        return [
            'status_id' => 'required|exists:output_statuses,id',
        ];
    }
}
```

---

### File 81: `app/Http/Requests/StoreOutputParticipantRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOutputParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'participant_type_id' => 'required|exists:participant_types,id',
        ];
    }
}
```

---

### File 82: `app/Http/Requests/StorePatentRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Patent::class);
    }

    public function rules(): array
    {
        return [
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'inventors' => 'required|string',
            'filing_date' => 'required|date',
            'patent_number' => 'nullable|string|max:100',
            'status_id' => 'required|exists:patent_statuses,id',
        ];
    }
}
```

---

### File 83: `app/Http/Requests/UpdatePatentRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('patent'));
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'inventors' => 'sometimes|string',
            'filing_date' => 'sometimes|date',
            'patent_number' => 'sometimes|string|max:100',
            'status_id' => 'sometimes|exists:patent_statuses,id',
        ];
    }
}
```

---

### File 84: `app/Http/Requests/StoreLicenseRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\License::class);
    }

    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'royalty_rate' => 'required|numeric|min:0|max:100',
        ];
    }
}
```

---

### File 85: `app/Http/Requests/UpdateLicenseRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('license'));
    }

    public function rules(): array
    {
        return [
            'company_name' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'royalty_rate' => 'sometimes|numeric|min:0|max:100',
        ];
    }
}
```

---

### File 86: `app/Http/Requests/StorePartnerRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Partner::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'sector' => 'required|string|max:100',
            'contact_email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
        ];
    }
}
```

---

### File 87: `app/Http/Requests/UpdatePartnerRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('partner'));
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'sector' => 'sometimes|string|max:100',
            'contact_email' => 'sometimes|email|max:255',
            'website' => 'nullable|url|max:255',
        ];
    }
}
```

---

### File 88: `app/Http/Requests/StoreMoURequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMoURequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\MoU::class);
    }

    public function rules(): array
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ];
    }
}
```

---

### File 89: `app/Http/Requests/UpdateMoURequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMoURequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('mo_u'));
    }

    public function rules(): array
    {
        return [
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
        ];
    }
}
```

---

### File 90: `app/Http/Requests/StoreExpenseRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0',
            'budget_category' => 'required|in:personnel,equipment,travel,other',
            'description' => 'required|string',
        ];
    }
}
```

---

### File 91: `app/Http/Requests/UpdateExpenseRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('expense'));
    }

    public function rules(): array
    {
        return [
            'amount' => 'sometimes|numeric|min:0',
            'budget_category' => 'sometimes|in:personnel,equipment,travel,other',
            'description' => 'sometimes|string',
        ];
    }
}
```

---

### File 92: `app/Http/Requests/ApproveExpenseRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('finance_officer', 'super_admin');
    }

    public function rules(): array
    {
        return [];
    }
}
```

---

### File 93: `app/Http/Requests/StoreEventRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Event::class);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'venue' => 'required|string|max:255',
            'description' => 'required|string',
            'capacity' => 'nullable|integer|min:1',
            'registration_deadline' => 'nullable|date|before:start_date',
            'image_file_id' => 'nullable|exists:files,id',
        ];
    }
}
```

---

### File 94: `app/Http/Requests/UpdateEventRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('event'));
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'venue' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'capacity' => 'nullable|integer|min:1',
            'registration_deadline' => 'nullable|date|before:start_date',
            'image_file_id' => 'nullable|exists:files,id',
        ];
    }
}
```

---

### File 95: `app/Http/Requests/RegisterEventRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
```

---

### File 96: `app/Http/Requests/StorePublicationRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Publication::class);
    }

    public function rules(): array
    {
        return [
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'abstract' => 'nullable|string',
            'keywords' => 'nullable|string',
            'journal' => 'required|string|max:255',
            'doi' => 'nullable|string|max:255',
            'scholar_url' => 'nullable|url|max:255',
            'publication_date' => 'required|date',
            'citation_count' => 'integer|min:0',
            'file_id' => 'nullable|exists:files,id',
        ];
    }
}
```

---

### File 97: `app/Http/Requests/UpdatePublicationRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('publication'));
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'abstract' => 'nullable|string',
            'keywords' => 'nullable|string',
            'journal' => 'sometimes|string|max:255',
            'doi' => 'nullable|string|max:255',
            'scholar_url' => 'nullable|url|max:255',
            'publication_date' => 'sometimes|date',
            'citation_count' => 'sometimes|integer|min:0',
            'file_id' => 'nullable|exists:files,id',
        ];
    }
}
```

---

### File 98: `app/Http/Requests/StorePublicationAuthorRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicationAuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'external_author_name' => 'required_without:user_id|string|max:255',
            'external_institution' => 'nullable|string|max:255',
            'author_order' => 'required|integer|min:1',
        ];
    }
}
```

---

### File 99: `app/Http/Requests/StoreCommunityProblemRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityProblemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Anyone (even guest) can submit a community problem
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'contact_info' => 'nullable|string|max:255',
            'is_anonymous' => 'boolean',
        ];
    }
}
```

---

### File 100: `app/Http/Requests/UpdateCommunityProblemRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunityProblemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('community_problem'));
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'location' => 'sometimes|string|max:255',
            'contact_info' => 'nullable|string|max:255',
        ];
    }
}
```

---

### File 101: `app/Http/Requests/GenerateReportRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:projects,outputs,publications,expenses,community',
            'filters' => 'nullable|json',
        ];
    }
}
```

---

## PHASE 7: POLICIES (DEVELOPER A – Kenean)

### File 102: `app/Policies/UserPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }
}
```

---

### File 103: `app/Policies/RolePolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool { return $user->isAdmin(); }
    public function view(User $user, Role $role): bool { return $user->isAdmin(); }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, Role $role): bool { return $user->isAdmin(); }
    public function delete(User $user, Role $role): bool { return $user->isAdmin(); }
}
```

---

### File 104: `app/Policies/PermissionPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool { return $user->isAdmin(); }
    public function view(User $user, Permission $permission): bool { return $user->isAdmin(); }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, Permission $permission): bool { return $user->isAdmin(); }
    public function delete(User $user, Permission $permission): bool { return $user->isAdmin(); }
}
```

---

### File 105: `app/Policies/AcademicYearPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\AcademicYear;
use App\Models\User;

class AcademicYearPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, AcademicYear $academicYear): bool { return true; }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, AcademicYear $academicYear): bool { return $user->isAdmin(); }
    public function delete(User $user, AcademicYear $academicYear): bool { return $user->isAdmin(); }
}
```

---

### File 106: `app/Policies/DepartmentPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Department $department): bool { return true; }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, Department $department): bool { return $user->isAdmin(); }
    public function delete(User $user, Department $department): bool { return $user->isAdmin(); }
}
```

---

### File 107: `app/Policies/ResearchCenterPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\ResearchCenter;
use App\Models\User;

class ResearchCenterPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, ResearchCenter $researchCenter): bool { return true; }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, ResearchCenter $researchCenter): bool { return $user->isAdmin(); }
    public function delete(User $user, ResearchCenter $researchCenter): bool { return $user->isAdmin(); }
}
```

---

### File 108: `app/Policies/SettingPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    public function viewAny(User $user): bool { return $user->isAdmin(); }
    public function view(User $user, Setting $setting): bool { return $user->isAdmin(); }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, Setting $setting): bool { return $user->isAdmin(); }
    public function delete(User $user, Setting $setting): bool { return $user->isAdmin(); }
}
```

---

## PHASE 8: POLICIES (DEVELOPER B – Burite)

### File 109: `app/Policies/CallPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Call;
use App\Models\User;

class CallPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Call $call): bool { return true; }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, Call $call): bool { return $user->isAdmin(); }
    public function delete(User $user, Call $call): bool { return $user->isAdmin(); }
}
```

---

### File 110: `app/Policies/ReviewCriterionPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\ReviewCriterion;
use App\Models\User;

class ReviewCriterionPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, ReviewCriterion $reviewCriterion): bool { return true; }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, ReviewCriterion $reviewCriterion): bool { return $user->isAdmin(); }
    public function delete(User $user, ReviewCriterion $reviewCriterion): bool { return $user->isAdmin(); }
}
```

---

### File 111: `app/Policies/ProposalPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;

class ProposalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('researcher', 'reviewer', 'admin', 'super_admin');
    }

    public function view(User $user, Proposal $proposal): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->id === $proposal->submitted_by) return true;
        if ($proposal->reviewers()->where('reviewer_id', $user->id)->exists()) return true;
        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('researcher');
    }

    public function update(User $user, Proposal $proposal): bool
    {
        if ($user->isAdmin()) return true;
        return $user->id === $proposal->submitted_by && $proposal->status_id === 1; // draft
    }

    public function delete(User $user, Proposal $proposal): bool
    {
        if ($user->isAdmin()) return true;
        return $user->id === $proposal->submitted_by && $proposal->status_id === 1; // draft
    }
}
```

---

### File 112: `app/Policies/FinanceCheckPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\FinanceCheck;
use App\Models\User;

class FinanceCheckPolicy
{
    public function viewAny(User $user): bool { return $user->hasRole('finance_officer', 'admin', 'super_admin'); }
    public function view(User $user, FinanceCheck $financeCheck): bool { return $user->hasRole('finance_officer', 'admin', 'super_admin'); }
    public function create(User $user): bool { return $user->hasRole('finance_officer'); }
    public function update(User $user, FinanceCheck $financeCheck): bool { return $user->hasRole('finance_officer', 'admin', 'super_admin'); }
}
```

---

### File 113: `app/Policies/EthicsRequestPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\EthicsRequest;
use App\Models\User;

class EthicsRequestPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, EthicsRequest $ethicsRequest): bool { return true; }
    public function create(User $user): bool { return $user->hasRole('researcher'); }
    public function update(User $user, EthicsRequest $ethicsRequest): bool { return $user->hasRole('ethics_officer', 'admin', 'super_admin'); }
}
```

---

### File 114: `app/Policies/DetectionRequestPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\DetectionRequest;
use App\Models\User;

class DetectionRequestPolicy
{
    public function viewAny(User $user): bool { return $user->hasRole('admin', 'super_admin'); }
    public function view(User $user, DetectionRequest $detectionRequest): bool { return true; }
    public function create(User $user): bool { return true; }
}
```

---

### File 115: `app/Policies/FilePolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;

class FilePolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, File $file): bool { return $file->is_public || $user->isAdmin() || $file->uploaded_by === $user->id; }
    public function create(User $user): bool { return true; }
    public function update(User $user, File $file): bool { return $user->isAdmin() || $file->uploaded_by === $user->id; }
    public function delete(User $user, File $file): bool { return $user->isAdmin() || $file->uploaded_by === $user->id; }
}
```

---

## PHASE 9: POLICIES (DEVELOPER C – Hermela)

### File 116: `app/Policies/ProjectPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Project $project): bool { return true; }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, Project $project): bool { return $user->isAdmin() || $user->id === $project->pi_id; }
    public function delete(User $user, Project $project): bool { return $user->isAdmin(); }
}
```

---

### File 117: `app/Policies/TaskPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Task $task): bool { return true; }
    public function create(User $user): bool { return $user->hasRole('researcher', 'admin', 'super_admin'); }
    public function update(User $user, Task $task): bool { return $user->id === $task->assigned_to || $user->isAdmin(); }
    public function delete(User $user, Task $task): bool { return $user->isAdmin(); }
}
```

---

### File 118: `app/Policies/OutputPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Output;
use App\Models\User;

class OutputPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Output $output): bool { return true; }
    public function create(User $user): bool { return $user->hasRole('researcher', 'student', 'admin', 'super_admin'); }
    public function update(User $user, Output $output): bool {
        return $user->isAdmin() || $output->participants()->where('user_id', $user->id)->exists();
    }
    public function delete(User $user, Output $output): bool { return $user->isAdmin(); }
}
```

---

### File 119: `app/Policies/PatentPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Patent;
use App\Models\User;

class PatentPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Patent $patent): bool { return true; }
    public function create(User $user): bool { return $user->hasRole('researcher', 'admin', 'super_admin'); }
    public function update(User $user, Patent $patent): bool { return $user->isAdmin(); }
    public function delete(User $user, Patent $patent): bool { return $user->isAdmin(); }
}
```

---

### File 120: `app/Policies/LicensePolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\License;
use App\Models\User;

class LicensePolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, License $license): bool { return true; }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, License $license): bool { return $user->isAdmin(); }
    public function delete(User $user, License $license): bool { return $user->isAdmin(); }
}
```

---

### File 121: `app/Policies/PartnerPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Partner;
use App\Models\User;

class PartnerPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Partner $partner): bool { return true; }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, Partner $partner): bool { return $user->isAdmin(); }
    public function delete(User $user, Partner $partner): bool { return $user->isAdmin(); }
}
```

---

### File 122: `app/Policies/MoUPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\MoU;
use App\Models\User;

class MoUPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, MoU $moU): bool { return true; }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, MoU $moU): bool { return $user->isAdmin(); }
    public function delete(User $user, MoU $moU): bool { return $user->isAdmin(); }
}
```

---

### File 123: `app/Policies/ExpensePolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Expense $expense): bool { return true; }
    public function create(User $user): bool { return $user->hasRole('researcher', 'admin', 'super_admin'); }
    public function update(User $user, Expense $expense): bool {
        return $user->isAdmin() || ($expense->approved_by === null && $user->hasRole('researcher'));
    }
    public function delete(User $user, Expense $expense): bool { return $user->isAdmin(); }
}
```

---

### File 124: `app/Policies/EventPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Event $event): bool { return true; }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, Event $event): bool { return $user->isAdmin(); }
    public function delete(User $user, Event $event): bool { return $user->isAdmin(); }
}
```

---

### File 125: `app/Policies/PublicationPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Publication;
use App\Models\User;

class PublicationPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Publication $publication): bool { return true; }
    public function create(User $user): bool { return $user->hasRole('researcher', 'admin', 'super_admin'); }
    public function update(User $user, Publication $publication): bool { return $user->isAdmin(); }
    public function delete(User $user, Publication $publication): bool { return $user->isAdmin(); }
}
```

---

### File 126: `app/Policies/CommunityProblemPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\CommunityProblem;
use App\Models\User;

class CommunityProblemPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, CommunityProblem $communityProblem): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user, CommunityProblem $communityProblem): bool {
        return $user->isAdmin() || $user->id === $communityProblem->claimed_by;
    }
    public function delete(User $user, CommunityProblem $communityProblem): bool { return $user->isAdmin(); }
}
```

---

### File 127: `app/Policies/ReportPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool { return $user->isAdmin(); }
    public function view(User $user, Report $report): bool { return $user->isAdmin(); }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function delete(User $user, Report $report): bool { return $user->isAdmin(); }
}
```

---

## PHASE 10: CONTROLLERS (DEVELOPER A – Kenean)

### File 128: `app/Http/Controllers/AuthController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        private UserService $userService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->userService->register($request->validated());
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Account is deactivated.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully.',
            'user' => $user->load('roles'),
            'token' => $token,
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('roles.permissions', 'department'));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
```

---

### File 129: `app/Http/Controllers/UserController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles', 'department')
            ->when(request('search'), fn($q) => $q->where('name', 'LIKE', '%' . request('search') . '%')
                ->orWhere('email', 'LIKE', '%' . request('search') . '%'))
            ->when(request('role'), fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', request('role'))))
            ->when(request('is_active') !== null, fn($q) => $q->where('is_active', request('is_active')))
            ->paginate(20);

        return response()->json($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            ...$request->safe()->except('password'),
            'password' => bcrypt($request->password),
        ]);

        return response()->json($user, 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);
        return response()->json($user->load('roles.permissions', 'department', 'expertise'));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);
        $user->update($request->validated());
        return response()->json($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        $this->userService->deactivate($user);
        return response()->json(['message' => 'User deactivated.']);
    }
}
```

---

### File 130: `app/Http/Controllers/UserRoleController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignUserRoleRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserRoleController extends Controller
{
    public function __construct(
        private UserService $userService,
    ) {}

    public function assign(AssignUserRoleRequest $request, User $user): JsonResponse
    {
        $role = Role::findOrFail($request->role_id);
        $this->userService->assignRole($user, $role, $request->user());

        return response()->json(['message' => 'Role assigned successfully.']);
    }

    public function revoke(User $user, Role $role): JsonResponse
    {
        $this->userService->revokeRole($user, $role);
        return response()->json(['message' => 'Role revoked successfully.']);
    }
}
```

---

### File 131: `app/Http/Controllers/UserResearchCenterController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\ResearchCenter;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserResearchCenterController extends Controller
{
    public function attach(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'research_center_id' => 'required|exists:research_centers,id',
            'center_role_id' => 'required|exists:center_roles,id',
        ]);

        $user->researchCenters()->attach($request->research_center_id, [
            'center_role_id' => $request->center_role_id,
        ]);

        return response()->json(['message' => 'User attached to research center.']);
    }

    public function detach(User $user, ResearchCenter $researchCenter): JsonResponse
    {
        $user->researchCenters()->detach($researchCenter->id);
        return response()->json(['message' => 'User detached from research center.']);
    }
}
```

---

### File 132: `app/Http/Controllers/RoleController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Role::class);
        return response()->json(Role::with('permissions')->get());
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::create($request->validated());
        return response()->json($role, 201);
    }

    public function show(Role $role): JsonResponse
    {
        $this->authorize('view', $role);
        return response()->json($role->load('permissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $this->authorize('update', $role);
        $role->update($request->validated());
        return response()->json($role);
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);
        $role->delete();
        return response()->json(['message' => 'Role deleted.']);
    }
}
```

---

### File 133: `app/Http/Controllers/PermissionController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);
        return response()->json(Permission::all());
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = Permission::create($request->validated());
        return response()->json($permission, 201);
    }

    public function show(Permission $permission): JsonResponse
    {
        $this->authorize('view', $permission);
        return response()->json($permission);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $this->authorize('update', $permission);
        $permission->update($request->validated());
        return response()->json($permission);
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $this->authorize('delete', $permission);
        $permission->delete();
        return response()->json(['message' => 'Permission deleted.']);
    }
}
```

---

### File 134: `app/Http/Controllers/RolePermissionController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\SyncRolePermissionsRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RolePermissionController extends Controller
{
    public function sync(SyncRolePermissionsRequest $request, Role $role): JsonResponse
    {
        $role->permissions()->sync($request->permissions);
        return response()->json(['message' => 'Permissions synced.']);
    }
}
```

---

### File 135: `app/Http/Controllers/UniversityController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUniversityRequest;
use App\Http\Requests\UpdateUniversityRequest;
use App\Models\University;
use Illuminate\Http\JsonResponse;

class UniversityController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(University::with('campuses')->get());
    }

    public function store(StoreUniversityRequest $request): JsonResponse
    {
        $university = University::create($request->validated());
        return response()->json($university, 201);
    }

    public function show(University $university): JsonResponse
    {
        return response()->json($university->load('campuses.faculties.departments', 'researchCenters'));
    }

    public function update(UpdateUniversityRequest $request, University $university): JsonResponse
    {
        $university->update($request->validated());
        return response()->json($university);
    }

    public function destroy(University $university): JsonResponse
    {
        $university->delete();
        return response()->json(['message' => 'University deleted.']);
    }
}
```

---

### File 136: `app/Http/Controllers/CampusController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampusRequest;
use App\Http\Requests\UpdateCampusRequest;
use App\Models\Campus;
use Illuminate\Http\JsonResponse;

class CampusController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Campus::with('university')->get());
    }

    public function store(StoreCampusRequest $request): JsonResponse
    {
        $campus = Campus::create($request->validated());
        return response()->json($campus, 201);
    }

    public function show(Campus $campus): JsonResponse
    {
        return response()->json($campus->load('university', 'faculties'));
    }

    public function update(UpdateCampusRequest $request, Campus $campus): JsonResponse
    {
        $campus->update($request->validated());
        return response()->json($campus);
    }

    public function destroy(Campus $campus): JsonResponse
    {
        $campus->delete();
        return response()->json(['message' => 'Campus deleted.']);
    }
}
```

---

### File 137: `app/Http/Controllers/FacultyController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacultyRequest;
use App\Http\Requests\UpdateFacultyRequest;
use App\Models\Faculty;
use Illuminate\Http\JsonResponse;

class FacultyController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Faculty::with('campus')->get());
    }

    public function store(StoreFacultyRequest $request): JsonResponse
    {
        $faculty = Faculty::create($request->validated());
        return response()->json($faculty, 201);
    }

    public function show(Faculty $faculty): JsonResponse
    {
        return response()->json($faculty->load('campus', 'departments'));
    }

    public function update(UpdateFacultyRequest $request, Faculty $faculty): JsonResponse
    {
        $faculty->update($request->validated());
        return response()->json($faculty);
    }

    public function destroy(Faculty $faculty): JsonResponse
    {
        $faculty->delete();
        return response()->json(['message' => 'Faculty deleted.']);
    }
}
```

---

### File 138: `app/Http/Controllers/DepartmentController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Department::with('faculty.campus.university')->get());
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = Department::create($request->validated());
        return response()->json($department, 201);
    }

    public function show(Department $department): JsonResponse
    {
        return response()->json($department->load('faculty', 'users'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $department->update($request->validated());
        return response()->json($department);
    }

    public function destroy(Department $department): JsonResponse
    {
        $department->delete();
        return response()->json(['message' => 'Department deleted.']);
    }
}
```

---

### File 139: `app/Http/Controllers/ResearchCenterController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResearchCenterRequest;
use App\Http\Requests\UpdateResearchCenterRequest;
use App\Models\ResearchCenter;
use Illuminate\Http\JsonResponse;

class ResearchCenterController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ResearchCenter::with('director', 'parentUniversity')->get());
    }

    public function store(StoreResearchCenterRequest $request): JsonResponse
    {
        $center = ResearchCenter::create($request->validated());
        return response()->json($center, 201);
    }

    public function show(ResearchCenter $researchCenter): JsonResponse
    {
        return response()->json($researchCenter->load('director', 'users', 'parentUniversity', 'parentCampus', 'parentFaculty'));
    }

    public function update(UpdateResearchCenterRequest $request, ResearchCenter $researchCenter): JsonResponse
    {
        $researchCenter->update($request->validated());
        return response()->json($researchCenter);
    }

    public function destroy(ResearchCenter $researchCenter): JsonResponse
    {
        $researchCenter->delete();
        return response()->json(['message' => 'Research center deleted.']);
    }
}
```

---

### File 140: `app/Http/Controllers/AcademicYearController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAcademicYearRequest;
use App\Http\Requests\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Http\JsonResponse;

class AcademicYearController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(AcademicYear::orderBy('start_date', 'desc')->get());
    }

    public function store(StoreAcademicYearRequest $request): JsonResponse
    {
        $year = AcademicYear::create($request->validated());
        return response()->json($year, 201);
    }

    public function show(AcademicYear $academicYear): JsonResponse
    {
        return response()->json($academicYear);
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): JsonResponse
    {
        $academicYear->update($request->validated());
        return response()->json($academicYear);
    }

    public function destroy(AcademicYear $academicYear): JsonResponse
    {
        $academicYear->delete();
        return response()->json(['message' => 'Academic year deleted.']);
    }

    public function setCurrent(AcademicYear $academicYear): JsonResponse
    {
        AcademicYear::query()->update(['is_current' => false]);
        $academicYear->update(['is_current' => true]);
        return response()->json(['message' => 'Current academic year set.']);
    }
}
```

---

### File 141: `app/Http/Controllers/ExpertiseController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpertiseRequest;
use App\Http\Requests\UpdateExpertiseRequest;
use App\Models\Expertise;
use Illuminate\Http\JsonResponse;

class ExpertiseController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Expertise::all());
    }

    public function store(StoreExpertiseRequest $request): JsonResponse
    {
        $expertise = Expertise::create($request->validated());
        return response()->json($expertise, 201);
    }

    public function update(UpdateExpertiseRequest $request, Expertise $expertise): JsonResponse
    {
        $expertise->update($request->validated());
        return response()->json($expertise);
    }

    public function destroy(Expertise $expertise): JsonResponse
    {
        $expertise->delete();
        return response()->json(['message' => 'Expertise deleted.']);
    }
}
```

---

### File 142: `app/Http/Controllers/UserExpertiseController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserExpertiseController extends Controller
{
    public function attach(Request $request, User $user): JsonResponse
    {
        $request->validate(['expertise_id' => 'required|exists:expertise,id']);
        $user->expertise()->attach($request->expertise_id);
        return response()->json(['message' => 'Expertise attached.']);
    }

    public function detach(Request $request, User $user): JsonResponse
    {
        $request->validate(['expertise_id' => 'required|exists:expertise,id']);
        $user->expertise()->detach($request->expertise_id);
        return response()->json(['message' => 'Expertise detached.']);
    }
}
```

---

### File 143: `app/Http/Controllers/SettingController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);
        return response()->json(Setting::all());
    }

    public function store(StoreSettingRequest $request): JsonResponse
    {
        $setting = Setting::create($request->validated());
        return response()->json($setting, 201);
    }

    public function update(UpdateSettingRequest $request, Setting $setting): JsonResponse
    {
        $this->authorize('update', $setting);
        $setting->update($request->validated());
        return response()->json($setting);
    }

    public function destroy(Setting $setting): JsonResponse
    {
        $this->authorize('delete', $setting);
        $setting->delete();
        return response()->json(['message' => 'Setting deleted.']);
    }
}
```

---

### File 144: `app/Http/Controllers/NotificationController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($notifications);
    }

    public function markAsRead(string $id, Request $request): JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->update(['read_at' => now()]);
        return response()->json(['message' => 'Notification marked as read.']);
    }
}
```

---

### File 145: `app/Http/Controllers/AuditLogController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AuditLog::class);

        $logs = AuditLog::with('user')
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->table_name, fn($q) => $q->where('table_name', $request->table_name))
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->from_date, fn($q) => $q->whereDate('created_at', '>=', $request->from_date))
            ->when($request->to_date, fn($q) => $q->whereDate('created_at', '<=', $request->to_date))
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($logs);
    }
}
```

---

### File 146: `app/Http/Controllers/LanguagePreferenceController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguagePreferenceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $preference = $request->user()->languagePreference;
        return response()->json($preference ?? ['locale' => 'en']);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate(['locale' => 'required|in:en,am']);

        $preference = $request->user()->languagePreference()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['locale' => $request->locale]
        );

        return response()->json($preference);
    }
}
```

---

### File 147: `app/Http/Controllers/LookupController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class LookupController extends Controller
{
    private array $allowedTables = [
        'call_statuses', 'proposal_types', 'proposal_statuses', 'review_decisions',
        'finance_check_statuses', 'ethics_approval_statuses', 'patent_statuses',
        'community_problem_statuses', 'project_statuses', 'milestone_statuses',
        'task_statuses', 'investigator_roles', 'invitation_statuses', 'agreement_types',
        'output_categories', 'student_levels', 'output_subtypes', 'detection_services',
        'detection_statuses', 'participant_types', 'output_statuses', 'center_roles',
    ];

    public function index(string $table): JsonResponse
    {
        if (! in_array($table, $this->allowedTables)) {
            return response()->json(['message' => 'Table not found.'], 404);
        }

        if (! Schema::hasTable($table)) {
            return response()->json(['message' => 'Table does not exist.'], 404);
        }

        $results = \DB::table($table)->orderBy('id')->get(['id', 'name']);

        return response()->json($results);
    }
}
```

---

## PHASE 11: CONTROLLERS (DEVELOPER B – Burite)

### File 148: `app/Http/Controllers/CallController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCallRequest;
use App\Http\Requests\UpdateCallRequest;
use App\Models\Call;
use Illuminate\Http\JsonResponse;

class CallController extends Controller
{
    public function index(): JsonResponse
    {
        $calls = Call::with('status', 'academicYear', 'createdBy')
            ->when(request('status'), fn($q) => $q->whereHas('status', fn($s) => $s->where('name', request('status'))))
            ->orderBy('deadline', 'desc')
            ->paginate(20);

        return response()->json($calls);
    }

    public function store(StoreCallRequest $request): JsonResponse
    {
        $call = Call::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return response()->json($call, 201);
    }

    public function show(Call $call): JsonResponse
    {
        return response()->json($call->load('status', 'academicYear', 'guidelineFile', 'proposals'));
    }

    public function update(UpdateCallRequest $request, Call $call): JsonResponse
    {
        $call->update($request->validated());
        return response()->json($call);
    }

    public function destroy(Call $call): JsonResponse
    {
        $call->delete();
        return response()->json(['message' => 'Call deleted.']);
    }
}
```

---

### File 149: `app/Http/Controllers/ReviewCriterionController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewCriterionRequest;
use App\Http\Requests\UpdateReviewCriterionRequest;
use App\Models\ReviewCriterion;
use Illuminate\Http\JsonResponse;

class ReviewCriterionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ReviewCriterion::all());
    }

    public function store(StoreReviewCriterionRequest $request): JsonResponse
    {
        $criterion = ReviewCriterion::create($request->validated());
        return response()->json($criterion, 201);
    }

    public function show(ReviewCriterion $reviewCriterion): JsonResponse
    {
        return response()->json($reviewCriterion);
    }

    public function update(UpdateReviewCriterionRequest $request, ReviewCriterion $reviewCriterion): JsonResponse
    {
        $reviewCriterion->update($request->validated());
        return response()->json($reviewCriterion);
    }

    public function destroy(ReviewCriterion $reviewCriterion): JsonResponse
    {
        $reviewCriterion->delete();
        return response()->json(['message' => 'Review criterion deleted.']);
    }
}
```

---

### File 150: `app/Http/Controllers/ProposalController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignReviewersRequest;
use App\Http\Requests\StoreProposalRequest;
use App\Http\Requests\SubmitProposalRequest;
use App\Http\Requests\UpdateProposalRequest;
use App\Models\Proposal;
use App\Services\ProposalService;
use App\Services\ReviewerSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function __construct(
        private ProposalService $proposalService,
        private ReviewerSuggestionService $reviewerSuggestionService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Proposal::class);

        $proposals = Proposal::with('status', 'type', 'submittedBy', 'call')
            ->when(! $request->user()->isAdmin(), fn($q) => $q->where('submitted_by', $request->user()->id))
            ->when($request->status, fn($q) => $q->whereHas('status', fn($s) => $s->where('name', $request->status)))
            ->when($request->call_id, fn($q) => $q->where('call_id', $request->call_id))
            ->when($request->search, fn($q) => $q->where('title', 'LIKE', '%' . $request->search . '%')
                ->orWhere('keywords', 'LIKE', '%' . $request->search . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($proposals);
    }

    public function store(StoreProposalRequest $request): JsonResponse
    {
        $proposal = Proposal::create([
            ...$request->safe()->except('investigators'),
            'submitted_by' => $request->user()->id,
            'status_id' => 1, // draft
        ]);

        // Attach investigators
        foreach ($request->investigators as $investigator) {
            $proposal->investigators()->create([
                'user_id' => $investigator['user_id'] ?? null,
                'name' => $investigator['name'] ?? null,
                'email' => $investigator['email'] ?? null,
                'institution' => $investigator['institution'] ?? null,
                'role_id' => $investigator['role_id'],
                'status_id' => 1, // pending
                'invited_at' => now(),
            ]);
        }

        return response()->json($proposal->load('investigators'), 201);
    }

    public function show(Proposal $proposal): JsonResponse
    {
        $this->authorize('view', $proposal);
        return response()->json($proposal->load(
            'status', 'type', 'submittedBy', 'approvedBy', 'call',
            'investigators.user', 'reviewers', 'reviewScores.criterion',
            'financeChecks', 'ethicsRequests', 'file'
        ));
    }

    public function update(UpdateProposalRequest $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('update', $proposal);
        $proposal->update($request->validated());
        return response()->json($proposal);
    }

    public function destroy(Proposal $proposal): JsonResponse
    {
        $this->authorize('delete', $proposal);
        $proposal->delete();
        return response()->json(['message' => 'Proposal deleted.']);
    }

    public function submit(SubmitProposalRequest $request, Proposal $proposal): JsonResponse
    {
        $this->proposalService->submit($proposal, $request->user());
        return response()->json(['message' => 'Proposal submitted successfully.', 'proposal' => $proposal]);
    }

    public function approve(Proposal $proposal, Request $request): JsonResponse
    {
        $this->authorize('update', $proposal);
        $this->proposalService->approve($proposal, $request->user());
        return response()->json(['message' => 'Proposal approved. Project created.']);
    }

    public function reject(Request $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('update', $proposal);
        $request->validate(['comment' => 'required|string']);
        $this->proposalService->reject($proposal, $request->user(), $request->comment);
        return response()->json(['message' => 'Proposal rejected.']);
    }

    public function assignReviewers(AssignReviewersRequest $request, Proposal $proposal): JsonResponse
    {
        $this->proposalService->assignReviewers($proposal, $request->reviewer_ids, $request->user());
        return response()->json(['message' => 'Reviewers assigned.']);
    }

    public function suggestReviewers(Proposal $proposal): JsonResponse
    {
        $suggestions = $this->reviewerSuggestionService->suggest($proposal);
        return response()->json($suggestions);
    }
}
```

---

### File 151: `app/Http/Controllers/ProposalInvestigatorController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\ProposalInvestigator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalInvestigatorController extends Controller
{
    public function index(Proposal $proposal): JsonResponse
    {
        return response()->json($proposal->investigators()->with('user', 'role', 'status')->get());
    }

    public function store(Request $request, Proposal $proposal): JsonResponse
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required_without:user_id|string|max:255',
            'email' => 'required_without:user_id|email|max:255',
            'institution' => 'nullable|string|max:255',
            'role_id' => 'required|exists:investigator_roles,id',
        ]);

        $investigator = $proposal->investigators()->create([
            ...$request->all(),
            'status_id' => 1, // pending
            'invited_at' => now(),
        ]);

        return response()->json($investigator, 201);
    }

    public function destroy(Proposal $proposal, ProposalInvestigator $investigator): JsonResponse
    {
        $investigator->delete();
        return response()->json(['message' => 'Investigator removed.']);
    }
}
```

---

### File 152: `app/Http/Controllers/ProposalReviewerController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignReviewersRequest;
use App\Models\Proposal;
use Illuminate\Http\JsonResponse;

class ProposalReviewerController extends Controller
{
    public function index(Proposal $proposal): JsonResponse
    {
        return response()->json($proposal->reviewers);
    }

    public function store(AssignReviewersRequest $request, Proposal $proposal): JsonResponse
    {
        foreach ($request->reviewer_ids as $reviewerId) {
            $proposal->reviewers()->attach($reviewerId, [
                'assigned_by' => $request->user()->id,
                'assigned_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Reviewers assigned.']);
    }

    public function destroy(Proposal $proposal, int $reviewerId): JsonResponse
    {
        $proposal->reviewers()->detach($reviewerId);
        return response()->json(['message' => 'Reviewer removed.']);
    }
}
```

---

### File 153: `app/Http/Controllers/ReviewerProposalController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitReviewRequest;
use App\Models\Proposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewerProposalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $proposals = $request->user()->reviewedProposals()->with('status', 'type')->paginate(20);
        return response()->json($proposals);
    }

    public function show(Proposal $proposal, Request $request): JsonResponse
    {
        $isReviewer = $proposal->reviewers()->where('reviewer_id', $request->user()->id)->exists();

        if (! $isReviewer) {
            abort(403, 'You are not assigned as a reviewer for this proposal.');
        }

        // Anonymize – hide submitter name
        $proposal->setRelation('submittedBy', null);

        return response()->json($proposal->load('status', 'type', 'file', 'reviewScores.criterion'));
    }

    public function storeReview(SubmitReviewRequest $request, Proposal $proposal): JsonResponse
    {
        $reviewerId = $request->user()->id;

        // Save scores per criterion
        foreach ($request->scores as $scoreData) {
            $proposal->reviewScores()->create([
                'proposal_reviewer_id' => $proposal->reviewers()->where('reviewer_id', $reviewerId)->first()->pivot->id,
                'criterion_id' => $scoreData['criterion_id'],
                'score' => $scoreData['score'],
                'comments' => $scoreData['comments'] ?? null,
            ]);
        }

        // Update pivot with overall review
        $proposal->reviewers()->updateExistingPivot($reviewerId, [
            'overall_score' => $request->overall_score,
            'overall_comments' => $request->overall_comments,
            'decision_id' => $request->decision_id,
            'submitted_at' => now(),
        ]);

        return response()->json(['message' => 'Review submitted.']);
    }
}
```

---

### File 154: `app/Http/Controllers/FinanceCheckController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFinanceCheckRequest;
use App\Http\Requests\UpdateFinanceCheckRequest;
use App\Models\FinanceCheck;
use App\Models\Proposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceCheckController extends Controller
{
    public function store(StoreFinanceCheckRequest $request, Proposal $proposal): JsonResponse
    {
        $check = $proposal->financeChecks()->create([
            ...$request->validated(),
            'checker_id' => $request->user()->id,
            'checked_at' => now(),
        ]);

        return response()->json($check, 201);
    }

    public function update(UpdateFinanceCheckRequest $request, FinanceCheck $financeCheck): JsonResponse
    {
        $this->authorize('update', $financeCheck);
        $financeCheck->update($request->validated());
        return response()->json($financeCheck);
    }
}
```

---

### File 155: `app/Http/Controllers/EthicsRequestController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEthicsRequestRequest;
use App\Http\Requests\UpdateEthicsRequestRequest;
use App\Models\EthicsRequest;
use App\Models\Proposal;
use App\Services\EthicsService;
use Illuminate\Http\JsonResponse;

class EthicsRequestController extends Controller
{
    public function __construct(
        private EthicsService $ethicsService,
    ) {}

    public function store(StoreEthicsRequestRequest $request, Proposal $proposal): JsonResponse
    {
        $ethicsRequest = $this->ethicsService->generatePdf($proposal);
        return response()->json($ethicsRequest, 201);
    }

    public function update(UpdateEthicsRequestRequest $request, EthicsRequest $ethicsRequest): JsonResponse
    {
        $this->authorize('update', $ethicsRequest);
        $ethicsRequest->update($request->validated());
        return response()->json($ethicsRequest);
    }
}
```

---

### File 156: `app/Http/Controllers/DetectionController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDetectionRequest;
use App\Models\DetectionRequest;
use App\Jobs\ProcessDetectionJob;
use Illuminate\Http\JsonResponse;

class DetectionController extends Controller
{
    public function store(StoreDetectionRequest $request): JsonResponse
    {
        $detectionRequest = DetectionRequest::create([
            ...$request->validated(),
            'status_id' => 1, // pending
            'requested_by' => $request->user()->id,
            'requested_at' => now(),
        ]);

        ProcessDetectionJob::dispatch($detectionRequest);

        return response()->json(['message' => 'Detection requested.', 'request' => $detectionRequest], 202);
    }

    public function show(int $id): JsonResponse
    {
        $detectionRequest = DetectionRequest::with('results', 'service', 'status')->findOrFail($id);
        return response()->json($detectionRequest);
    }
}
```

---

### File 157: `app/Http/Controllers/FileController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateFileRequest;
use App\Http\Requests\UploadFileRequest;
use App\Models\File;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct(
        private FileService $fileService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $files = File::with('uploader')
            ->when(! $request->user()->isAdmin(), fn($q) => $q->where('is_public', true)
                ->orWhere('uploaded_by', $request->user()->id))
            ->when($request->search, fn($q) => $q->where('file_path', 'LIKE', '%' . $request->search . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($files);
    }

    public function upload(UploadFileRequest $request): JsonResponse
    {
        $file = $this->fileService->upload(
            $request->file('file'),
            $request->user()->id,
            $request->boolean('is_public', false)
        );

        return response()->json($file, 201);
    }

    public function download(File $file): mixed
    {
        $this->authorize('view', $file);
        return $this->fileService->download($file);
    }

    public function update(UpdateFileRequest $request, File $file): JsonResponse
    {
        $this->authorize('update', $file);
        $file->update($request->validated());
        return response()->json($file);
    }

    public function destroy(File $file): JsonResponse
    {
        $this->authorize('delete', $file);
        $this->fileService->delete($file);
        return response()->json(['message' => 'File deleted.']);
    }

    public function versions(File $file): JsonResponse
    {
        return response()->json($file->versions()->orderBy('version_number', 'desc')->get());
    }

    public function uploadNewVersion(Request $request, File $file): JsonResponse
    {
        $request->validate(['file' => 'required|file|max:10240']);
        $newFile = $this->fileService->uploadNewVersion($file, $request->file('file'));
        return response()->json($newFile);
    }
}
```

---

### File 158: `app/Http/Controllers/ProposalFileController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalFileController extends Controller
{
    public function attach(Request $request, Proposal $proposal): JsonResponse
    {
        $request->validate(['file_id' => 'required|exists:files,id']);
        $proposal->files()->attach($request->file_id);
        return response()->json(['message' => 'File attached to proposal.']);
    }

    public function detach(Proposal $proposal, int $fileId): JsonResponse
    {
        $proposal->files()->detach($fileId);
        return response()->json(['message' => 'File detached from proposal.']);
    }
}
```

---

## PHASE 12: CONTROLLERS (DEVELOPER C – Hermela)

### File 159: `app/Http/Controllers/ProjectController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Proposal;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $projects = Project::with('status', 'pi', 'academicYear')
            ->when($request->status, fn($q) => $q->whereHas('status', fn($s) => $s->where('name', $request->status)))
            ->when($request->search, fn($q) => $q->where('title', 'LIKE', '%' . $request->search . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create($request->validated());
        return response()->json($project, 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json($project->load('status', 'pi', 'milestones.tasks', 'expenses', 'publications', 'patents', 'outputs'));
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        $project->update($request->validated());
        return response()->json($project);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->json(['message' => 'Project deleted.']);
    }

    public function createFromProposal(Proposal $proposal, Request $request): JsonResponse
    {
        $project = $this->projectService->createFromProposal($proposal, $request->user());
        return response()->json($project, 201);
    }
}
```

---

### File 160: `app/Http/Controllers/MilestoneController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMilestoneRequest;
use App\Http\Requests\UpdateMilestoneRequest;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class MilestoneController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        return response()->json($project->milestones()->with('status', 'tasks')->orderBy('display_order')->get());
    }

    public function store(StoreMilestoneRequest $request, Project $project): JsonResponse
    {
        $milestone = $project->milestones()->create($request->validated());
        return response()->json($milestone, 201);
    }

    public function show(Milestone $milestone): JsonResponse
    {
        return response()->json($milestone->load('tasks'));
    }

    public function update(UpdateMilestoneRequest $request, Milestone $milestone): JsonResponse
    {
        $milestone->update($request->validated());
        return response()->json($milestone);
    }

    public function destroy(Milestone $milestone): JsonResponse
    {
        $milestone->delete();
        return response()->json(['message' => 'Milestone deleted.']);
    }
}
```

---

### File 161: `app/Http/Controllers/TaskController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Milestone;
use App\Models\Task;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function index(Milestone $milestone): JsonResponse
    {
        return response()->json($milestone->tasks()->with('assignedTo', 'status')->get());
    }

    public function store(StoreTaskRequest $request, Milestone $milestone): JsonResponse
    {
        $task = $milestone->tasks()->create($request->validated());
        return response()->json($task, 201);
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json($task->load('assignedTo', 'status'));
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);
        $task->update($request->validated());
        return response()->json($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);
        $task->delete();
        return response()->json(['message' => 'Task deleted.']);
    }
}
```

---

### File 162: `app/Http/Controllers/OutputController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeOutputStatusRequest;
use App\Http\Requests\StoreOutputRequest;
use App\Http\Requests\UpdateOutputRequest;
use App\Models\Output;
use App\Services\OutputService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutputController extends Controller
{
    public function __construct(
        private OutputService $outputService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $outputs = Output::with('category', 'status', 'subtype')
            ->when($request->status, fn($q) => $q->whereHas('status', fn($s) => $s->where('name', $request->status)))
            ->when($request->category, fn($q) => $q->whereHas('category', fn($c) => $c->where('name', $request->category)))
            ->when($request->search, fn($q) => $q->where('title', 'LIKE', '%' . $request->search . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($outputs);
    }

    public function store(StoreOutputRequest $request): JsonResponse
    {
        $output = Output::create(['status_id' => 1, ...$request->validated()]); // draft
        return response()->json($output, 201);
    }

    public function show(Output $output): JsonResponse
    {
        return response()->json($output->load('category', 'status', 'participants.user', 'files', 'project'));
    }

    public function update(UpdateOutputRequest $request, Output $output): JsonResponse
    {
        $this->authorize('update', $output);
        $output->update($request->validated());
        return response()->json($output);
    }

    public function destroy(Output $output): JsonResponse
    {
        $this->authorize('delete', $output);
        $output->delete();
        return response()->json(['message' => 'Output deleted.']);
    }

    public function changeStatus(ChangeOutputStatusRequest $request, Output $output): JsonResponse
    {
        $this->outputService->changeStatus($output, $request->status_id, $request->user());
        return response()->json(['message' => 'Status updated.']);
    }
}
```

---

### File 163: `app/Http/Controllers/OutputParticipantController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOutputParticipantRequest;
use App\Models\Output;
use Illuminate\Http\JsonResponse;

class OutputParticipantController extends Controller
{
    public function index(Output $output): JsonResponse
    {
        return response()->json($output->participants()->with('user', 'participantType')->get());
    }

    public function store(StoreOutputParticipantRequest $request, Output $output): JsonResponse
    {
        $participant = $output->participants()->create($request->validated());
        return response()->json($participant, 201);
    }

    public function destroy(Output $output, int $participantId): JsonResponse
    {
        $output->participants()->where('id', $participantId)->delete();
        return response()->json(['message' => 'Participant removed.']);
    }
}
```

---

### File 164: `app/Http/Controllers/PatentController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatentRequest;
use App\Http\Requests\UpdatePatentRequest;
use App\Models\Patent;
use Illuminate\Http\JsonResponse;

class PatentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Patent::with('status', 'project')->paginate(20));
    }

    public function store(StorePatentRequest $request): JsonResponse
    {
        $patent = Patent::create($request->validated());
        return response()->json($patent, 201);
    }

    public function show(Patent $patent): JsonResponse
    {
        return response()->json($patent->load('status', 'project', 'licenses', 'files'));
    }

    public function update(UpdatePatentRequest $request, Patent $patent): JsonResponse
    {
        $this->authorize('update', $patent);
        $patent->update($request->validated());
        return response()->json($patent);
    }

    public function destroy(Patent $patent): JsonResponse
    {
        $this->authorize('delete', $patent);
        $patent->delete();
        return response()->json(['message' => 'Patent deleted.']);
    }
}
```

---

### File 165: `app/Http/Controllers/LicenseController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLicenseRequest;
use App\Http\Requests\UpdateLicenseRequest;
use App\Models\License;
use App\Models\Patent;
use Illuminate\Http\JsonResponse;

class LicenseController extends Controller
{
    public function index(Patent $patent): JsonResponse
    {
        return response()->json($patent->licenses);
    }

    public function store(StoreLicenseRequest $request, Patent $patent): JsonResponse
    {
        $license = $patent->licenses()->create($request->validated());
        return response()->json($license, 201);
    }

    public function show(License $license): JsonResponse
    {
        return response()->json($license->load('patent'));
    }

    public function update(UpdateLicenseRequest $request, License $license): JsonResponse
    {
        $this->authorize('update', $license);
        $license->update($request->validated());
        return response()->json($license);
    }

    public function destroy(License $license): JsonResponse
    {
        $this->authorize('delete', $license);
        $license->delete();
        return response()->json(['message' => 'License deleted.']);
    }
}
```

---

### File 166: `app/Http/Controllers/PartnerController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartnerRequest;
use App\Http\Requests\UpdatePartnerRequest;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;

class PartnerController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Partner::with('moUs')->paginate(20));
    }

    public function store(StorePartnerRequest $request): JsonResponse
    {
        $partner = Partner::create($request->validated());
        return response()->json($partner, 201);
    }

    public function show(Partner $partner): JsonResponse
    {
        return response()->json($partner->load('moUs', 'outputs'));
    }

    public function update(UpdatePartnerRequest $request, Partner $partner): JsonResponse
    {
        $partner->update($request->validated());
        return response()->json($partner);
    }

    public function destroy(Partner $partner): JsonResponse
    {
        $partner->delete();
        return response()->json(['message' => 'Partner deleted.']);
    }
}
```

---

### File 167: `app/Http/Controllers/MoUController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMoURequest;
use App\Http\Requests\UpdateMoURequest;
use App\Models\MoU;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;

class MoUController extends Controller
{
    public function index(Partner $partner): JsonResponse
    {
        return response()->json($partner->moUs);
    }

    public function store(StoreMoURequest $request, Partner $partner): JsonResponse
    {
        $moU = $partner->moUs()->create($request->validated());
        return response()->json($moU, 201);
    }

    public function show(MoU $moU): JsonResponse
    {
        return response()->json($moU->load('partner'));
    }

    public function update(UpdateMoURequest $request, MoU $moU): JsonResponse
    {
        $moU->update($request->validated());
        return response()->json($moU);
    }

    public function destroy(MoU $moU): JsonResponse
    {
        $moU->delete();
        return response()->json(['message' => 'MoU deleted.']);
    }
}
```

---

### File 168: `app/Http/Controllers/ExpenseController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveExpenseRequest;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        return response()->json($project->expenses()->with('approvedBy')->paginate(20));
    }

    public function store(StoreExpenseRequest $request, Project $project): JsonResponse
    {
        $expense = $project->expenses()->create($request->validated());
        return response()->json($expense, 201);
    }

    public function show(Expense $expense): JsonResponse
    {
        return response()->json($expense->load('project', 'approvedBy'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $this->authorize('update', $expense);
        $expense->update($request->validated());
        return response()->json($expense);
    }

    public function approve(ApproveExpenseRequest $request, Expense $expense): JsonResponse
    {
        $expense->update([
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);
        return response()->json(['message' => 'Expense approved.']);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $this->authorize('delete', $expense);
        $expense->delete();
        return response()->json(['message' => 'Expense deleted.']);
    }
}
```

---

### File 169: `app/Http/Controllers/EventController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        $events = Event::with('registrations')
            ->when(request('upcoming'), fn($q) => $q->where('start_date', '>=', now()))
            ->orderBy('start_date', 'desc')
            ->paginate(20);

        return response()->json($events);
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = Event::create($request->validated());
        return response()->json($event, 201);
    }

    public function show(Event $event): JsonResponse
    {
        return response()->json($event->load('registrations.user', 'imageFile'));
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $event->update($request->validated());
        return response()->json($event);
    }

    public function destroy(Event $event): JsonResponse
    {
        $event->delete();
        return response()->json(['message' => 'Event deleted.']);
    }
}
```

---

### File 170: `app/Http/Controllers/EventRegistrationController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventRegistrationController extends Controller
{
    public function __construct(
        private EventService $eventService,
    ) {}

    public function register(Request $request, Event $event): JsonResponse
    {
        $registration = $this->eventService->register($event, $request->user()->id);
        return response()->json($registration, 201);
    }

    public function markAttendance(Request $request, Event $event): JsonResponse
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $this->eventService->markAttendance($event, $request->user_id);
        return response()->json(['message' => 'Attendance marked.']);
    }

    public function generateCertificate(Request $request, Event $event): JsonResponse
    {
        $filePath = $this->eventService->generateCertificate($event, $request->user()->id);
        return response()->json(['certificate_path' => $filePath]);
    }
}
```

---

### File 171: `app/Http/Controllers/PublicationController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $publications = Publication::with('project', 'authors.user')
            ->when($request->search, fn($q) => $q->where('title', 'LIKE', '%' . $request->search . '%')
                ->orWhere('journal', 'LIKE', '%' . $request->search . '%'))
            ->when($request->year, fn($q) => $q->whereYear('publication_date', $request->year))
            ->orderBy('publication_date', 'desc')
            ->paginate(20);

        return response()->json($publications);
    }

    public function store(StorePublicationRequest $request): JsonResponse
    {
        $publication = Publication::create($request->validated());
        return response()->json($publication, 201);
    }

    public function show(Publication $publication): JsonResponse
    {
        return response()->json($publication->load('project', 'authors.user', 'file'));
    }

    public function update(UpdatePublicationRequest $request, Publication $publication): JsonResponse
    {
        $publication->update($request->validated());
        return response()->json($publication);
    }

    public function destroy(Publication $publication): JsonResponse
    {
        $publication->delete();
        return response()->json(['message' => 'Publication deleted.']);
    }
}
```

---

### File 172: `app/Http/Controllers/PublicationAuthorController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicationAuthorRequest;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;

class PublicationAuthorController extends Controller
{
    public function index(Publication $publication): JsonResponse
    {
        return response()->json($publication->authors()->orderBy('author_order')->get());
    }

    public function store(StorePublicationAuthorRequest $request, Publication $publication): JsonResponse
    {
        $author = $publication->authors()->create($request->validated());
        return response()->json($author, 201);
    }

    public function destroy(Publication $publication, int $authorId): JsonResponse
    {
        $publication->authors()->where('id', $authorId)->delete();
        return response()->json(['message' => 'Author removed.']);
    }
}
```

---

### File 173: `app/Http/Controllers/CommunityProblemController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommunityProblemRequest;
use App\Http\Requests\UpdateCommunityProblemRequest;
use App\Models\CommunityProblem;
use App\Services\CommunityProblemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityProblemController extends Controller
{
    public function __construct(
        private CommunityProblemService $communityProblemService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $problems = CommunityProblem::with('status', 'submittedBy', 'claimedBy', 'linkedProject')
            ->when($request->status, fn($q) => $q->whereHas('status', fn($s) => $s->where('name', $request->status)))
            ->when($request->location, fn($q) => $q->where('location', 'LIKE', '%' . $request->location . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($problems);
    }

    public function store(StoreCommunityProblemRequest $request): JsonResponse
    {
        $problem = CommunityProblem::create([
            ...$request->validated(),
            'submitted_by' => $request->user()->id,
            'status_id' => 1, // open
        ]);

        return response()->json($problem, 201);
    }

    public function show(CommunityProblem $communityProblem): JsonResponse
    {
        return response()->json($communityProblem->load('status', 'submittedBy', 'claimedBy', 'linkedProject'));
    }

    public function update(UpdateCommunityProblemRequest $request, CommunityProblem $communityProblem): JsonResponse
    {
        $this->authorize('update', $communityProblem);
        $communityProblem->update($request->validated());
        return response()->json($communityProblem);
    }

    public function claim(CommunityProblem $communityProblem, Request $request): JsonResponse
    {
        $this->communityProblemService->claim($communityProblem, $request->user());
        return response()->json(['message' => 'Problem claimed.']);
    }

    public function complete(CommunityProblem $communityProblem, Request $request): JsonResponse
    {
        $this->communityProblemService->complete($communityProblem, $request->user());
        return response()->json(['message' => 'Problem marked as completed.']);
    }

    public function addFeedback(Request $request, CommunityProblem $communityProblem): JsonResponse
    {
        $request->validate([
            'feedback' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $this->communityProblemService->addFeedback($communityProblem, $request->feedback, $request->rating);
        return response()->json(['message' => 'Feedback added.']);
    }

    public function destroy(CommunityProblem $communityProblem): JsonResponse
    {
        $this->authorize('delete', $communityProblem);
        $communityProblem->delete();
        return response()->json(['message' => 'Community problem deleted.']);
    }
}
```

---

### File 174: `app/Http/Controllers/ReportController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateReportRequest;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
    ) {}

    public function index(): JsonResponse
    {
        $reports = Report::with('generatedBy')
            ->orderBy('generated_at', 'desc')
            ->paginate(20);

        return response()->json($reports);
    }

    public function generate(GenerateReportRequest $request): JsonResponse
    {
        $view = match ($request->type) {
            'projects' => 'reports.projects',
            'outputs' => 'reports.outputs',
            'publications' => 'reports.publications',
            'expenses' => 'reports.expenses',
            'community' => 'reports.community',
            default => abort(400, 'Invalid report type.'),
        };

        $filters = json_decode($request->filters, true) ?? [];
        $data = $this->getReportData($request->type, $filters);

        $report = $this->reportService->generate($request->name, $view, $data, $request->user()->id);

        return response()->json($report, 201);
    }

    public function download(Report $report): mixed
    {
        $this->authorize('view', $report);

        if (! Storage::disk('local')->exists($report->file_path)) {
            abort(404, 'Report file not found.');
        }

        return Storage::disk('local')->download($report->file_path);
    }

    private function getReportData(string $type, array $filters): array
    {
        return match ($type) {
            'projects' => ['projects' => \App\Models\Project::with('status', 'pi')->get()],
            'outputs' => ['outputs' => \App\Models\Output::with('status', 'category')->get()],
            'publications' => ['publications' => \App\Models\Publication::with('authors')->get()],
            'expenses' => ['expenses' => \App\Models\Expense::with('project')->get()],
            'community' => ['problems' => \App\Models\CommunityProblem::with('status')->get()],
            default => [],
        };
    }
}
```

---

### File 175: `app/Http/Controllers/ProjectFileController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectFileController extends Controller
{
    public function attach(Request $request, Project $project): JsonResponse
    {
        $request->validate(['file_id' => 'required|exists:files,id']);
        $project->files()->attach($request->file_id);
        return response()->json(['message' => 'File attached to project.']);
    }

    public function detach(Project $project, int $fileId): JsonResponse
    {
        $project->files()->detach($fileId);
        return response()->json(['message' => 'File detached from project.']);
    }
}
```

---

### File 176: `app/Http/Controllers/OutputFileController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Output;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutputFileController extends Controller
{
    public function attach(Request $request, Output $output): JsonResponse
    {
        $request->validate(['file_id' => 'required|exists:files,id']);
        $output->files()->attach($request->file_id);
        return response()->json(['message' => 'File attached to output.']);
    }

    public function detach(Output $output, int $fileId): JsonResponse
    {
        $output->files()->detach($fileId);
        return response()->json(['message' => 'File detached from output.']);
    }
}
```

---

### File 177: `app/Http/Controllers/PatentFileController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Patent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatentFileController extends Controller
{
    public function attach(Request $request, Patent $patent): JsonResponse
    {
        $request->validate(['file_id' => 'required|exists:files,id']);
        $patent->files()->attach($request->file_id);
        return response()->json(['message' => 'File attached to patent.']);
    }

    public function detach(Patent $patent, int $fileId): JsonResponse
    {
        $patent->files()->detach($fileId);
        return response()->json(['message' => 'File detached from patent.']);
    }
}
```

---

### File 178: `app/Http/Controllers/AgreementFileController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\AgreementFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgreementFileController extends Controller
{
    public function attach(Request $request): JsonResponse
    {
        $request->validate([
            'parent_type_id' => 'required|exists:agreement_types,id',
            'parent_id' => 'required|integer',
            'file_id' => 'required|exists:files,id',
        ]);

        $agreementFile = AgreementFile::create($request->all());
        return response()->json($agreementFile, 201);
    }

    public function detach(AgreementFile $agreementFile): JsonResponse
    {
        $agreementFile->delete();
        return response()->json(['message' => 'File detached from agreement.']);
    }
}
```

---

## PHASE 13: JOBS

### File 179: `app/Jobs/ProcessDetectionJob.php`

```php
<?php

namespace App\Jobs;

use App\Models\DetectionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private DetectionRequest $detectionRequest,
    ) {}

    public function handle(): void
    {
        // Mark as processing
        $this->detectionRequest->update(['status_id' => 2]); // processing

        try {
            // For now: simple local similarity check (placeholder)
            // In production, call external API (Turnitin, Copyleaks, etc.)
            $similarityScore = random_int(0, 100) / 100; // dummy
            $aiProbability = random_int(0, 100) / 100;   // dummy

            $this->detectionRequest->results()->create([
                'similarity_score' => $similarityScore,
                'ai_probability' => $aiProbability,
                'raw_response' => ['message' => 'Local detection completed.'],
            ]);

            $this->detectionRequest->update([
                'status_id' => 3, // completed
                'completed_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->detectionRequest->update(['status_id' => 4]); // failed
            throw $e;
        }
    }
}
```

---

### File 180: `app/Jobs/CheckMoUExpiryJob.php`

```php
<?php

namespace App\Jobs;

use App\Models\MoU;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class CheckMoUExpiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $expiringSoon = MoU::whereDate('end_date', '<=', now()->addDays(30))
            ->whereDate('end_date', '>=', now())
            ->get();

        foreach ($expiringSoon as $moU) {
            // Create in-app notification for admins
            $admins = User::whereHas('roles', fn($q) => $q->whereIn('name', ['super_admin', 'research_admin']))->get();

            foreach ($admins as $admin) {
                $admin->notifications()->create([
                    'type' => 'mou_expiring',
                    'message' => "MoU with {$moU->partner->name} expires on {$moU->end_date->format('Y-m-d')}.",
                    'created_at' => now(),
                ]);
            }
        }
    }
}
```

---

### File 181: `app/Jobs/CheckLicenseExpiryJob.php`

```php
<?php

namespace App\Jobs;

use App\Models\License;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckLicenseExpiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $expiringSoon = License::whereDate('end_date', '<=', now()->addDays(30))
            ->whereDate('end_date', '>=', now())
            ->get();

        foreach ($expiringSoon as $license) {
            $admins = User::whereHas('roles', fn($q) => $q->whereIn('name', ['super_admin', 'research_admin']))->get();

            foreach ($admins as $admin) {
                $admin->notifications()->create([
                    'type' => 'license_expiring',
                    'message' => "License for {$license->patent->title} ({$license->company_name}) expires on {$license->end_date->format('Y-m-d')}.",
                    'created_at' => now(),
                ]);
            }
        }
    }
}
```

---

## PHASE 14: ROUTES

### File 182: `routes/api.php`

```php
<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AgreementFileController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\CommunityProblemController;
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
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserExpertiseController;
use App\Http\Controllers\UserResearchCenterController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Lookups (public read)
Route::get('lookups/{table}', [LookupController::class, 'index']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Language
    Route::get('language-preference', [LanguagePreferenceController::class, 'show']);
    Route::put('language-preference', [LanguagePreferenceController::class, 'update']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // Audit logs (admin only)
    Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('role:super_admin,research_admin');

    // Settings (admin only)
    Route::apiResource('settings', SettingController::class)->only(['index', 'store', 'update', 'destroy']);

    // Academic hierarchy
    Route::apiResource('universities', UniversityController::class);
    Route::apiResource('campuses', CampusController::class);
    Route::apiResource('faculties', FacultyController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('research-centers', ResearchCenterController::class);
    Route::apiResource('academic-years', AcademicYearController::class);
    Route::post('academic-years/{academic_year}/set-current', [AcademicYearController::class, 'setCurrent']);

    // Users & Roles (admin only where needed)
    Route::apiResource('users', UserController::class);
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
    Route::apiResource('calls', CallController::class);

    // Review criteria
    Route::apiResource('review-criteria', ReviewCriterionController::class);

    // Proposals
    Route::apiResource('proposals', ProposalController::class);
    Route::post('proposals/{proposal}/submit', [ProposalController::class, 'submit']);
    Route::post('proposals/{proposal}/approve', [ProposalController::class, 'approve']);
    Route::post('proposals/{proposal}/reject', [ProposalController::class, 'reject']);
    Route::post('proposals/{proposal}/assign-reviewers', [ProposalController::class, 'assignReviewers']);
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
    Route::post('proposals/{proposal}/ethics-requests', [EthicsRequestController::class, 'store']);
    Route::put('ethics-requests/{ethics_request}', [EthicsRequestController::class, 'update']);

    // Detection
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
    Route::apiResource('projects.milestones', MilestoneController::class);
    Route::apiResource('milestones.tasks', TaskController::class);
    Route::post('projects/{project}/files', [ProjectFileController::class, 'attach']);
    Route::delete('projects/{project}/files/{file}', [ProjectFileController::class, 'detach']);

    // Outputs
    Route::apiResource('outputs', OutputController::class);
    Route::post('outputs/{output}/status', [OutputController::class, 'changeStatus']);
    Route::apiResource('outputs.participants', OutputParticipantController::class)->only(['index', 'store', 'destroy']);
    Route::post('outputs/{output}/files', [OutputFileController::class, 'attach']);
    Route::delete('outputs/{output}/files/{file}', [OutputFileController::class, 'detach']);

    // Patents
    Route::apiResource('patents', PatentController::class);
    Route::apiResource('patents.licenses', LicenseController::class);
    Route::post('patents/{patent}/files', [PatentFileController::class, 'attach']);
    Route::delete('patents/{patent}/files/{file}', [PatentFileController::class, 'detach']);

    // Partners & MoUs
    Route::apiResource('partners', PartnerController::class);
    Route::apiResource('partners.mo-us', MoUController::class);

    // Agreement files
    Route::post('agreement-files', [AgreementFileController::class, 'attach']);
    Route::delete('agreement-files/{agreement_file}', [AgreementFileController::class, 'detach']);

    // Expenses
    Route::apiResource('projects.expenses', ExpenseController::class);
    Route::put('expenses/{expense}/approve', [ExpenseController::class, 'approve']);

    // Events
    Route::apiResource('events', EventController::class);
    Route::post('events/{event}/register', [EventRegistrationController::class, 'register']);
    Route::put('events/{event}/attendance', [EventRegistrationController::class, 'markAttendance']);
    Route::post('events/{event}/certificates', [EventRegistrationController::class, 'generateCertificate']);

    // Publications
    Route::apiResource('publications', PublicationController::class);
    Route::apiResource('publications.authors', PublicationAuthorController::class);

    // Community Problems
    Route::apiResource('community-problems', CommunityProblemController::class);
    Route::post('community-problems/{community_problem}/claim', [CommunityProblemController::class, 'claim']);
    Route::post('community-problems/{community_problem}/complete', [CommunityProblemController::class, 'complete']);
    Route::post('community-problems/{community_problem}/feedback', [CommunityProblemController::class, 'addFeedback']);

    // Reports
    Route::get('reports', [ReportController::class, 'index']);
    Route::post('reports/generate', [ReportController::class, 'generate']);
    Route::get('reports/{report}/download', [ReportController::class, 'download']);
});
```

---

## PHASE 15: SCHEDULED TASKS

### File 183: `routes/console.php`

```php
<?php

use App\Jobs\CheckLicenseExpiryJob;
use App\Jobs\CheckMoUExpiryJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(CheckMoUExpiryJob::class)->dailyAt('08:00');
Schedule::job(CheckLicenseExpiryJob::class)->dailyAt('08:30');
```

---

## PHASE 16: BLADE VIEWS FOR PDFs

### File 184: `resources/views/pdfs/ethics_request.blade.php`

```html
<!DOCTYPE html>
<html>
<head>
    <title>Ethics Clearance Request</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 40px; }
        h1 { text-align: center; color: #1a5276; }
        .info { margin-top: 30px; }
        .info p { margin: 10px 0; }
        .label { font-weight: bold; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <h1>Ethics Clearance Request</h1>
    <div class="info">
        <p><span class="label">Title:</span> {{ $title }}</p>
        <p><span class="label">Abstract:</span> {{ $abstract }}</p>
        <p><span class="label">Objectives:</span> {{ $objectives }}</p>
        <p><span class="label">Methodology:</span> {{ $methodology }}</p>
        <p><span class="label">Submitted by:</span> {{ $submitted_by }}</p>
        <p><span class="label">Date:</span> {{ $date }}</p>
    </div>
    <div class="footer">
        Generated by RDRIMS - Wollo University<br>
        This document is auto-generated and does not require a signature.
    </div>
</body>
</html>
```

---

### File 185: `resources/views/pdfs/event_certificate.blade.php`

```html
<!DOCTYPE html>
<html>
<head>
    <title>Certificate of Participation</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; text-align: center; margin: 50px; }
        .certificate { border: 5px solid #1a5276; padding: 50px; }
        h1 { color: #1a5276; font-size: 36px; margin-bottom: 20px; }
        .name { font-size: 28px; margin: 30px 0; color: #2980b9; }
        .event { font-size: 20px; margin: 20px 0; }
        .date { margin-top: 40px; font-size: 16px; }
    </style>
</head>
<body>
    <div class="certificate">
        <h1>Certificate of Participation</h1>
        <p>This is to certify that</p>
        <div class="name">{{ $user_name }}</div>
        <p>has participated in</p>
        <div class="event"><strong>{{ $event_title }}</strong></div>
        <p>held on {{ $event_date }} at {{ $venue }}.</p>
        <div class="date">Issued on: {{ now()->format('F j, Y') }}</div>
    </div>
</body>
</html>
```

---

### File 186: `resources/views/reports/projects.blade.php`

```html
<!DOCTYPE html>
<html>
<head>
    <title>Projects Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 30px; }
        h1 { color: #1a5276; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #1a5276; color: white; }
    </style>
</head>
<body>
    <h1>Projects Report</h1>
    <p>Generated on: {{ now()->format('F j, Y') }}</p>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>PI</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Budget</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $project)
            <tr>
                <td>{{ $project->title }}</td>
                <td>{{ $project->status->name }}</td>
                <td>{{ $project->pi->name ?? 'N/A' }}</td>
                <td>{{ $project->start_date->format('Y-m-d') }}</td>
                <td>{{ $project->end_date->format('Y-m-d') }}</td>
                <td>{{ number_format($project->total_budget, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
```

---

## PHASE 17: FINAL INSTRUCTIONS

### File 187: Run these commands after all files are created:

```bash
# Clear all caches
php artisan optimize:clear

# Register policies (if not auto-discovered)
php artisan config:clear

# Run any pending migrations (ensure all 72 tables exist)
php artisan migrate

# Install and compile if using frontend (optional)
# npm install && npm run build

# Start the development server
php artisan serve
```

---I Found One Omission
The prompt is missing the UpdatePublicationAuthorRequest Form Request file.

File 98 (StorePublicationAuthorRequest) exists ✅

File 172 (PublicationAuthorController) uses store with StorePublicationAuthorRequest ✅

But there is no dedicated UpdatePublicationAuthorRequest file for updating author order or details.

✅ The Fix – Add This Missing File
File 98B: app/Http/Requests/UpdatePublicationAuthorRequest.php
php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePublicationAuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('publication'));
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'external_author_name' => 'nullable|string|max:255',
            'external_institution' => 'nullable|string|max:255',
            'author_order' => 'sometimes|integer|min:1',
        ];
    }
}
Then update File 172 (PublicationAuthorController) to add an update method:

php
public function update(UpdatePublicationAuthorRequest $request, Publication $publication, int $authorId): JsonResponse
{
    $author = $publication->authors()->where('id', $authorId)->firstOrFail();
    $author->update($request->validated());
    return response()->json($author);
}
And add the route in File 182 (routes/api.php), change this line:

php
Route::apiResource('publications.authors', PublicationAuthorController::class);
to:

php
Route::apiResource('publications.authors', PublicationAuthorController::class)->only(['index', 'store', 'update', 'destroy']);