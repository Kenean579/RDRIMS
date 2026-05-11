<?php
// app/Services/FileVersionService.php

namespace App\Services;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileVersionService
{
    /**
     * Create a new version of an existing file.
     * Copies parent relationships from the original file.
     */
    public function createNewVersion(File $originalFile, UploadedFile $newFile, int $userId): File
    {
        $directory = dirname($originalFile->file_path);
        $path = $newFile->store($directory, 'public');

        $newVersion = File::create([
            'file_path'   => $path,
            'version'     => $originalFile->version + 1,
            'uploaded_by' => $userId,
            'is_public'   => $originalFile->is_public,
        ]);

        // Copy all parent (pivot) associations from the original file
        $pivotTables = [
            'proposal_files', 'project_files', 'output_files', 'patent_files', 'agreement_files'
        ];

        foreach ($pivotTables as $table) {
            $oldLinks = \DB::table($table)->where('file_id', $originalFile->id)->get();
            foreach ($oldLinks as $link) {
                $data = (array) $link;
                unset($data['id']);
                $data['file_id'] = $newVersion->id;
                \DB::table($table)->insert($data);
            }
        }

        return $newVersion;
    }

    /**
     * Get all versions of a file (including itself) sorted by version number.
     */
    public function getVersions(File $file): \Illuminate\Support\Collection
    {
        // Assumes all versions share the same base path pattern
        $baseName = pathinfo($file->file_path, PATHINFO_FILENAME);
        $dir = dirname($file->file_path);

        return File::where('file_path', 'like', "$dir/$baseName%")
            ->orderBy('version')
            ->get();
    }
}
