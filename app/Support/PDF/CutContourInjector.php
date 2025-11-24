<?php

namespace App\Support\PDF;

use App\Models\File;
use App\Models\User;
use App\Repositories\FileManager;
use Illuminate\Contracts\Filesystem\Filesystem;
use InvalidArgumentException;
use RuntimeException;

class CutContourInjector
{
    private string $inputFile;
    private string $tempFile;
    private string $outputFile;
    private string $compressedOutputFile;
    private string $targetStrokeRGB;
    private string $spotColorName = '/CutContour';
    private string $spotColorDefinition = "[/Separation /CutContour /DeviceRGB << /FunctionType 2 /Domain [0 1] /Range [0 1 0 1 0 1] /C0 [1 0 1] /C1 [1 0 1] /N 1 >>]";

    public static function testRun()
    {
        shell_exec(
            sprintf(
                'cd %s; rm -rf pdf_*.pdf compressed*.pdf',
                __DIR__,
            )
        );

        $processor = new static(
            inputFile: __DIR__ . '/input.pdf',
            compressedOutputFile: __DIR__ . '/compressed-output.pdf',
        );

        $processor->process();
    }

    public static function clearTestFiles()
    {
        $path = __DIR__;

        $files = glob($path . '/*.pdf');

        foreach ($files as $file) {
            unlink($file);
        }
    }

    public static function withFile(File $file)
    {
        $path = (new FileManager)->path($file);

        return new static($path, sys_get_temp_dir() . '/cutcontour_' . uniqid() . '.pdf');
    }

    public function __construct(
        string $inputFile,
        string $compressedOutputFile,
        string $targetStrokeRGB = '1 0 1 RG'
    ) {
        $this->inputFile = $inputFile;
        $this->compressedOutputFile = $compressedOutputFile;
        $this->outputFile = $this->getTmpDir() . '/pdf_uncompressed_processed_' . uniqid() . '.pdf';
        $this->tempFile = $this->getTmpDir() . '/pdf_uncompressed_' . uniqid() . '.pdf';
        $this->targetStrokeRGB = $targetStrokeRGB;
    }


    protected function getTmpDir()
    {
        if (app()->environment('local')) {
            return __DIR__;
        }

        return sys_get_temp_dir();
    }

    public function saveResult(User $byUser): File
    {
        $manager = new FileManager;

        $file = $manager->save(
            name: 'cut-contour.pdf',
            type: FileManager::FILE_TYPE_GENERAL_USE_FILE,
            mime_type: 'application/pdf',
            attachable_type: User::class,
            attachable_id: null,
            user_id: $byUser->id,
            extension: 'pdf',
            data: file_get_contents($this->compressedOutputFile)
        );

        $this->cleanup();

        return $file;
    }

    /**
     * Main function to process PDF: decompress, inject spot color, replace stroke color, save output.
     */
    public function process()
    {
        $this->decompressPdf();

        $content = $this->loadPdfContent();
        $content = $this->injectSpotColorInResources($content);
        $content = $this->replaceStrokeColorWithSpotColor($content);

        $this->savePdfContent($content);
        $this->compressOutputPdfFile();

        return $this;
    }

    /**
     * Runs qpdf to decompress the PDF file.
     */
    private function decompressPdf(): void
    {
        $cmd = sprintf(
            'qpdf --qdf --object-streams=disable %s %s',
            escapeshellarg($this->inputFile),
            escapeshellarg($this->tempFile)
        );

        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new RuntimeException("Failed to decompress PDF using qpdf.");
        }
    }

    protected function compressOutputPdfFile()
    {
        $cmd = sprintf(
            'qpdf %s %s',
            escapeshellarg($this->outputFile),
            escapeshellarg($this->compressedOutputFile),
        );

        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            // qpdf will give a warning about compressing but file
            // will be actually generated 

            // throw new RuntimeException("Failed to compress PDF using qpdf.");
        }
    }

    /**
     * Loads the decompressed PDF content into memory.
     */
    private function loadPdfContent(): string
    {
        $content = file_get_contents($this->tempFile);

        if ($content === false) {
            throw new RuntimeException("Failed to read decompressed PDF content.");
        }
        return $content;
    }

    /**
     * Injects the named spot color into /ColorSpace of each page's /Resources.
     */
    private function injectSpotColorInResources(string $pdfContent): string
    {
        $spotColorEntry = $this->spotColorName . ' ' . $this->spotColorDefinition;

        $definition = "\n/ColorSpace << " . $spotColorEntry . " >>";

        return preg_replace('/\/Parent 3 0 R\s+\/Resources <</', "/Parent 3 0 R\n/Resources <<$definition", $pdfContent);
    }

    /**
     * Replaces the specified RGB stroke color with the spot color usage commands.
     */
    private function replaceStrokeColorWithSpotColor(string $pdfContent): string
    {
        $spotColorUse = $this->spotColorName . " CS\n1 SCN"; // stroking color space + tint

        return str_replace($this->targetStrokeRGB, $spotColorUse, $pdfContent);
    }

    /**
     * Saves the modified PDF content to output file.
     */
    private function savePdfContent(string $content): void
    {
        if (file_put_contents($this->outputFile, $content) === false) {
            throw new RuntimeException("Failed to save modified PDF to output file.");
        }
    }

    /**
     * Deletes the temporary decompressed PDF file.
     */
    private function cleanup(): void
    {
        if (app()->environment('local')) {
            return;
        }

        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }

        if (file_exists($this->outputFile)) {
            unlink($this->outputFile);
        }

        if (file_exists($this->compressedOutputFile)) {
            unlink($this->compressedOutputFile);
        }
    }
}
