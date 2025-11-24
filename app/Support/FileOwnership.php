<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

class FileOwnership
{
    public static function getFileOwner($path)
    {
        $command = "ls -al $path | grep \\.$";

        $result = '';

        try {
            exec($command, $result);
            $result = preg_match('/total/i', $result[0]) ? $result[1] : $result[0];

            // Example result: 'drwxr-xr-x    5 nobody   nobody        4096 May 31 08:16 .';

            $result = array_values(array_filter(explode(' ', $result)));

            $user = $result[2];

            $group = $result[3];

            return compact('user', 'group');
        } catch (\Throwable $th) {
        }
    }

    public static function setFolderOwnership($path, $user, $group)
    {
        try {
            $command = "chown -R $user:$group $path";
            exec($command);
        } catch (\Throwable $th) {
        }
    }

    public static function setStorageOwnership()
    {
        try {
            $result = static::getFileOwner(base_path('storage'));

            static::setFolderOwnership(base_path('storage'), $result['user'], $result['group']);
        } catch (Throwable $th) {
        }
    }
}
