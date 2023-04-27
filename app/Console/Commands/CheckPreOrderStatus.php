<?php

namespace App\Console\Commands;

use App\Models\PreOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckPreOrderStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check-pre-order-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check pre-order status and send SMS if approved';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $oneDayAgo = Carbon::now()->subDay()->toDateTimeString();

        PreOrder::where('status', 'waiting')
            ->where('created_at', '<=', $oneDayAgo)
            ->update(['status' => 'auto_rejected']);

        $this->info('Pre-order status checked successfully!');
    }
}
