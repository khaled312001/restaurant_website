<?php

namespace App\Http\Controllers;

use App\Models\ProductOrder;
use App\Http\Controllers\Controller;
use App\Jobs\IyzicoProductOrderPendingPayment;

class CronJobController extends Controller
{
    public function checkIyzicoPendingPayment()
    {
        try {
            /*```````````````````````````````````````````````````````
            ```````````Check Iyzico product purchase pending bookings``````````
            -------------------------------------------------------*/
            $productOrders = ProductOrder::where([['payment_status', 'pending'], ['method', 'iyzico']])->get();
            if (count($productOrders) > 0) {
                foreach ($productOrders as $key => $productOrder) {
                    if (!is_null($productOrder->conversation_id)) {
                        IyzicoProductOrderPendingPayment::dispatch($productOrder->id);
                    }
                }
            }
        } catch (\Throwable $th) {
            dd($th);
        }
    }
}
