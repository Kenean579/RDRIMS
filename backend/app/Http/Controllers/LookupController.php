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
        'output_categories', 'output_statuses', 'output_subtypes', 'center_roles',
        'publication_statuses', 'publication_types',
        'thematic_areas', 'academic_years',
        'student_levels', 'participant_types',
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

    public function store(string $table, \Illuminate\Http\Request $request): JsonResponse
    {
        if (! in_array($table, $this->allowedTables)) return response()->json(['message' => 'Table not allowed.'], 403);
        $request->validate(['name' => 'required|string|max:255']);
        
        $id = \DB::table($table)->insertGetId([
            'name' => $request->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json(['id' => $id, 'name' => $request->name], 201);
    }

    public function update(string $table, int $id, \Illuminate\Http\Request $request): JsonResponse
    {
        if (! in_array($table, $this->allowedTables)) return response()->json(['message' => 'Table not allowed.'], 403);
        $request->validate(['name' => 'required|string|max:255']);
        
        \DB::table($table)->where('id', $id)->update([
            'name' => $request->name,
            'updated_at' => now(),
        ]);
        
        return response()->json(['id' => $id, 'name' => $request->name]);
    }

    public function destroy(string $table, int $id): JsonResponse
    {
        if (! in_array($table, $this->allowedTables)) return response()->json(['message' => 'Table not allowed.'], 403);
        
        try {
            \DB::table($table)->where('id', $id)->delete();
            return response()->json(['message' => 'Deleted.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Cannot delete this record because it is in use.'], 400);
        }
    }
}
