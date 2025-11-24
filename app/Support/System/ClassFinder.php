<?php

namespace App\Support\System;

use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Collection;
use ReflectionClass;
use Throwable;

class ClassFinder
{
    use WriteLogs;

    private $dir = null;

    private $parentClass = null;

    private $namespace = null;

    public static function in($dir)
    {
        $instance = new static;

        $instance->dir = $dir;

        return $instance;
    }

    public function subClassesOf($class)
    {
        $this->parentClass = $class;

        return $this;
    }

    public function withNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    private function getFilesList()
    {
        $patterns = [$this->dir . '/*.php', $this->dir . '/**/*.php'];

        return collect($patterns)
            ->reduce(
                function ($result, $pattern) {
                    // 
                    $result = $result->concat(glob($pattern));

                    return $result;
                },
                collect()
            )->values();
    }

    /**
     * @return Collection
     */
    private function getFileNames()
    {
        $files = $this->getFilesList()->map(function ($path) {
            return basename($path, '.php');
        });

        return $files;
    }

    private function getClassList()
    {
        return $this->getFileNames()->map(
            function ($name) {
                return $this->getNamespace() . '\\' . $name;
            }
        );
    }

    private function getMatchedClassList()
    {
        return $this->getClassList()->filter(function ($class) {
            if (!$this->parentClass) return true;

            try {

                $classObject = new ReflectionClass($class);

                return $classObject->isSubclassOf($this->parentClass);
                // 
            } catch (Throwable $th) {
                $this->logWarning('Cannot create reflection class of %s [%s]', $class, $th->getMessage());
                return false;
            }
        })->values();
    }

    /**
     * @return Collection
     */
    public function find()
    {
        $list = $this->getMatchedClassList();

        $instances = $list->map(function ($class) {
            return app($class);
        })->values();

        return $instances;
    }

    private function getNamespace()
    {
        return $this->namespace ?? $this->guessNamespace();
    }

    private function guessNamespace()
    {
        $namespace = str_replace(base_path('app'), '', $this->dir);

        $namespace = str_replace('/', '\\', $namespace);

        return 'App' . $namespace;
    }
}
