<?php

namespace App\Support\TextRenderer;

use App\Support\System\Traits\WriteLogs;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Imagick;
use Throwable;

class InkscapeTextRenderer extends BaseTextRenderer
{
    use WriteLogs;

    private $inkscapeVersion;

    public function isSupported()
    {
        if (app()->environment('local')) return false;

        return class_exists(Imagick::class) && function_exists('exec') && $this->inkscapeIsInstalled();
    }

    private function isDockerizedInkscape()
    {
        return config('app.inkscape.dockerized');
    }

    private function detectInkscapeVersion()
    {
        $version = shell_exec('inkscape --version');

        $version = explode(' ', $version)[1];

        return $version;
    }

    private function inkscapeIsInstalled()
    {
        $this->logDebug('Before exec(inkscape --version)');

        exec('inkscape --version', $output);

        $this->logDebug('After exec(inkscape --version) %s', $output);

        if (empty($output)) return false;

        preg_match('#inkscape#i', $output[0], $matches);

        return !empty($matches);
    }

    protected function write(): ?Imagick
    {
        $this->initInkscape();

        if ($this->isDockerizedInkscape()) {
            return $this->writeWithDockerizedInkscape();
        }

        $this->inkscapeVersion = $this->detectInkscapeVersion();

        preg_match('/^0\.9/', $this->inkscapeVersion, $inkscape09);

        if ($inkscape09) {
            return $this->writeWithInkscape09();
        }

        preg_match('/^1/', $this->inkscapeVersion, $inkscape1);

        if ($inkscape1) {
            return $this->writeWithInkscape1();
        }

        throw new Exception('Inkscape version is not supported, supported versions are: 0.9.x, 1.x');
    }

    private function writeWithInkscape09()
    {
        $svgFile = Storage::path(uniqid('font-svg-'));

        file_put_contents($svgFile, $this->renderSvg(escapeForInlineCommand: false));

        $pngFile = Storage::path(uniqid('font-png-'));

        $cmd = sprintf(
            'INKSCAPE_PROFILE_DIR=%s inkscape %s --export-png=%s',
            Storage::path('inkscape'),
            $svgFile,
            $pngFile
        );

        exec($cmd);

        $trimCommand = sprintf('convert %1$s -trim +repage %1$s', $pngFile);

        exec($trimCommand);

        $image = new Imagick($pngFile);

        unlink($pngFile);

        unlink($svgFile);

        return $image;
    }

    private function writeWithInkscape1()
    {
        $cmd = sprintf(
            'echo "%s" | INKSCAPE_PROFILE_DIR=%s inkscape --export-type=png --export-filename=- -p | convert - -trim +repage png:-',
            $this->renderSvg(),
            Storage::path('inkscape'),

        );

        $output = shell_exec($cmd);

        $image = new Imagick();

        $image->readImageBlob($output);

        return $image;
    }

    private function initDocker()
    {
        $dockerfile = '
FROM ubuntu

WORKDIR /app

RUN apt update && apt install inkscape imagemagick -y

ENTRYPOINT ["tail", "-f", "/dev/null"]
';

        $compose = '
services:
        inkscape:
                restart: unless-stopped
                volumes:
                        - .:/app
                build: .
                environment:
                        - INKSCAPE_PROFILE_DIR=/app/storage/app/inkscape
';

        if (!file_exists(base_path('Dockerfile'))) {
            file_put_contents(base_path('Dockerfile'), $dockerfile);
            Log::info('Dockerfile added to use portable inkscape');
        }

        if (!file_exists(base_path('compose.yml'))) {
            file_put_contents(base_path('compose.yml'), $compose);
            Log::info('compose.yml added to use portable inkscape');
            Log::info('Run docker compose build');
        }
    }

    /**
     * Run inkscape in Dockerized environment because it cannot be installed on centos system
     */
    private function writeWithDockerizedInkscape()
    {
        $this->initDocker();

        $svgFile = uniqid('font-svg-') . '.svg';

        file_put_contents(
            Storage::path($svgFile),
            $this->renderSvg(escapeForInlineCommand: false)
        );

        $pngFile = uniqid('font-png-') . '.png';

        $inkscapeCommand = 'docker compose exec inkscape dbus-run-session inkscape';

        $cmd = sprintf(
            'cd %s; %s %s --export-type=png --export-filename=%s',
            base_path(),
            $inkscapeCommand,
            "storage/app/$svgFile",
            "storage/app/$pngFile"
        );

        Log::debug($cmd);

        $output = shell_exec($cmd);

        Log::debug($output);

        $trimmedFile = str_replace('.png', '-trimmed.png', $pngFile);

        $trimCommand = sprintf(
            'convert %1$s -trim +repage %2$s',
            Storage::path($pngFile),
            Storage::path($trimmedFile)
        );

        Log::debug($trimCommand);

        exec($trimCommand);

        try {
            $image = new Imagick(Storage::path($trimmedFile));
        } catch (Throwable $th) {
            Log::error($th->getMessage());
            return null;
        }


        unlink(Storage::path($pngFile));

        unlink(Storage::path($svgFile));

        unlink(Storage::path($trimmedFile));

        return $image;
    }

    protected function renderSvg($escapeForInlineCommand = true)
    {
        $svg = file_get_contents(__DIR__ . '/inkscape-text-renderer/text.svg');

        $fontStyle = 'normal';

        if (preg_match('/italic/', $this->fontVariant)) {
            $fontStyle = 'italic';
        }

        $fontWeight = str_replace('regular', 'normal', $this->fontVariant);

        $fontWeight = str_replace('italic', '', $fontWeight);

        if (empty($fontWeight)) {
            $fontWeight = 'normal';
        }

        $replace = [
            'TEXT' => $this->text,

            'FONT_FAMILY' => $this->fontFamily,

            'COLOR' => $this->color,

            'FONT_STYLE' => $fontStyle,

            'FONT_WEIGHT' => $fontWeight
        ];

        if ($escapeForInlineCommand) {
            $replace = array_merge($replace, [
                '"' => '\"',

                "\n" => " ",
            ]);
        }

        foreach ($replace as $key => $value) {
            $svg = str_replace($key, $value, $svg);
        }

        // clean up multiple white spaces

        $svg = preg_replace('/\s+/', ' ', $svg);

        return $svg;
    }

    protected function initInkscape()
    {
        Storage::makeDirectory('inkscape');

        if (!file_exists(storage_path('inkscape/fonts'))) {

            $cmd = sprintf(
                'ln -s %s %s',
                Storage::path('google_fonts'),
                Storage::path('inkscape/fonts')
            );

            if ($this->isDockerizedInkscape()) {
                $cmd = 'docker compose exec inkscape ln -s /app/storage/app/google_fonts /app/storage/app/inkscape/fonts';
            }

            exec($cmd, $output);
        }
    }
}
