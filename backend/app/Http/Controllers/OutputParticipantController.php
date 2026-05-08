<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOutputParticipantRequest;
use App\Models\Output;
use Illuminate\Http\JsonResponse;

class OutputParticipantController extends Controller
{
    public function index(Output $output): JsonResponse
    {
        $this->authorize('view', $output);
        $participants = $output->participants()->with('participantType', 'user')->get();
        return response()->json($participants);
    }

    public function store(StoreOutputParticipantRequest $request, Output $output): JsonResponse
    {
        $existing = $output->participants()
            ->where('user_id', $request->user_id)
            ->where('participant_type_id', $request->participant_type_id)
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'Participant already added.'], 422);
        }

        $participant = $output->participants()->create($request->validated());
        return response()->json($participant, 201);
    }

    public function destroy(Output $output, $participantId): JsonResponse
    {
        $this->authorize('update', $output);
        $participant = $output->participants()->findOrFail($participantId);
        $participant->delete();
        return response()->json(null, 204);
    }
}