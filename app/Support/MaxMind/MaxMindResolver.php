<?php

namespace App\Support\MaxMind;

use Throwable;
use App\Models\Config;
use GeoIp2\Database\Reader;
use GeoIp2\WebService\Client;
use App\Support\System\Traits\WriteLogs;

class MaxMindResolver
{
    use WriteLogs;

    private static $reader = null;

    private $apiKey, $accountId;

    public function __construct()
    {
        try {
            $this->apiKey = Config::get('maxmind.api_key');
            $this->accountId = Config::get('maxmind.account_id');
        } catch (Throwable $th) {
            //
        }
    }

    public function getReader()
    {
        if ($this::$reader) {
            return $this::$reader;
        }

        $updater = new MaxMindUpdater;

        // This reader object should be reused across lookups as creation of it is
        // expensive.
        $this::$reader = new Reader($updater->databaseFileName(absolute: true));

        return $this::$reader;
    }

    /**
     * If web service credentials available will try first 
     * with the web api. If it fails, it falls back to 
     * the free database based.
     */
    public function resolve($ip): ?Location
    {
        if ($this->shouldUseWebService()) {
            $location = $this->webService($ip);

            if ($location) {
                return $location;
            }
        }

        return $this->database($ip);
    }

    private function database($ip)
    {
        $reader = $this->getReader();

        try {
            $record = $reader->city($ip);

            if (!$record) {
                return null;
            }

            return new Location($record);
        } catch (Throwable $error) {

            $this->logDebug(
                'Unable to lookup from the free database IP address: ' . $ip
            );

            $this->logDebug(
                $error->getMessage()
            );

            return null;
        }
    }

    private function webService($ip)
    {
        try {
            $client = new Client($this->accountId, $this->apiKey);

            $location = new Location(
                $client->city($ip)
            );

            return $location;
        } catch (Throwable $th) {
            // 
            $this->logWarning(
                'Cannot query MaxMind web services ' . $ip
            );

            $this->logWarning(
                $th->getMessage()
            );
            // 
            return null;
        }
    }

    private function shouldUseWebService()
    {
        return !empty($this->apiKey) && !empty($this->accountId);
    }
}
