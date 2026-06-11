<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'app_name', 'value' => 'RDRIMS', 'description' => 'Application name displayed in the UI and emails.'],
            ['key' => 'default_language', 'value' => 'en', 'description' => 'Default system language (en or am).'],
            ['key' => 'max_proposal_budget', 'value' => '5000000', 'description' => 'Maximum allowed budget for a single proposal in ETB.'],
            ['key' => 'min_proposal_budget', 'value' => '10000', 'description' => 'Minimum allowed budget for a proposal in ETB.'],
            ['key' => 'allow_public_registration', 'value' => 'true', 'description' => 'Whether new users can self-register.'],
            ['key' => 'require_email_verification', 'value' => 'false', 'description' => 'Whether users must verify their email before login.'],
            ['key' => 'ethics_required', 'value' => 'true', 'description' => 'Whether ethics clearance is mandatory before proposal approval.'],
            ['key' => 'plagiarism_threshold', 'value' => '20', 'description' => 'Maximum allowed similarity percentage (0-100).'],
            ['key' => 'auto_approve_below_budget', 'value' => '100000', 'description' => 'Proposals below this amount (ETB) skip finance check.'],
            ['key' => 'default_project_duration_months', 'value' => '12', 'description' => 'Default project duration in months.'],
            ['key' => 'max_reviewers_per_proposal', 'value' => '5', 'description' => 'Maximum number of reviewers per proposal.'],
            ['key' => 'min_reviewers_per_proposal', 'value' => '2', 'description' => 'Minimum number of reviewers required per proposal.'],
            ['key' => 'proposal_review_deadline_days', 'value' => '14', 'description' => 'Days reviewers have to submit their review.'],
            ['key' => 'max_file_upload_size_mb', 'value' => '10', 'description' => 'Maximum file upload size in megabytes.'],
            ['key' => 'allowed_file_types', 'value' => 'pdf,doc,docx,xlsx,csv,jpg,png', 'description' => 'Comma-separated list of allowed file extensions.'],
            ['key' => 'enable_notifications', 'value' => 'true', 'description' => 'Whether email/SMS notifications are enabled globally.'],
            ['key' => 'contact_email', 'value' => 'contact@rdrims.local', 'description' => 'Public contact email.'],
            ['key' => 'contact_phone', 'value' => '+251 900 000000', 'description' => 'Public contact phone.'],
            ['key' => 'contact_address', 'value' => 'Main Campus, Research Hub', 'description' => 'Public contact address.'],
            ['key' => 'app_description', 'value' => 'Research Data & Resource Information Management System orchestrating the complete lifecycle of academic research paradigms.', 'description' => 'Description shown in footer.'],
            ['key' => 'allow_public_problem_submission', 'value' => 'true', 'description' => 'Whether guests can submit community problems without logging in.'],
        ];
        foreach ($settings as $setting) {
            Setting::firstOrCreate($setting);
        }
    }
}
