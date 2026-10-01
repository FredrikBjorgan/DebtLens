export const exampleCode = `<?php

class OrderService
{
    public function processOrder(
        $user, $order, $payment,
        $shipping, $discount, $logger
    ) {
        if (!$user) { return false; }
        if (!$order) { return false; }
        if (!$payment) { return false; }
        if (!$shipping) { return false; }
        if ($discount) { $logger->info('Discount applied'); }

        foreach ($order->items as $item) {
            if ($item->stock <= 0) { continue; }
            if ($item->price > 1000) { $logger->info('High value item'); }
        }

        while ($shipping->isPending()) {
            if ($shipping->hasError()) { break; }
        }

        return true;
    }
}
`;
