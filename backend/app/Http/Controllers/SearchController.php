<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = $request->query('q');
        
        if (empty($q)) {
            return response()->json(['results' => []]);
        }

        $user = $request->user();
        
        $users = User::where(function($query) use ($q) {
                $query->where('name', 'LIKE', "%{$q}%")->orWhere('email', 'LIKE', "%{$q}%");
            })
            ->hierarchical($user, 'id')
            ->limit(5)->get();

        $proposals = Proposal::where('title', 'LIKE', "%{$q}%")
            ->hierarchical($user, 'submitted_by')
            ->limit(5)->get();

        $projects = Project::where('title', 'LIKE', "%{$q}%")
            ->hierarchical($user, 'pi_id')
            ->limit(5)->get();

        $publications = Publication::where('title', 'LIKE', "%{$q}%")
            ->whereHas('project', function($query) use ($user) {
                $query->hierarchical($user, 'pi_id');
            })
            ->limit(5)->get();

        return response()->json([
            'users' => $users,
            'proposals' => $proposals,
            'projects' => $projects,
            'publications' => $publications,
        ]);
    }
}
