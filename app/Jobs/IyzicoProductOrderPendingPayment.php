<?php

namespace App\Jobs;

use Config\Iyzipay;
use App\Models\Earning;
use App\Models\ProductOrder;
use App\Models\Shop\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Session;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\Payment\product\PaymentController;
use App\Http\Controllers\FrontEnd\Shop\PurchaseProcessController;

class IyzicoProductOrderPendingPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $order_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id)
    {
        $this->order_id = $order_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $productOrder = ProductOrder::where('id', $this->order_id)->first();
        $conversion_id = $productOrder->conversation_id;

        $options = Iyzipay::options();

        $request = new \Iyzipay\Request\ReportingPaymentDetailRequest();
        $request->setPaymentConversationId($conversion_id);

        $paymentResponse = \Iyzipay\Model\ReportingPaymentDetail::create($request, $options);

        $result = (array) $paymentResponse;
        
        foreach ($result as $key => $data) {
            if (is_string($data)) {
                $data = json_decode($data, true);
                if (isset($data['status']) == 'success' && count($data['payments']) > 0) {
                    if (is_array($data['payments'])) {
                        if ($data['payments'][0]['paymentStatus'] == 1) {
                            $productOrder->update(['payment_status' => 'completed']);

                            $paymentProcess = new PaymentController();
                            $paymentProcess->sendNotifications($productOrder);

                            // send a mail to the customer with the invoice

                            // remove all session data
                            Session::forget('zipcode');
                            Session::forget('address');
                            Session::forget('city');
                            Session::forget('country');
                            Session::forget('identity_number');

                            \Artisan::call('queue:work --stop-when-empty');

                            // return redirect()->route('shop.purchase_product.complete');
                        }
                    }
                }
            }
        }
    }
}
