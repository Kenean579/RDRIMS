<?php
$tables = ['universities', 'campuses', 'faculties', 'departments', 'research_centers', 'academic_years', 'roles', 'permissions', 'role_permission', 'institution_role_permission', 'center_roles', 'users', 'user_roles', 'user_research_centers', 'audit_logs', 'notifications', 'language_preferences', 'expertise', 'user_expertises', 'call_statuses', 'proposal_types', 'proposal_statuses', 'review_decisions', 'finance_check_statuses', 'ethics_approval_statuses', 'patent_statuses', 'community_problem_statuses', 'project_statuses', 'milestone_statuses', 'task_statuses', 'investigator_roles', 'invitation_statuses', 'agreement_types', 'output_categories', 'student_levels', 'output_subtypes', 'detection_services', 'detection_statuses', 'participant_types', 'output_statuses', 'calls', 'review_criteria', 'proposals', 'proposal_investigators', 'proposal_reviewers', 'proposal_review_scores', 'finance_checks', 'ethics_requests', 'detection_requests', 'detection_results', 'projects', 'milestones', 'tasks', 'files', 'proposal_files', 'project_files', 'output_files', 'patent_files', 'agreement_files', 'outputs', 'output_participants', 'patents', 'licenses', 'partners', 'mo_us', 'expenses', 'events', 'event_registrations', 'publications', 'publication_authors', 'community_problems', 'reports', 'settings'];

$missing = [];
foreach ($tables as $t) {
    if (!Schema::hasTable($t)) {
        $missing[] = $t;
    }
}
if (empty($missing)) {
    echo "All 73 tables exist.\n";
} else {
    echo "Missing tables:\n" . implode("\n", $missing) . "\n";
}
