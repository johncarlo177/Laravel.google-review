<?php

namespace App\Support\AI\OpenAi\Models;

use App\Support\System\Traits\WriteLogs;

class Output
{
    use WriteLogs;

    public $id;
    public $type;
    public $status;
    /**
     * @var Content[]
     */
    public $content;
    public $role;

    public static function make($outputs)
    {
        $array = [];

        foreach ($outputs as $data) {
            $instance = new static;

            $keys = array_keys(get_object_vars($instance));

            foreach ($keys as $key) {
                if ($key === 'content') {
                    $instance->content = Content::parse($data['content']);
                } else {
                    $instance->{$key} = $data[$key];
                }
            }

            $array[] = $instance;
        }

        return $array;
    }
}
