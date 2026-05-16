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
        $path = $uploadedFile->store('files/' . date('Y/m'), 'local');
        $mimeType = $uploadedFile->getMimeType();
        $size = $uploadedFile->getSize();

        return File::create([
            'file_path' => $path,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
            'disk' => 'local',
            'version' => 1,
            'uploaded_by' => $uploadedBy,
            'is_public' => $isPublic,
            'created_at' => now(),
        ]);
    }

    public function uploadNewVersion(File $file, UploadedFile $uploadedFile): File
    {
        $path = $uploadedFile->store('files/' . date('Y/m'), 'local');
        $originalName = $uploadedFile->getClientOriginalName();
        $mimeType = $uploadedFile->getMimeType();
        $size = $uploadedFile->getSize();

        $newVersion = $file->version + 1;

        $file->update([
            'file_path' => $path,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
            'version' => $newVersion,
        ]);

        return $file;
    }

    public function delete(File $file): void
    {
        if (Storage::disk('local')->exists($file->file_path)) {
            Storage::disk('local')->delete($file->file_path);
        }
        $file->delete();
    }

    public function download(File $file): mixed
    {
        $disk = $file->disk ?? 'local';
        if (! Storage::disk($disk)->exists($file->file_path)) {
            abort(404, 'File not found on storage.');
        }
        return Storage::disk($disk)->download($file->file_path, $file->original_name ?? basename($file->file_path));
    }
}
