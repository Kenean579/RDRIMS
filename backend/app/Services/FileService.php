<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function upload(UploadedFile $uploadedFile, int $uploadedBy, bool $isPublic = false): File
    {
        $originalName = $uploadedFile->getClientOriginalName();
        $path = $uploadedFile->store('files/' . date('Y/m'), 'public');
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

    public function uploadNewVersion(File $file, UploadedFile $uploadedFile): File
    {
        $path = $uploadedFile->store('files/' . date('Y/m'), 'public');
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
        if (! Storage::disk($disk)->exists($file->file_path)) {
            // Fallback for old files on local
            if (Storage::disk('local')->exists($file->file_path)) {
                return Storage::disk('local')->download($file->file_path, $file->original_filename ?? basename($file->file_path));
            }
            abort(404, 'File not found on storage.');
        }
        return Storage::disk($disk)->download($file->file_path, $file->original_filename ?? basename($file->file_path));
    }
}
