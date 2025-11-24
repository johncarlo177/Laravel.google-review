<?php

namespace App\Support\System;

use Throwable;
use ZipArchive;

class LogFileManager
{
    public function __construct()
    {
    }

    public function getLogFileTail($base64 = true)
    {
        try {
            $data = $this->tail($this->logFilePath(), 500);
        } catch (Throwable $th) {
            $data = [];
        }

        $data = implode("\n", $data);

        if ($base64) {
            return base64_encode($data);
        }

        return $data;
    }

    protected function serveTextLogFile()
    {
        return $this->serveFile(
            $this->logFilePath(),
            $this->getLogFileSize(formatted: false),
            $this->generateLogFileName()
        );
    }

    protected function serveZippedLogFile()
    {
        return $this->serveFile(
            path: $this->zippedFilePath(),
            size: $this->getZippedFileSize(),
            name: $this->generateLogFileName(extension: 'zip')
        );
    }


    protected function serveFile($path, $size, $name)
    {
        $stream = fopen(
            $path,
            'r'
        );

        return response()->stream(
            function () use ($stream) {
                while (ob_get_level() > 0) ob_end_flush();

                fpassthru($stream);

                fclose($stream);
            },
            200,
            [
                'Content-Type' => 'application/oct-stream',

                'Content-disposition' => sprintf('inline; filename="%s"', $name),

                'Content-Length' => $size
            ]
        );
    }


    public function serve()
    {
        try {
            if ($this->canZipFile()) {

                $this->zipLogFile();

                return $this->serveZippedLogFile();
            }
        } catch (Throwable $th) {
            //
        }

        return $this->serveTextLogFile();
    }

    public function clear()
    {
        file_put_contents($this->logFilePath(), '');

        $this->clearOldZippedFile();
    }

    private function maxFileSize()
    {
        $value = config('log.max_file_size');

        if (empty($value)) {
            return 100;
        }

        return intval($value);
    }

    public function clearIfExceededMaxSize()
    {
        $bytes = filesize($this->logFilePath());

        $mb = $bytes / (1024 * 1024);

        if ($mb > $this->maxFileSize()) {
            $this->clear();
        }
    }

    protected function generateLogFileName($extension = 'log')
    {
        return sprintf(
            'qrcode-generator-log-%s.%s',
            now()->format('Y-m-d'),
            $extension
        );
    }

    public function getLogFileSize($formatted = true)
    {
        return $this->getFileSize($this->logFilePath(), $formatted);
    }

    public function getZippedFileSize()
    {
        return $this->getFileSize($this->zippedFilePath(), false);
    }

    protected function getFileSize($path, $formatted)
    {
        $size = filesize($path);

        if ($formatted)
            return $this->formatFileSize($size);

        return $size;
    }

    protected function canZipFile()
    {
        return class_exists(ZipArchive::class);
    }

    protected function zipLogFile()
    {
        $this->clearOldZippedFile();

        $zip = new ZipArchive;

        $zip->open($this->zippedFilePath(), ZipArchive::OVERWRITE | ZipArchive::CREATE);

        $zip->addFile($this->logFilePath(), basename($this->logFilePath()));

        $zip->close();
    }

    protected function clearOldZippedFile()
    {
        if (file_exists($this->zippedFilePath())) {
            try {
                unlink($this->zippedFilePath());
            } catch (Throwable $th) {
                //
            }
        }
    }

    protected function zippedFilePath()
    {
        return storage_path('logs/compressed.zip');
    }

    protected function formatFileSize($size, $accuracy = 2)
    {
        if (!$size) {
            return '0 B';
        }

        $units = array('B', 'KB', 'MB', 'GB');

        foreach ($units as $n => $u) {
            $div = pow(1024, $n);

            if ($size > $div) $output = number_format($size / $div, $accuracy) . ' ' . $u;
        }

        return $output;
    }

    protected function logFilePath()
    {
        return storage_path('logs/qrcode-generator.log');
    }

    /**
     * Get last lines of a file
     */
    function tail($filename, $n)
    {
        $buffer_size = 1024;

        $fp = fopen($filename, 'r');
        if (!$fp) return array();

        fseek($fp, 0, SEEK_END);
        $pos = ftell($fp);

        $input = '';
        $line_count = 0;

        while ($line_count < $n + 1) {
            // read the previous block of input
            $read_size = $pos >= $buffer_size ? $buffer_size : $pos;
            fseek($fp, $pos - $read_size, SEEK_SET);

            // prepend the current block, and count the new lines
            $input = fread($fp, $read_size) . $input;
            $line_count = substr_count(ltrim($input), "\n");

            // if $pos is == 0 we are at start of file
            $pos -= $read_size;
            if (!$pos) break;
        }

        fclose($fp);

        // return the last 50 lines found  

        return array_slice(explode("\n", rtrim($input)), -$n);
    }
}
