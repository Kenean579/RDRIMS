<?php

namespace Tests\Unit;

use App\Models\File;
use App\Models\Output;
use App\Models\Project;
use App\Models\ResearchCenter;
use App\Models\User;
use App\Policies\FilePolicy;
use App\Policies\OutputPolicy;
use App\Policies\ResearchCenterPolicy;
use Tests\TestCase;

class IdorPolicyTest extends TestCase
{
    public function test_private_file_is_not_visible_to_admin_from_other_institution(): void
    {
        $owner = new class extends User {
            public function isAdmin(): bool
            {
                return false;
            }

            public function hasRole(string ...$roles): bool
            {
                return false;
            }
        };
        $owner->forceFill(['id' => 1, 'university_id' => 10]);

        $viewer = new class extends User {
            public function isAdmin(): bool
            {
                return true;
            }

            public function hasRole(string ...$roles): bool
            {
                return false;
            }
        };
        $viewer->forceFill(['id' => 2, 'university_id' => 20]);

        $file = new File(['uploaded_by' => 1, 'is_public' => false]);
        $file->setRelation('uploader', $owner);

        $this->assertFalse((new FilePolicy())->view($viewer, $file));
    }

    public function test_research_center_is_not_visible_to_non_admin_from_other_institution(): void
    {
        $viewer = new class extends User {
            public function isAdmin(): bool
            {
                return false;
            }

            public function hasRole(string ...$roles): bool
            {
                return false;
            }
        };
        $viewer->forceFill(['id' => 3, 'university_id' => 30]);

        $researchCenter = new ResearchCenter(['parent_university_id' => 10]);

        $this->assertFalse((new ResearchCenterPolicy())->view($viewer, $researchCenter));
    }

    public function test_output_update_is_denied_to_admin_from_other_institution(): void
    {
        $viewer = new class extends User {
            public function isAdmin(): bool
            {
                return true;
            }

            public function hasRole(string ...$roles): bool
            {
                return false;
            }
        };
        $viewer->forceFill(['id' => 4, 'university_id' => 20]);

        $project = new Project(['pi_id' => 1]);
        $project->forceFill(['id' => 99]);
        $project->setRelation('pi', new class extends User {
            public function isAdmin(): bool
            {
                return false;
            }

            public function hasRole(string ...$roles): bool
            {
                return false;
            }
        });

        $pi = new class extends User {
            public function isAdmin(): bool
            {
                return false;
            }

            public function hasRole(string ...$roles): bool
            {
                return false;
            }
        };
        $pi->forceFill(['id' => 1, 'university_id' => 10]);
        $project->setRelation('pi', $pi);

        $output = new Output(['submitted_by' => 2]);
        $output->setRelation('project', $project);

        $this->assertFalse((new OutputPolicy())->update($viewer, $output));
    }
}
