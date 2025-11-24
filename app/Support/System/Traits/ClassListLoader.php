<?php

namespace App\Support\System\Traits;


use ReflectionClass;

/**
 * @deprecated
 * @use \App\Support\System\ClassFinder
 */
trait ClassListLoader
{
    use WriteLogs;

    /**
     * @deprecated
     */
    private function getDir() {}

    /**
     * @deprecated
     */
    private function getNamespace() {}

    /**
     * @deprecated
     * @use makeInstances
     */
    private function makeInstancesOfInstantiableClassesInCurrentDirectory($excludePattern = null)
    {
        return $this->makeInstances($this->getDir(), $this->getNamespace(), $excludePattern);
    }

    private function makeInstances($directory, $namespace = null, $excludePattern = null)
    {
        $instances = $this->makeInstantiableListOfClasses($directory, $namespace, $excludePattern);

        return array_map(fn($class) => app($class), $instances);
    }

    private function guessNamespace($directory)
    {
        $namespace = str_replace(base_path('app'), '', $directory);

        $namespace = str_replace('/', '\\', $namespace);

        return 'App' . $namespace;
    }

    /**
     * Get an array of class strings that are not abstract and is found in the given
     * directoy.
     * 
     * @param string $directory directory to search for classes in
     * @param string? $namespace if not provided, it will be gussed based on the current directory
     * @param string $excludePattern regex pattern to exclude from the list
     * 
     * @return ClassString[] array of classes
     */
    private function makeInstantiableListOfClasses(
        $directory,
        $namespace = null,
        $excludePattern = null
    ) {
        if (!$namespace) {
            $namespace = $this->guessNamespace($directory);
        }

        $files = array_map(
            function ($file) {
                $file = basename($file, '.php');

                return $file;
            },
            glob($directory . '/*.php')
        );

        if (!empty($excludePattern)) {
            $files = array_filter(
                $files,
                function ($file) use ($excludePattern) {
                    return !preg_match("/$excludePattern/", $file);
                }
            );
        }

        $classes = array_map(fn($f) =>  $namespace . '\\' . $f, $files);

        // Ignore caller class from the list.
        $classes = array_filter($classes, fn($cl) => $cl != static::class);

        // Ignore all abstract classes.
        $classes = array_filter($classes, function ($class) {
            $class = new ReflectionClass($class);

            return !$class->isAbstract();
        });

        return array_values($classes);
    }

    /** 
     * @deprecated  
     * @use makeInstantiableListOfClasses
     * */
    private function makeInstantiableListsOfClassesInCurrentDirectory(
        $excludePattern = null
    ) {
        return $this->makeInstantiableListOfClasses(
            $this->getDir(),
            $this->getNamespace(),
            $excludePattern
        );
    }
}
