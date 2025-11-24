<?php

namespace App\Console\Commands;

use App\Repositories\StripePaymentGateway;
use Illuminate\Console\Command;

class ClearWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhooks:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears Stripe webhooks';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        (new StripePaymentGateway)->clearWebhooks();

        $this->info('Stripe webhooks cleared!');

        return 0;
    }
}
