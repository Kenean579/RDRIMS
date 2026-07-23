<?php

namespace App\Providers;

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Campus;
use App\Models\CommunityProblem;
use App\Models\Department;
use App\Models\DetectionRequest;
use App\Models\EthicsRequest;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Faculty;
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
use App\Policies\CampusPolicy;
use App\Policies\CommunityProblemPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\DetectionRequestPolicy;
use App\Policies\EthicsRequestPolicy;
use App\Policies\EventPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\FacultyPolicy;
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
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        AcademicYear::class => AcademicYearPolicy::class,
        Campus::class => CampusPolicy::class,
        Faculty::class => FacultyPolicy::class,
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
        /*
        |--------------------------------------------------------------
        | Super-Admin Global Bypass
        |--------------------------------------------------------------
        | Gate::before runs BEFORE every policy check. If the user has
        | the 'super_admin' role we return true immediately, granting
        | unrestricted CRUD on every model / ability in the system.
        | Returning null for non-super-admins lets the normal policy
        | logic execute as usual.
        |--------------------------------------------------------------
        */
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super_admin')) {
                // Deny super admin for campus, faculty, department, research_center, and call related abilities (tenant resources)
                if (str_starts_with($ability, 'campus.') 
                    || str_starts_with($ability, 'faculty.') 
                    || str_starts_with($ability, 'department.')
                    || str_starts_with($ability, 'research_center.')
                    || str_starts_with($ability, 'call.')) {
                    return false;
                }
                return true;
            }

            return null; // continue to the normal policy
        });

        // Use hierarchical permissions as a fallback for all abilities
        Gate::after(function (User $user, string $ability, $result) {
            if ($result === true) return true;
            if ($result === false) return false;
            return $user->hasPermission($ability);
        });
    }
}
