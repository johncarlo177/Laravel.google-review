<?php

namespace App\Support\AI\OpenAi\Models;

use App\Support\System\Traits\WriteLogs;

class Response
{
    use WriteLogs;

    public $id;
    public $object;
    public $created_at;
    public $status;
    public $error;
    public $incomplete_details;
    public $instructions;
    public $max_output_tokens;
    public $model;

    /**
     * @var Output[]
     */
    public $output;

    public $parallel_tool_calls;
    public $previous_response_id;
    public $reasoning;
    public $service_tier;
    public $store;
    public $temperature;
    public $text;
    public $tool_choice;
    public $tools;
    public $top_p;
    public $truncation;
    /**
     * @var Usage[]
     */
    public $usage;
    public $user;
    public $metadata;

    public static function make($raw)
    {
        if (!isset($raw['id'])) {
            static::logWarning(
                'Expected open AI response with ID key, but got instead: %s',
                $raw
            );
            return new static;
        }

        $instance = new static;

        $keys = array_keys(get_object_vars($instance));

        foreach ($keys as $key) {
            switch ($key) {
                case 'output':
                    $instance->output = Output::make($raw['output']);
                    break;

                case 'usage':
                    break;

                default:
                    $instance->{$key} = $raw[$key];
                    break;
            }
        }

        return $instance;
    }
}
