<?php

namespace App\Listeners;

use App\Events\ProductQuantityUpdate;

class UpdateQuantity
{
    /**
     * @param ProductQuantityUpdate $event
     * @return void
     */
    public function handle(ProductQuantityUpdate $event)
    {
        $product = $event->product->refresh();
        $quantity = $event->quantity;

        if ($product->status == 'rejected' || $product->status == 'auto_rejected') {
            $product->quantity = $product->quantity + $quantity;
        } else {
            $product->quantity = $product->quantity - $quantity;
        }

        $product->save();
    }
}
