<?php
namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessSubscriptions extends Command
{
    protected $signature = 'afren:process-subscriptions';
    protected $description = 'Issue renewal invoices, mark past-due/expired and end cancelled subscriptions';
    public function handle(SubscriptionService $s): int
    {
        $this->table(['invoiced', 'past_due', 'expired', 'cancelled'], [array_values($s->process())]);
        return self::SUCCESS;
    }
}
