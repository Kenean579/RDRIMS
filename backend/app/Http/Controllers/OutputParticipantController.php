<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOutputParticipantRequest;
use App\Models\Output;
use Illuminate\Http\JsonResponse;

class OutputParticipantController extends Controller
{
    public function index(Output $output): JsonResponse
    {
        return response()->json($output->participants()->with('user', 'participantType')->get());
    }

    public function store(StoreOutputParticipantRequest $request, Output $output): JsonResponse
    {
        $participant = $output->participants()->create($request->validated());
        return response()->json($participant, 201);
    }

    public function destroy(Output $output, int $participantId): JsonResponse
    {
        $output->participants()->where('id', $participantId)->delete();
        return response()->json(['message' => 'Participant removed.']);
    }
}