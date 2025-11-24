<?php

namespace App\Support\AI\OpenAi\Models;


class Input
{
    public const ROLE_USER = 'user';
    public const ROLE_SYSTEM = 'system';
    public const ROLE_ASSISTANT = 'assistant';

    public $id;

    public $role;
    /**
     * @var Content[]
     */
    public $content;

    protected static function make($role, $text)
    {
        $instance = new static;

        $instance->role = $role;

        $instance->content = [Content::make($text)];

        return $instance;
    }

    public static function system($text)
    {
        return static::make(static::ROLE_SYSTEM, $text);
    }

    public static function user($text)
    {
        return static::make(static::ROLE_USER, $text);
    }

    /**
     * @param string $id
     * @param Content[] $content
     */
    public static function assistant($id, $content)
    {
        $instance = new static;

        $instance->id = $id;

        $instance->content = $content;

        $instance->role = static::ROLE_ASSISTANT;

        return $instance;
    }
}
