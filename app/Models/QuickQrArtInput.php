<?php

namespace App\Models;

use ReflectionClass;

class QuickQrArtInput
{
    const WORKFLOW_V2 = 'generate_qr_art_v2';

    const WORKFLOW_V1 = 'generate_qr_art_v1.1';

    const WORKFLOW_V3A = 'generate_qr_art_v3a';

    const WORKFLOW_V4 = 'generate_qr_art_v4';

    const WORKFLOW_V4S = 'generate_qr_art_v4s';

    const WORKFLOW_V4REAL = 'generate_qr_art_v4real';

    const WORKFLOW_V4NIJI = 'generate_qr_art_v4niji';

    const WORKFLOW_V4DREAM = 'generate_qr_art_v4dream';

    const WORKFLOW_V5 = 'generate_qr_art_v5';

    const WORKFLOW_V5S = 'generate_qr_art_v5s';



    public string $workflow;
    public float $qrStrength;
    public int $qrSteps;
    public string $qrImage;
    public string $qrText;
    public string $negativePrompt;
    public int $seed;
    public string $webhook;
    public string $qrContent;

    public function __construct()
    {
    }

    public static function init(
        $workflow,
        $qrStrength,
        $qrImage,
        $qrText,
        $negativePrompt,
        $qrSteps,
        $webhook,
        $qrContent
    ) {
        $instance = new static;

        $instance->workflow = $workflow;
        $instance->qrStrength = $qrStrength;
        $instance->qrImage = $qrImage;
        $instance->qrText = $qrText;
        $instance->negativePrompt = $negativePrompt;
        $instance->qrSteps = $qrSteps;
        $instance->webhook = $webhook;
        $instance->qrContent = $qrContent;

        return $instance;
    }

    public static function fromArray($array)
    {
        $instance = new static;

        foreach ($array as $key => $value) {
            $instance->{$key} = $value;
        }

        return $instance;
    }

    public static function resolveShortVersion($shortVersion)
    {
        $fullVersion = sprintf('generate_qr_art_v%s', $shortVersion);

        $found = static::getWorkflows()->first(function ($workflow) use ($fullVersion) {
            return $workflow === $fullVersion;
        });

        if (!$found) {
            return static::WORKFLOW_V1;
        }

        return $found;
    }

    private static function getWorkflows()
    {
        $class = new ReflectionClass(static::class);

        $constants = $class->getConstants();

        $workflows = collect($constants)->filter(function ($value, $key) {
            return preg_match('/workflow/i', $key);
        });

        return $workflows;
    }

    public function toArray()
    {
        return (array)$this;
    }
}
