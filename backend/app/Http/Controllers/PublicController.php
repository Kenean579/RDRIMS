<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Publication;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PublicController extends Controller
{
    public function projects(): JsonResponse
    {
        return response()->json(Project::where('status_id', 1)->with('pi', 'academicYear', 'coverImage')->paginate(20));
    }

    public function publications(): JsonResponse
    {
        return response()->json(Publication::with('authors.user', 'file')->paginate(20));
    }

    public function events(): JsonResponse
    {
        return response()->json(Event::where('start_date', '>=', now())->with('imageFile')->get());
    }

    public function projectDetails(Project $project): JsonResponse
    {
        return response()->json($project->load('pi', 'academicYear', 'proposal.investigators.user', 'outputs'));
    }

    public function researchers(): JsonResponse
    {
        return response()->json(User::with('department.faculty', 'expertise', 'profileImage')
            ->whereHas('roles', fn($q) => $q->where('name', 'researcher'))
            ->get());
    }
}
