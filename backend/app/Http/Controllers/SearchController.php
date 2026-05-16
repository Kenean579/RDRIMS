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

        $users = User::where('name', 'LIKE', "%{$q}%")->orWhere('email', 'LIKE', "%{$q}%")->limit(5)->get();
        $proposals = Proposal::where('title', 'LIKE', "%{$q}%")->limit(5)->get();
        $projects = Project::where('title', 'LIKE', "%{$q}%")->limit(5)->get();
        $publications = Publication::where('title', 'LIKE', "%{$q}%")->limit(5)->get();

        return response()->json([
            'users' => $users,
            'proposals' => $proposals,
            'projects' => $projects,
            'publications' => $publications,
        ]);
    }
}
