<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class EventService
{
    public function register(Event $event, int $userId): EventRegistration
    {
        if ($event->registrations()->where('user_id', $userId)->exists()) {
            abort(422, 'You are already registered for this event.');
        }

        if ($event->capacity && $event->registrations()->count() >= $event->capacity) {
            abort(422, 'This event has reached its capacity.');
        }

        if ($event->registration_deadline && now()->gt($event->registration_deadline)) {
            abort(422, 'Registration deadline has passed.');
        }

        return EventRegistration::create([
            'event_id' => $event->id,
            'user_id'  => $userId,
            'attended' => false,
        ]);
    }

    public function markAttendance(Event $event, int $userId): void
    {
        $registration = $event->registrations()->where('user_id', $userId)->firstOrFail();
        $registration->update(['attended' => true]);
    }

    public function generateCertificate(Event $event, int $userId): string
    {
        $registration = $event->registrations()
            ->where('user_id', $userId)
            ->where('attended', true)
            ->firstOrFail();

        $user = $registration->user;
        $pdf = Pdf::loadView('pdfs.event_certificate', [
            'user_name'   => $user->name,
            'event_title' => $event->title,
            'event_date'  => $event->start_date->format('F j, Y'),
            'venue'       => $event->venue,
        ]);

        $path = 'certificates/' . $event->id . '_' . $userId . '_' . time() . '.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }
}
