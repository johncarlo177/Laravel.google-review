<?php

namespace App\Support\SystemStatus;

use App\Support\SoftwareUpdate\AutoUpdate\SoftwareVersion;

class SystemStatus
{
    public function get()
    {
        return array_map(
            [$this, 'response'],
            $this->sortEntries($this->getEntries())
        );
    }

    public function ok()
    {
        $version = new SoftwareVersion;

        if ($version->hasUpdate()) {
            return false;
        }

        return array_reduce(
            $this->getEntries(),
            function ($carry, $entry) {
                return $carry && $entry->type() === EntryInterface::TYPE_SUCCESS;
            },
            true
        );
    }

    private function response($entry)
    {
        return [
            'title' => $entry->title(),
            'text' => $entry->text(),
            'information' => $entry->information(),
            'type' => $entry->type(),
            'instructions' => $entry->instructions()
        ];
    }

    private function sortEntries($entries)
    {
        usort($entries, fn($a, $b) => $a->sortOrder() - $b->sortOrder());

        return array_values(
            $entries
        );
    }

    private function getEntries()
    {
        return array_map(
            function ($class) {
                $fullClassName = __NAMESPACE__ . '\\' . $class;

                return new $fullClassName;
            },
            $this->makeClassList()
        );
    }

    private function makeClassList()
    {
        $files = array_map(
            function ($file) {
                $file = str_replace(__DIR__ . '/', '', $file);

                $file = str_replace('.php', '', $file);

                return $file;
            },
            glob(__DIR__ . '/*.php')
        );

        return collect($files)->filter(
            function ($file) {
                return !preg_match('/SystemStatus|BaseEntry|EntryInterface|StorageEntry|PHPFunctions/', $file);
            }
        )->filter(
            function ($file) {
                return !preg_match('/^VersionEntry/', $file);
            }
        )
            ->values()
            ->all();
    }
}
