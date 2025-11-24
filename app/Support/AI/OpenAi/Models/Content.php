<?php

namespace App\Support\AI\OpenAi\Models;

class Content
{
    public const TYPE_OUTPUT_TEXT = 'output_text';
    public const TYPE_INPUT_TEXT = 'input_text';

    public $type;
    public $text;

    public static function make($text)
    {
        $instance = new static;

        $instance->type = 'input_text';

        $instance->text = $text;

        return $instance;
    }

    public static function parse($array)
    {
        $result = [];

        foreach ($array as $data) {
            $instance = new static;

            $keys = array_keys(get_object_vars($instance));

            foreach ($keys as $key) {
                $instance->{$key} = $data[$key];
            }

            $result[] = $instance;
        }

        return $result;
    }
}
