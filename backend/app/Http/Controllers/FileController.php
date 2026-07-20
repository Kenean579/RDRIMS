<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateFileRequest;
use App\Http\Requests\UploadFileRequest;
use App\Models\File;
use App\Models\Proposal;
use App\Services\FileService;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct(
        private FileService $fileService,
        private ReviewService $reviewService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenantUniversityId = $user->resolvedUniversityId();

        $files = File::with('uploader')
            ->when(! $user->hasRole('super_admin'), function ($q) use ($user, $tenantUniversityId): void {
                $q->whereHas('uploader', function ($uploaderQuery) use ($tenantUniversityId): void {
                    $uploaderQuery->where('university_id', $tenantUniversityId);
                });

                if ($user->isAdmin()) {
                    return;
                }

                $q->where(function ($scope) use ($user): void {
                    $scope->where('is_public', true)
                        ->orWhere('uploaded_by', $user->id);
                });
            })
            ->when($request->search, fn($q) => $q->where('file_path', 'LIKE', '%' . $request->search . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($files);
    }

    public function upload(UploadFileRequest $request): JsonResponse
    {
        $file = $this->fileService->upload(
            $request->file('file'),
            $request->user()->id,
            $request->boolean('is_public', false)
        );

        return response()->json($file, 201);
    }

    public function download(Request $request, File $file): mixed
    {
        $this->authorizeTenantResource($file, 'download');

        $proposal = Proposal::where('file_id', $file->id)->first();
        if ($proposal && $proposal->reviewers()->where('reviewer_id', $request->user()->id)->exists()) {
            $this->reviewService->logAction('reviewer_download_proposal', $proposal, $request->user(), [
                'file_id' => $file->id,
            ]);
        }

        return $this->fileService->download($file);
    }

    public function update(UpdateFileRequest $request, File $file): JsonResponse
    {
        $this->authorizeTenantResource($file, 'update');
        $file->update($request->validated());
        return response()->json($file);
    }

    public function destroy(File $file): JsonResponse
    {
        $this->authorizeTenantResource($file, 'delete');
        $this->fileService->delete($file);
        return response()->json(['message' => 'File deleted.']);
    }

    public function versions(File $file): JsonResponse
    {
        $this->authorizeTenantResource($file, 'view');
        return response()->json($file->versions()->orderBy('version_number', 'desc')->get());
    }

    public function uploadNewVersion(Request $request, File $file): JsonResponse
    {
        $this->authorizeTenantResource($file, 'update');
        $request->validate(['file' => 'required|file|max:10240']);
        $newFile = $this->fileService->uploadNewVersion($file, $request->file('file'));
        return response()->json($newFile);
    }
}
