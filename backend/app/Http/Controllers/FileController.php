<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateFileRequest;
use App\Http\Requests\UploadFileRequest;
use App\Models\File;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct(
        private FileService $fileService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $files = File::with('uploader')
            ->when(! $request->user()->isAdmin(), fn($q) => $q->where('is_public', true)
                ->orWhere('uploaded_by', $request->user()->id))
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

    public function download(File $file): mixed
    {
        $this->authorize('view', $file);
        return $this->fileService->download($file);
    }

    public function update(UpdateFileRequest $request, File $file): JsonResponse
    {
        $this->authorize('update', $file);
        $file->update($request->validated());
        return response()->json($file);
    }

    public function destroy(File $file): JsonResponse
    {
        $this->authorize('delete', $file);
        $this->fileService->delete($file);
        return response()->json(['message' => 'File deleted.']);
    }

    public function versions(File $file): JsonResponse
    {
        return response()->json($file->versions()->orderBy('version_number', 'desc')->get());
    }

    public function uploadNewVersion(Request $request, File $file): JsonResponse
    {
        $request->validate(['file' => 'required|file|max:10240']);
        $newFile = $this->fileService->uploadNewVersion($file, $request->file('file'));
        return response()->json($newFile);
    }
}
