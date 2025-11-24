<?php

namespace App\Http\Controllers;

use App\Events\FileDeleted;
use App\Interfaces\FileManager;
use App\Models\File;
use App\Policies\Restriction\FileRestrictor;
use App\Support\Files\UploadManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FilesController extends Controller
{
    private FileManager $files;

    public function __construct()
    {
        $this->files = app(FileManager::class);
    }

    public function chunkUpload(Request $request)
    {
        $success = UploadManager::withRequest($request)->uploadChunk();

        return compact('success');
    }

    public function chunksMerge(Request $request)
    {
        $request->merge([
            'attachable_type' => 'App\\Models\\' . $request->attachable_type,
            'type' => FileManager::FILE_TYPE_GENERAL_USE_FILE,
        ]);

        return UploadManager::withRequest($request)->mergeChunks();
    }

    public function store(Request $request)
    {
        $request->merge([
            'attachable_type' => 'App\\Models\\' . $request->attachable_type,
            'type' => FileManager::FILE_TYPE_GENERAL_USE_FILE,
        ]);

        return $this->files->store($request);
    }

    public function resource(Request $request, File $file)
    {
        try {
            return $this->files->resource($request, $file);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            abort(404, 'File not found');
        }
    }

    public function show(File $file)
    {
        return $file;
    }

    public function destroy(File $file)
    {
        FileRestrictor::make($file->id)->applyRestrictions();

        $this->files->delete($file);

        event(new FileDeleted($file));

        return $file;
    }
}
