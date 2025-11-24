<?php

namespace App\Interfaces;

use App\Models\File;
use Closure;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;

interface FileManager
{
    public const FILE_TYPE_QRCODE_LOGO = 'qrcode/logo';

    public const FILE_TYPE_QRCODE_DESIGN_FILE = 'qrcode/design-file';

    public const FILE_TYPE_QRCODE_FOREGROUND_IMAGE = 'qrcode/foreground_image';

    public const FILE_TYPE_QRCODE_WEBPAGEDESIGN = 'qrcode/webpage-design-files';

    public const FILE_TYPE_DYNAMIC_BIOLINK_BLOCK_FILE = 'dynamic-biolink-block/file';

    public const FILE_TYPE_TRANSLATION = 'translation/file';

    public const FILE_TYPE_TRANSACTION_PROOF_OF_PAYMENT = 'transaction/proof-of-payment';

    public const FILE_TYPE_CONFIG_ATTACHMENT = 'config/attachment';

    public const FILE_TYPE_BLOG_POST_FEATURED_IMAGE = 'blog-post/featured-image';

    public const FILE_TYPE_GENERAL_USE_FILE = 'general-use-file';

    public const UPLOAD_DIR = 'files';

    public function store(Request $request): File;

    public function validate(Request $request);

    public function resource(Request $request, File $file);

    public function delete(File $file);

    public function path(File $file);

    public function url(File $file);

    public function extension(File $file);

    /**
     * @param callable function($file)
     */
    public function setFileValidator(callable $callback);

    /**
     * @return File
     */
    public function save(
        $name,
        $type,
        $mime_type,
        $attachable_type,
        $attachable_id,
        $user_id,
        $extension = null,
        $data = null,
    );

    public function duplicate(File $file): File;

    public function exists(File $file): bool;

    public function write(File $file, $data);

    public function base64(File $file);

    /**
     * Test if it's possible to read/write files to the current storage driver.
     * @return bool 
     */
    public function testReadWrite();

    public function raw(File $file);

    public static function fs(): FilesystemAdapter;

    /**
     * Useful when using s3 disk, fetch the file locally and delete the temporary copy after executing the callback.
     * 
     * @param File $file
     * @param callable $callback which will be given path of the temporary as parameter.
     */
    public function useTempLocalFile(File $file, callable $callback);
}
