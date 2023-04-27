<?php

namespace App\Jobs;

use App\Models\PreOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Twilio\Rest\Client;

class TwilioSendSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $preOrder;

    public function __construct(PreOrder $preOrder)
    {
        $this->preOrder = $preOrder;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (config('services.twilio.sid') == null || config('services.twilio.token') == null || config('services.twilio.from') == null) {
            return;
        }

        $client = new Client(config('services.twilio.sid'), config('services.twilio.token'));

        $userName = $this->preOrder->first_name . ' ' . $this->preOrder->last_name;

        // Use the Client to make requests to the Twilio REST API
        $client->messages->create(
        // The number you'd like to send the message to
            $this->preOrder->phone,
            [
                // A Twilio phone number you purchased at https://console.twilio.com
                'from' => config('services.twilio.from'),
                // The body of the text message you'd like to send
                'body' => "Hello $userName, your pre-order has been received."
            ]
        );
    }
}
