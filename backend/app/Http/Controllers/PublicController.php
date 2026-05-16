<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Publication;
use App\Models\Event;
use Illuminate\Http\JsonResponse;

class PublicController extends Controller
{
    public function projects(): JsonResponse
    {
        return response()->json(Project::where('status_id', 1)->with('pi', 'academicYear')->paginate(20));
    }

    public function publications(): JsonResponse
    {
        return response()->json(Publication::with('authors.user')->paginate(20));
    }

    public function events(): JsonResponse
    {
        return response()->json(Event::where('start_date', '>=', now())->get());
    }

    public function projectDetails(Project $project): JsonResponse
    {
        return response()->json($project->load('pi', 'academicYear', 'proposal.investigators.user', 'outputs'));
    }
}
