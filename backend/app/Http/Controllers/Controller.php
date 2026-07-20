<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\File;
use App\Models\Project;
use App\Models\Publication;
use App\Models\Proposal;
use App\Models\ResearchCenter;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    use AuthorizesRequests;

    protected function authorizeTenantResource(mixed $resource, string $ability = 'view'): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        if ($user->hasRole('super_admin')) {
            return;
        }

        if (! $this->resourceIsInSameTenant($user, $resource)) {
            abort(403, 'You do not have access to this resource.');
        }

        $this->authorize($ability, $resource);
    }

    protected function resourceIsInSameTenant(User $user, mixed $resource): bool
    {
        if ($resource instanceof User) {
            return $user->id === $resource->id || $user->sharesInstitutionWith($resource);
        }

        if ($resource instanceof File) {
            if ($resource->uploaded_by === $user->id) {
                return true;
            }

            $uploader = $resource->relationLoaded('uploader')
                ? $resource->getRelation('uploader')
                : $resource->uploader;

            return $uploader instanceof User && $user->sharesInstitutionWith($uploader);
        }

        if ($resource instanceof Call) {
            if ($resource->created_by && (int) $resource->created_by === (int) $user->id) {
                return true;
            }

            $userUniversityId = $user->resolvedUniversityId();
            return $resource->university_id === null || (int) $resource->university_id === (int) $userUniversityId;
        }

        if ($resource instanceof Proposal) {
            if ($resource->submitted_by === $user->id) {
                return true;
            }

            $submittedBy = $resource->relationLoaded('submittedBy')
                ? $resource->getRelation('submittedBy')
                : $resource->submittedBy;

            if ($submittedBy instanceof User && $user->sharesInstitutionWith($submittedBy)) {
                return true;
            }

            if ($resource->reviewers()->where('reviewer_id', $user->id)->exists()) {
                return true;
            }

            $call = $resource->relationLoaded('call') ? $resource->getRelation('call') : $resource->call;
            if ($call && $call->university_id !== null) {
                return (int) $call->university_id === (int) $user->resolvedUniversityId();
            }

            return false;
        }

        if ($resource instanceof Project) {
            $pi = $resource->relationLoaded('pi') ? $resource->getRelation('pi') : $resource->pi;
            if ($pi instanceof User) {
                return $user->sharesInstitutionWith($pi);
            }

            return false;
        }

        if ($resource instanceof Publication) {
            $project = $resource->relationLoaded('project') ? $resource->getRelation('project') : $resource->project;
            if (! $project) {
                return false;
            }

            $pi = $project->relationLoaded('pi') ? $project->getRelation('pi') : $project->pi;
            return $pi instanceof User && $user->sharesInstitutionWith($pi);
        }

        if ($resource instanceof ResearchCenter) {
            $userUniversityId = $user->resolvedUniversityId();
            return $userUniversityId !== null
                && (int) ($resource->parent_university_id ?? $resource->university_id ?? 0) === (int) $userUniversityId;
        }

        return true;
    }
}
