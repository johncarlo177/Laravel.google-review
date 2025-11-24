<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use App\Exceptions\NotImplementedException;
use App\Models\Config;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Log;

abstract class Seeder
{
    use WriteLogs;

    protected $rawFile = '';

    protected $table = '';

    protected $runOnce = false;

    protected $didRun = false;

    /** @var string Seeder version, the seeder will not run in future, unless the version is changed in each Seeder class. */
    protected $version = '';

    protected function run()
    {
        foreach ($this->getRawArray() as $row) {
            if ($this->shouldInsertRow($row)) {

                $data = array_merge($row, [
                    'id' => null,
                ]);

                $model = $this->newModel($row);
                // In some hosting environments insertion is async
                // and the record would be inserted multiple times.
                usleep(300);

                $this->forceFill($model, $data, $row);

                $model->save();
            }
        }
    }

    protected function forceFill($model, array $data, array $row)
    {
        $model->forceFill($data);
    }

    protected function newModel($row)
    {
        throw new NotImplementedException();
    }

    protected function shouldInsertRow(array $row)
    {
        throw new NotImplementedException();
    }

    public function seed()
    {
        if (!$this->shouldRun()) {
            return;
        }

        $this->beforeRun();

        $this->run();

        $this->afterRun();

        $this->terminate();
    }

    protected function shouldRun()
    {
        if ($this->runOnce) {
            return empty(Config::get($this->lastRunConfigKey()));
        }

        if (!empty($this->version)) return $this->versionUpgradeRequired();

        return true;
    }

    public function versionUpgradeRequired()
    {
        if (!empty($this->version)) {
            return $this->getLastVersion() != $this->version;
        }

        return false;
    }

    public function debugVersion()
    {
        $this->logDebug(
            'Last version = %s, current version = %s, equals = %s',
            $this->getLastVersion(),
            $this->version,
            $this->version == $this->getLastVersion() ? 'true' : 'false'
        );
    }

    protected function getLastVersion()
    {
        $version = Config::get($this->versionConfigKey());

        return $version;
    }

    protected final function terminate()
    {
        Config::set($this->lastRunConfigKey(), now());

        Config::set($this->versionConfigKey(), $this->version);
    }

    protected function lastRunConfigKey()
    {
        return static::class . '::lastRun';
    }

    protected function versionConfigKey()
    {
        return static::class . '::version';
    }

    protected function beforeRun()
    {
    }

    protected function afterRun()
    {
    }

    protected function getRawArray()
    {
        $array = require(base_path('database/raw/' . $this->rawFile . '.php'));

        return $array;
    }

    protected function rawFile($name)
    {
        return file_get_contents(base_path('database/raw/' . $name));
    }
}
