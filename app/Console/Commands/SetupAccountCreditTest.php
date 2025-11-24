<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Billing\AccountCreditBillingManager;
use Illuminate\Console\Command;

class SetupAccountCreditTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:account-credit-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up account credit test, only used during development and should never be run in production.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!app()->environment('local')) {
            $this->error('This command can run only on local environment.');
            return 0;
        }

        $balance = 0;

        $user = User::find(1);

        $billing = new AccountCreditBillingManager();

        $billing->forUser($user)->setAccountBalance($balance);

        $this->info(sprintf(
            'New balance is (%s)',
            $billing->getAccountBalance()
        ));

        return 0;
    }
}
