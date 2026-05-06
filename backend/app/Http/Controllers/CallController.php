<?php
// app/Http/Controllers/Api/CallController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCallRequest;
use App\Http\Requests\UpdateCallRequest;
use App\Http\Resources\CallResource;
use App\Models\Call;
use App\Models\CallStatus;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CallController extends Controller
{
    public function index(Request $request)
    {
        $query = Call::with(['creator', 'status', 'academicYear', 'guidelineFile']);

        // Dynamic filter by status name (optional)
        if ($request->has('status_name')) {
            $statusId = CallStatus::where('name', $request->status_name)->value('id');
            if ($statusId) {
                $query->where('status_id', $statusId);
            }
        }

        // Non‑admins see only 'open' calls
        $user = $request->user();
        if (!($user->hasRole('admin') || $user->hasRole('research_admin'))) {
            $openId = CallStatus::where('name', 'open')->value('id');
            $query->where('status_id', $openId);
        }

        $calls = $query->latest()->paginate($request->get('per_page', 15));
        return CallResource::collection($calls);
    }

    public function store(StoreCallRequest $request)
    {
        try {
            DB::beginTransaction();

            // Resolve status ID dynamically
            $statusId = CallStatus::where('name', $request->status_name)->firstOrFail()->id;

            $data = $request->validated();
            $data['created_by'] = $request->user()->id;
            $data['status_id'] = $statusId;

            if ($request->hasFile('guideline_file')) {
                $fileRecord = $this->storeFile($request->file('guideline_file'), $request->user()->id);
                $data['guideline_file_id'] = $fileRecord->id;
            }

            $call = Call::create($data);

            DB::commit();

            return (new CallResource($call->load(['creator', 'status', 'academicYear', 'guidelineFile'])))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create call: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Call $call)
    {
        $user = request()->user();
        $draftId = CallStatus::where('name', 'draft')->value('id');

        if ($call->status_id === $draftId && !($user->hasRole('admin') || $user->hasRole('research_admin'))) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this call.',
            ], Response::HTTP_FORBIDDEN);
        }

        return new CallResource($call->load(['creator', 'status', 'academicYear', 'guidelineFile', 'proposals']));
    }

    public function update(UpdateCallRequest $request, Call $call)
    {
        if ($call->proposals()->exists() && $request->hasAny(['title', 'description', 'deadline', 'thematic_areas'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify call details because proposals exist.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();

            // If status_name is provided, resolve new status_id
            if (isset($data['status_name'])) {
                $data['status_id'] = CallStatus::where('name', $data['status_name'])->value('id');
                unset($data['status_name']);
            }

            if ($request->hasFile('guideline_file')) {
                if ($call->guideline_file_id) {
                    $this->deleteFileRecord($call->guidelineFile);
                }
                $fileRecord = $this->storeFile($request->file('guideline_file'), $request->user()->id);
                $data['guideline_file_id'] = $fileRecord->id;
            }

            $call->update($data);

            DB::commit();

            return new CallResource($call->load(['creator', 'status', 'academicYear', 'guidelineFile']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update call: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Call $call)
    {
        if ($call->proposals()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete call because proposals exist.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($call->guideline_file_id) {
            $this->deleteFileRecord($call->guidelineFile);
        }

        $call->delete();

        return response()->json([
            'success' => true,
            'message' => 'Call deleted successfully.',
        ], Response::HTTP_OK);
    }

    // ---- Helpers (same as before) ----
    private function storeFile($uploadedFile, int $userId): File
    {
        $path = $uploadedFile->store('guidelines', 'public');
        return File::create([
            'file_path'   => $path,
            'version'     => 1,
            'uploaded_by' => $userId,
            'is_public'   => true,
        ]);
    }

    private function deleteFileRecord(?File $file): void
    {
        if ($file && Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }
        $file?->delete();
    }
}
