<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        $events = Event::with('registrations', 'imageFile')
            ->when(request('upcoming'), fn($q) => $q->where('start_date', '>=', now()))
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->orderBy('start_date', 'desc')
            ->paginate(20);

        return response()->json($events);
    }

    public function store(StoreEventRequest $request, FileService $fileService): JsonResponse
    {
        $uploadedImage = null;

        try {
            $event = DB::transaction(function () use ($request, $fileService, &$uploadedImage) {
                $data = $request->safe()->except('image');
                $data['created_by'] = $request->user()->id;
                $data['university_id'] = $request->user()->resolvedUniversityId();

                $event = Event::create($data);

                if ($request->hasFile('image')) {
                    $uploadedImage = $this->storeImage($request, $event, $fileService);
                    $event->update(['image_file_id' => $uploadedImage->id]);
                }

                return $event;
            });
        } catch (\Throwable $exception) {
            if ($uploadedImage) {
                $fileService->delete($uploadedImage);
            }
            throw $exception;
        }

        return response()->json($event->load('imageFile'), 201);
    }

    public function show(Event $event): JsonResponse
    {
        return response()->json($event->load('registrations.user', 'imageFile'));
    }

    public function update(UpdateEventRequest $request, Event $event, FileService $fileService): JsonResponse
    {
        $oldImage = $event->imageFile;
        $uploadedImage = null;

        try {
            DB::transaction(function () use ($request, $event, $fileService, &$uploadedImage) {
                $event->update($request->safe()->except('image'));

                if ($request->hasFile('image')) {
                    $uploadedImage = $this->storeImage($request, $event, $fileService);
                    $event->update(['image_file_id' => $uploadedImage->id]);
                }
            });
        } catch (\Throwable $exception) {
            if ($uploadedImage) {
                $fileService->delete($uploadedImage);
            }
            throw $exception;
        }

        if ($uploadedImage && $oldImage && $this->isOwnedEventImage($oldImage, $event)) {
            $fileService->delete($oldImage);
        }

        return response()->json($event->fresh()->load('imageFile'));
    }

    private function storeImage(Request $request, Event $event, FileService $fileService)
    {
        $image = $fileService->upload($request->file('image'), $request->user()->id, true);
        $image->update(['metadata' => [
            'purpose' => 'event_image',
            'event_id' => $event->id,
        ]]);

        return $image;
    }

    private function isOwnedEventImage($file, Event $event): bool
    {
        return data_get($file->metadata, 'purpose') === 'event_image'
            && (int) data_get($file->metadata, 'event_id') === $event->id;
    }

    public function destroy(Event $event): JsonResponse
    {
        $event->delete();
        return response()->json(['message' => 'Event deleted.']);
    }
}
