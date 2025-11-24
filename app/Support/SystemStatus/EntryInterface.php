<?php

namespace App\Support\SystemStatus;

interface EntryInterface
{
    const TYPE_SUCCESS = 'success';

    const TYPE_FAIL = 'fail';
    /**
     * Entry title
     */
    public function title();

    /**
     * Entry main value text
     */
    public function text();

    /**
     * Either success or danger
     */
    public function type();

    /**
     * Informative text
     */
    public function information();

    /**
     * Instructions to resolve any issue.
     * 
     * @type string html
     */
    public function instructions();
}
