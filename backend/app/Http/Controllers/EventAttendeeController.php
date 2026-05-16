<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EventAttendeeController extends Controller
{
    use AuthorizesRequests;

    public function index(Event $event): JsonResponse
    {
        return response()->json($event->attendees()->with('user')->get());
    }

    public function store(Request $request, Event $event): JsonResponse
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        
        $attendee = $event->attendees()->create(['user_id' => $request->user_id, 'registration_date' => now()]);
        return response()->json($attendee, 201);
    }

    public function destroy(Event $event, int $attendeeId): JsonResponse
    {
        $this->authorize('update', $event);
        $event->attendees()->findOrFail($attendeeId)->delete();
        return response()->json(null, 204);
    }
}
