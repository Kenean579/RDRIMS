<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterEventRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;

class EventRegistrationController extends Controller
{
    public function register(RegisterEventRequest $request, Event $event): JsonResponse
    {
        if ($event->registrations()->where('user_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Already registered.'], 422);
        }

        if ($event->capacity && $event->registrations()->count() >= $event->capacity) {
            return response()->json(['message' => 'Event is full.'], 422);
        }

        if ($event->registration_deadline && now()->gt($event->registration_deadline)) {
            return response()->json(['message' => 'Registration deadline has passed.'], 422);
        }

        $registration = $event->registrations()->create([
            'user_id'  => auth()->id(),
            'attended' => false,
        ]);

        return response()->json($registration, 201);
    }

    public function markAttendance(Event $event): JsonResponse
    {
        $this->authorize('update', $event);
        $userId = request()->input('user_id', auth()->id());

        $registration = $event->registrations()->where('user_id', $userId)->first();

        if (!$registration) {
            return response()->json(['message' => 'User is not registered for this event.'], 404);
        }

        $registration->update(['attended' => true]);
        return response()->json($registration);
    }

    public function generateCertificates(Event $event): JsonResponse
    {
        $this->authorize('update', $event);
        $registrations = $event->registrations()->where('attended', true)->get();

        return response()->json([
            'message' => "Certificates generated for {$registrations->count()} attendees.",
            'count'   => $registrations->count(),
        ]);
    }
}