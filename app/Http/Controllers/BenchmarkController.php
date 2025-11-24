<?php

namespace App\Http\Controllers;

use App\Support\DatabaseHelper;
use Illuminate\Support\Facades\DB;

class BenchmarkController
{
    private static function getTime()
    {
        return (microtime(true) - LARAVEL_START) . ' seconds.';
    }

    public function showTime()
    {
        return $this->getTime();
    }

    public static function runQuery()
    {
        DatabaseHelper::startLog();

        $start = microtime(true);

        DB::statement("select table_name as `name`, (data_length + index_length) as `size`, table_comment as `comment`, engine as `engine`, table_collation as `collation` from information_schema.tables where table_schema = 'quickcode' and table_type in ('BASE TABLE', 'SYSTEM VERSIONED') order by table_name;");

        $end = microtime(true);

        return ($end - $start) . ' seconds';
    }
}
