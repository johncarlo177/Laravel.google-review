<?php

namespace App\Support\Files;

use App\Models\File;
use App\Repositories\FileManager;
use App\Support\MimeTypeResolver;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;


class UploadManager
{
    use WriteLogs;

    // The directory where temporary chunks are stored.
    protected $chunkDirectory = 'temp/chunks/';

    protected Request $request;

    public static function withRequest(Request $request)
    {
        $instance = new static;

        $instance->request = $request;

        return $instance;
    }



    protected function fs()
    {
        return FileManager::fs();
    }

    /**
     * Handles the upload of individual file chunks.
     */
    public function uploadChunk()
    {
        // 1. Validation and Input
        $this->request->validate([
            'file_chunk' => 'required|file',
            'upload_id' => 'required|string',
            'chunk_index' => 'required|integer',
            'total_chunks' => 'required|integer',
            'file_name' => 'required|string',
        ]);

        $chunk = $this->request->file('file_chunk');
        $uploadId = $this->request->input('upload_id');
        $chunkIndex = $this->request->input('chunk_index');

        // 2. Define chunk storage path
        // Chunks are stored in a unique folder based on the upload_id
        $tempDir = $this->chunkDirectory . $uploadId;

        // Ensure the directory exists
        $this->fs()->makeDirectory($tempDir);

        // Define the chunk file path (use index to maintain order)
        $chunkFilename = "chunk_{$chunkIndex}";

        try {
            // Store the chunk temporarily using the Storage facade

            $this->fs()->putFileAs($tempDir, $chunk, $chunkFilename);

            return true;
        } catch (\Exception $e) {
            // Log the detailed error

            $this->logError(
                'Chunk upload failed for uploadId: %s. Error: %s',
                $uploadId,
                $e->getMessage()
            );

            return false;
        }
    }

    protected function saveFile()
    {
        $file = new File;

        $file->name = $this->request->input('file_name');
        $file->type = $this->request->type;
        $file->attachable_type = $this->request->attachable_type;
        $file->attachable_id = $this->request->attachable_id;
        $file->user_id = $this->request->user()->id;
        $file->mime_type = MimeTypeResolver::resolve(
            $this->request->input('file_name')
        );

        $file->save();

        $extension = pathinfo($file->name, PATHINFO_EXTENSION);

        $file->path = FileManager::UPLOAD_DIR . '/' . $file->id . '.' . $extension;

        $this->logDebug('File path %s', $file->path);

        $file->save();

        return $file;
    }

    /**
     * Merges all uploaded chunks into the final file using only the Storage facade.
     */
    public function mergeChunks()
    {
        // 1. Validation and Input
        $this->request->validate([
            'upload_id' => 'required|string',
            'file_name' => 'required|string',
        ]);

        $uploadId = $this->request->input('upload_id');
        $tempDir = $this->chunkDirectory . $uploadId;

        // 2. Check if the temp directory exists using the Storage facade
        if (!$this->fs()->exists($tempDir)) {

            $this->logWarning(
                'Upload session not found (%s)',
                $uploadId
            );

            return false;
        }

        // 3. Get all chunk file paths and sort them by index (important!)
        // $this->fs()->files returns paths relative to the disk root (e.g., 'temp/chunks/uuid/chunk_0')
        $chunks = $this->fs()->files($tempDir);

        $this->logDebug('Chunks %s', $chunks);

        // Sort chunks numerically based on the index in their filename
        usort($chunks, function ($a, $b) {
            // Extract the number after 'chunk_' from the path
            $indexA = (int)str_replace('chunk_', '', pathinfo($a, PATHINFO_FILENAME));
            $indexB = (int)str_replace('chunk_', '', pathinfo($b, PATHINFO_FILENAME));
            return $indexA <=> $indexB;
        });

        // 4. Create the final file name and path

        $file = $this->saveFile();

        $path = $file->path;

        // 5. Merge the chunks using Storage methods
        try {

            $tempStream = tmpfile();

            // Append each chunk in order to the temp stream
            foreach ($chunks as $chunkFilePath) {
                $chunkStream = $this->fs()->readStream($chunkFilePath);

                if ($chunkStream === false) {
                    throw new \Exception("Failed to read chunk: {$chunkFilePath}");
                }

                // Stream the chunk into the temp file
                stream_copy_to_stream($chunkStream, $tempStream);
                fclose($chunkStream);

                // Delete the chunk after successful copy
                $this->fs()->delete($chunkFilePath);
            }

            // Upload the merged file as a stream
            rewind($tempStream);

            // Write the merged stream to the target disk location
            // This works with any Flysystem driver
            $this->fs()->writeStream($path, $tempStream);

            //  Cleanup
            fclose($tempStream);

            $this->fs()->deleteDirectory($tempDir);
            // 
        } catch (\Exception $e) {

            $this->logError('File merge failed for uploadId: %s. Error: %s', $uploadId, $e->getMessage());

            // Cleanup the temp directory even on failure
            $this->fs()->deleteDirectory($tempDir);

            (new FileManager)->delete($file);
        }

        // 6. Cleanup the chunk directory
        $this->fs()->deleteDirectory($tempDir);

        // 7. Success response
        return $file;
    }
}
