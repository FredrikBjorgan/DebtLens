<?php

class OrderService
{
    public function processOrder(
        $user,
        $order,
        $payment,
        $shipping,
        $discount,
        $logger,
        $notification
    ) {
        if (!$user) {
            return false;
        }

        if (!$order) {
            return false;
        }

        if ($payment) {
            if ($payment->isValid()) {
                if ($discount) {
                    // apply discount
                }
            }
        }

        foreach ($order->items as $item) {
            if ($item->stock <= 0) {
                continue;
            }

            if ($item->price > 1000) {
                // expensive item
            }
        }

        while ($shipping->isPending()) {
            if ($shipping->hasError()) {
                break;
            }
        }

        return true;
    }
}