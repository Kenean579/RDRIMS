<?php

namespace App\Services;

use App\Models\File;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * Upload a file, storing it under a tenant-scoped directory.
     *
     * Directory structure: files/{university_id}/{year}/{month}
     *
     * Files from different institutions are stored in separate directories,
     * preventing accidental cross-tenant access through predictable URL patterns.
     *
     * @param  UploadedFile  $uploadedFile
     * @param  int           $uploadedBy   User ID of the uploader
     * @param  bool          $isPublic     Whether the file is publicly accessible
     * @return File
     */
    public function upload(UploadedFile $uploadedFile, int $uploadedBy, bool $isPublic = false): File
    {
        $originalName = $uploadedFile->getClientOriginalName();
        $tenantPrefix = $this->getTenantPrefix($uploadedBy);
        $path = $uploadedFile->store("files/{$tenantPrefix}/" . date('Y/m'), 'public');
        $mimeType = $uploadedFile->getMimeType();

        return File::create([
            'file_path' => $path,
            'original_filename' => $originalName,
            'mime_type' => $mimeType,
            'version' => 1,
            'uploaded_by' => $uploadedBy,
            'is_public' => $isPublic,
            'created_at' => now(),
        ]);
    }

    /**
     * Upload a new version of an existing file (same tenant directory).
     */
    public function uploadNewVersion(File $file, UploadedFile $uploadedFile): File
    {
        $tenantPrefix = $this->getTenantPrefix($file->uploaded_by);
        $path = $uploadedFile->store("files/{$tenantPrefix}/" . date('Y/m'), 'public');
        $originalName = $uploadedFile->getClientOriginalName();
        $mimeType = $uploadedFile->getMimeType();

        $newVersion = $file->version + 1;

        $file->update([
            'file_path' => $path,
            'original_filename' => $originalName,
            'mime_type' => $mimeType,
            'version' => $newVersion,
        ]);

        return $file;
    }

    public function delete(File $file): void
    {
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }
        $file->delete();
    }

    public function download(File $file): mixed
    {
        $disk = 'public';
        if (!Storage::disk($disk)->exists($file->file_path)) {
            // Fallback for old files on local or without tenant prefix
            if (Storage::disk('local')->exists($file->file_path)) {
                return Storage::disk('local')->download($file->file_path, $file->original_filename ?? basename($file->file_path));
            }
            abort(404, 'File not found on storage.');
        }
        return Storage::disk($disk)->download($file->file_path, $file->original_filename ?? basename($file->file_path));
    }

    /**
     * Resolve the tenant directory prefix for the uploader.
     *
     * Returns "uni_{university_id}" if resolvable, or "shared" for global uploads
     * (e.g., super-admin uploads that don't belong to a specific institution).
     */
    private function getTenantPrefix(int $userId): string
    {
        $user = User::with('department.faculty.campus')->find($userId);

        if (!$user) {
            return 'shared';
        }

        $universityId = $user->university_id
            ?: $user->department?->faculty?->campus?->university_id;

        return $universityId ? "uni_{$universityId}" : 'shared';
    }
}
