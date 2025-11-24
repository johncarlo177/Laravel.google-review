<?php

namespace App\Support\SystemStatus;

use App\Support\SoftwareUpdate\DatabaseUpdateManager;

class DatabaseStatusEntry extends BaseEntry
{
    public function sortOrder()
    {
        return 2;
    }

    public function title()
    {
        return 'Database Schema';
    }

    public function text()
    {
        return $this->isSuccess() ? 'Up to date' : 'Update failed';
    }

    protected function isSuccess()
    {
        $manager = new DatabaseUpdateManager;

        return !$manager->hasDatabaseUpdate();
    }

    protected function instructionsText()
    {
        return 'Check error logs for more details.';
    }

    protected function informationText()
    {
        return 'Table structure and default records';
    }
}
