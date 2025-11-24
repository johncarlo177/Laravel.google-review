<?php

namespace App\Support;

use App\Support\System\Traits\WriteLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

class DatabaseHelper
{
    use WriteLogs;

    private static $shouldLog = false;
    private static $listenerBound = false;
    public static $queryCount = -1;

    public static function getTableColumns(string $table)
    {
        return DB::getSchemaBuilder()->getColumnListing($table);
    }

    public static function hasColumn($table, $column)
    {
        return collect(
            static::getTableColumns($table)
        )->filter(function ($c) use ($column) {
            return $c === $column;
        })->isNotEmpty();
    }

    public static function forceFillModel(Model $model, $attributes)
    {
        $columns = collect(static::getTableColumns($model->getTable()));

        $has = fn($column) => $columns->filter(fn($c) => $c === $column)->isNotEmpty();

        $attributes = collect($attributes)->reduce(
            function ($result, $value, $key) use ($model, $has) {
                if ($has($key)) {
                    $result[$key] = $value;
                }

                return $result;
            },
            []
        );

        $model->forceFill($attributes);
    }

    public static function startLog()
    {
        static::$shouldLog = true;

        static::bindListenerIfNeeded();
    }

    public static function stopLog()
    {
        static::$shouldLog = false;
    }

    public static function bindListenerIfNeeded()
    {
        if (static::$listenerBound) return;

        DB::listen(function (QueryExecuted $query) {

            if (static::$queryCount === -1) {
                static::$queryCount = 0;
            }

            static::$queryCount++;

            if (!static::$shouldLog) {
                return;
            }

            static::logDebug('%s milliseconds %s %s', $query->time, $query->sql, json_encode($query->bindings, JSON_PRETTY_PRINT));
        });

        static::$listenerBound = true;
    }
}
