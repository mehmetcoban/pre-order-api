<?php

namespace App\Observers;

use App\Jobs\TwilioSendSms;
use App\Models\PreOrder;

class PreOrderObserver
{
    /**
     * @param PreOrder $preOrder
     * @return void
     */
    public function updating(PreOrder $preOrder)
    {
        if ($preOrder->status == 'approved') {
            TwilioSendSms::dispatchSync($preOrder);
        }
    }
}
