<?php
// app/Http/Controllers/Api/CallController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCallRequest;
use App\Http\Requests\UpdateCallRequest;
use App\Http\Resources\CallResource;
use App\Models\Call;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles all operations related to Calls for Proposals.
 */
class CallController extends Controller
{
    /**
     * Display a paginated list of calls.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Call::with(['creator', 'status', 'academicYear', 'guidelineFile']);

        // Filter by specific status ID (1=draft, 2=open, 3=closed)
        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Filter by academic year
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // If the user is not an admin or research admin, show only open calls
        $user = $request->user();
        if (!($user->hasRole('admin') || $user->hasRole('research_admin'))) {
            $query->open();
        }

        $calls = $query->latest()->paginate($request->get('per_page', 15));

        return CallResource::collection($calls);
    }

    /**
     * Store a newly created call.
     *
     * @param  \App\Http\Requests\StoreCallRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCallRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['created_by'] = $request->user()->id;
            $data['status_id'] = $data['status_id'] ?? 1; // default draft

            // Handle guideline file upload
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

    /**
     * Display the specified call.
     *
     * @param  \App\Models\Call  $call
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Call $call)
    {
        // Draft calls are hidden from non‑admin/research_admin users
        $user = request()->user();
        if ($call->status_id === 1 && !($user->hasRole('admin') || $user->hasRole('research_admin'))) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this call.',
            ], Response::HTTP_FORBIDDEN);
        }

        return new CallResource($call->load(['creator', 'status', 'academicYear', 'guidelineFile', 'proposals']));
    }

    /**
     * Update the specified call.
     *
     * @param  \App\Http\Requests\UpdateCallRequest  $request
     * @param  \App\Models\Call  $call
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCallRequest $request, Call $call)
    {
        // Prevent editing if proposals already exist
        if ($call->proposals()->exists() && $request->hasAny(['title', 'description', 'deadline', 'thematic_areas'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify call details because proposals have already been submitted.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Replace guideline file if a new one is uploaded
            if ($request->hasFile('guideline_file')) {
                // Delete old file
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

    /**
     * Remove the specified call.
     *
     * @param  \App\Models\Call  $call
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Call $call)
    {
        // Prevent deletion if proposals exist
        if ($call->proposals()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete call because proposals have already been submitted.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Remove associated guideline file
        if ($call->guideline_file_id) {
            $this->deleteFileRecord($call->guidelineFile);
        }

        $call->delete();

        return response()->json([
            'success' => true,
            'message' => 'Call deleted successfully.',
        ], Response::HTTP_OK);
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Store an uploaded file into the `files` table and return the model.
     *
     * @param  \Illuminate\Http\UploadedFile  $uploadedFile
     * @param  int  $userId
     * @return \App\Models\File
     */
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

    /**
     * Delete a file record and its physical file from storage.
     *
     * @param  \App\Models\File|null  $file
     * @return void
     */
    private function deleteFileRecord(?File $file): void
    {
        if ($file && Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }
        $file?->delete();
    }
}
