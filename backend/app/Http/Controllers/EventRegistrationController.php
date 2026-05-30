<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventRegistrationController extends Controller
{
    public function __construct(
        private EventService $eventService,
    ) {}

    public function register(Request $request, Event $event): JsonResponse
    {
        $registration = $this->eventService->register($event, $request->user()->id);
        return response()->json($registration, 201);
    }

    public function markAttendance(Request $request, Event $event): JsonResponse
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $this->eventService->markAttendance($event, $request->user_id);
        return response()->json(['message' => 'Attendance marked.']);
    }

    public function generateCertificate(Request $request, Event $event): JsonResponse
    {
        $filePath = $this->eventService->generateCertificate($event, $request->user()->id);
        return response()->json(['certificate_path' => $filePath]);
    }

    public function destroy(Event $event, $registration): JsonResponse
    {
        \Illuminate\Support\Facades\DB::table('event_registrations')
            ->where('event_id', $event->id)
            ->where('id', $registration)
            ->delete();
            
        return response()->json(['message' => 'Registration cancelled']);
    }
}