<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use App\Models\Domain;
use App\Repositories\UserManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DomainSeeder extends Seeder
{
    use WriteLogs;

    protected $version = '1.18/3';

    protected function run()
    {
        $this->recreateDomainsTable();

        $this->seedDefaultDomain();
    }

    protected function recreateDomainsTable()
    {
        if (app()->environment('local')) return;

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Artisan::call('migrate:refresh', ['--path' => 'database/migrations/2022_11_14_034932_create_domains_table.php', '--force' => true]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    protected function seedDefaultDomain()
    {
        $parsed = parse_url(url('/'));

        $host = $parsed['host'];

        $protocol = $parsed['scheme'];

        $domain = new Domain();

        $domain->host = $host;

        if (@$parsed['port']) {
            $domain->host .= ':' . $parsed['port'];
        }

        $domain->protocol = $protocol;

        $domain->readonly = true;

        $domain->is_default = true;

        $userManager = new UserManager();

        $domain->user_id = $userManager->getSuperAdmins()[0]->id;

        $domain->availability = Domain::AVAILABILITY_PUBLIC;

        $domain->status = Domain::STATUS_PUBLISHED;

        $domain->save();

        return $domain;
    }
}
