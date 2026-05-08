<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Projects & Tasks
        Gate::policy(\App\Models\Project::class, \App\Policies\ProjectPolicy::class);
        Gate::policy(\App\Models\Task::class, \App\Policies\TaskPolicy::class);

        // Outputs
        Gate::policy(\App\Models\Output::class, \App\Policies\OutputPolicy::class);

        // Patents & Licenses
        Gate::policy(\App\Models\Patent::class, \App\Policies\PatentPolicy::class);
        Gate::policy(\App\Models\License::class, \App\Policies\LicensePolicy::class);

        // Partners & MoUs
        Gate::policy(\App\Models\Partner::class, \App\Policies\PartnerPolicy::class);
        Gate::policy(\App\Models\MoU::class, \App\Policies\MoUPolicy::class);

        // Expenses
        Gate::policy(\App\Models\Expense::class, \App\Policies\ExpensePolicy::class);

        // Events
        Gate::policy(\App\Models\Event::class, \App\Policies\EventPolicy::class);

        // Publications
        Gate::policy(\App\Models\Publication::class, \App\Policies\PublicationPolicy::class);

        // Community Problems
        Gate::policy(\App\Models\CommunityProblem::class, \App\Policies\CommunityProblemPolicy::class);

        // Reports
        Gate::policy(\App\Models\Report::class, \App\Policies\ReportPolicy::class);
    }
}