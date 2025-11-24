<?php

namespace App\Support\SoftwareUpdate;

use App\Models\Config;
use App\Support\SoftwareUpdate\MandatorySeeders\Seeder;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Throwable;

class DatabaseUpdateManager
{
    use WriteLogs;

    private Migrator $migrator;

    protected static ?Collection $db_migrations = null;

    public function __construct()
    {
        $this->migrator = app('migrator');
    }

    protected function canMigrate()
    {
        $migrationsFound = $this->getCountOfMigrationsWhichDidnotRun() > 0;

        if ($migrationsFound) {
            $this->logInfo(
                'Migrations to be run %s, all migrations = %s',
                $this->getPendingMigrations()->values()->all(),
                $this->getRanMigrations()->values()->all()
            );
        }

        return $migrationsFound;
    }

    public function hasDatabaseUpdate()
    {
        $canMigrate = $this->canMigrate();

        $upgradeRequired = $this->seederVersionUpgradeRequired();

        if ($canMigrate) {
            $this->logDebug('Database migration pending.');
        }

        return $canMigrate || $upgradeRequired;
    }

    public function updateDatabaseIfNeeded()
    {
        $this->logDebug('Disabling cache');

        Config::disableCache();

        if ($this->hasDatabaseUpdate()) {
            $this->updateDatabase();
        }

        Config::enableCache();

        Config::rebuildCache();
    }

    public function getPendingMigrationsFullPath()
    {
        return $this->getMigrationFiles()->filter(
            function ($path) {
                return $this->getPendingMigrations()
                    ->filter(function ($name) use ($path) {
                        return strpos($path, $name) !== false;
                    })->isNotEmpty();
            }
        )->values();
    }

    protected function updateDatabase()
    {
        $this->getPendingMigrationsFullPath()
            ->each(function ($path) {
                try {
                    Artisan::call(
                        sprintf(
                            'migrate --force --realpath --path "%s"',
                            $path
                        )
                    );
                } catch (Throwable $th) {
                    $this->logWarningf(
                        'Migration running failed: %s',
                        $th->getMessage()
                    );
                }
            });



        $this->runSeeders();
    }

    private function seederVersionUpgradeRequired()
    {
        $seeders = $this->makeMandatorySeedersClassList();

        return collect($seeders)
            ->map(fn($class) => app($class))
            ->reduce(function ($result, Seeder $seeder) {
                //
                $upgradeRequired = $seeder->versionUpgradeRequired();

                if ($upgradeRequired) {
                    $seeder->debugVersion();
                    $this->logDebug('Upgrade required for %s', $seeder::class);
                }

                return $result || $upgradeRequired;
                //
            }, false);
    }

    private function runSeeders()
    {
        $seeders = $this->makeMandatorySeedersClassList();

        foreach ($seeders as $class) {

            $className = class_basename($class);

            try {
                Log::debug('Running seeder ' . $className);

                $seeder = app($class);

                $seeder->seed();

                Log::debug('Seeder completed successfully ' . $className);
            } catch (Throwable $th) {

                Log::error('Failed running seeder ' . $className . ' ' . $th->getMessage());

                Log::debug($th->getTraceAsString());
            }
        }
    }

    private function makeMandatorySeedersClassList()
    {
        $files = array_map(
            function ($file) {
                $file = basename($file, '.php');

                return $file;
            },
            glob(__DIR__ . '/MandatorySeeders/*.php')
        );

        $files = array_filter(
            $files,
            function ($file) {
                return !preg_match('/^Seeder$/', $file);
            }
        );

        return array_map(fn($f) =>  __NAMESPACE__ . '\\MandatorySeeders\\' . $f, $files);
    }

    public function countMigrationFiles()
    {
        $paths = $this->migrator->paths();

        return collect($paths)
            ->reduce(function ($count, $path) {
                return $count + count(glob($path . '/*.php'));
            }, 0);
    }

    public function getPendingMigrations()
    {
        return $this->getMigrationFileNames()
            ->filter(function ($name) {
                return !$this->isMigrationFileAlreadyRan($name);
            });
    }

    public function getCountOfMigrationsWhichDidnotRun()
    {
        return $this->getPendingMigrations()->count();
    }

    public function isMigrationFileAlreadyRan($name)
    {
        return $this->getRanMigrations()
            ->filter(function ($m) use ($name) {
                return $m === $name;
            })->isNotEmpty();
    }

    public function getRanMigrations()
    {
        if (!$this::$db_migrations) {
            $this::$db_migrations = collect(
                DB::table('migrations')->pluck('migration')
            );
        }

        return $this::$db_migrations;
    }

    public function getMigrationFileNames()
    {
        return $this->getMigrationFiles()->map(function ($path) {
            $parts = explode('/', $path);

            $name = $parts[sizeof($parts) - 1];

            $name = preg_replace('/\..*/', '', $name);

            return $name;
        });
    }

    /**
     * @return Collection<string>
     */
    public function getMigrationFiles()
    {
        $paths = $this->migrator->paths();

        $path =  collect($paths)
            ->reduce(function ($result, $path) {
                return array_merge($result, glob($path . '/*.php'));
            }, []);

        return collect($path);
    }
}
