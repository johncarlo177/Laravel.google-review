<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Pluralizer;

use Illuminate\Filesystem\Filesystem;


class MakeRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a repository and interface';

    /**
     * Filesystem instance
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->generateSource('interface');
        $this->generateSource('repository');
    }


    private function generateSource($type)
    {
        $path = $this->getSourceFilePath($type);

        $this->makeDirectory(dirname($path));

        $contents = $this->getSourceFile($type);

        $typeName = $this->getSingularClassName($type);

        if (!$this->files->exists($path)) {
            $this->files->put($path, $contents);
            $this->info("$typeName generated successfully");
        } else {
            $this->error("$typeName already exists");
        }
    }

    /**
     * Return the Singular Capitalize Name
     * @param $name
     * @return string
     */
    public function getSingularClassName($name)
    {
        return ucwords(Pluralizer::singular($name));
    }

    private function getPluralizedClassName($name)
    {
        return ucwords(Pluralizer::plural($name, 3));
    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getStubPath(string $type)
    {
        return __DIR__ . "/../../../stubs/$type.stub";
    }

    /**
     **
     * Map the stub variables present in stub to its value
     *
     * @return array
     *
     */
    public function getStubVariables()
    {
        return [
            'CLASS_NAME'        => $this->getSingularClassName($this->argument('name')),
            'INTERFACE_NAME'    => $this->getSingularClassName($this->argument('name')) . 'Interface',
        ];
    }

    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public function getSourceFile($type)
    {
        return $this->getStubContents($this->getStubPath($type), $this->getStubVariables());
    }


    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return bool|mixed|string
     */
    public function getStubContents($stub, $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$' . $search . '$', $replace, $contents);
        }

        return $contents;
    }

    /**
     * Get the full path of generate class
     *
     * @return string
     */
    public function getSourceFilePath($type)
    {
        $folderName = $this->getPluralizedClassName($type);

        return base_path('app/' . $folderName) . '/' . $this->getSingularClassName($this->argument('name')) . '.php';
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }
}
