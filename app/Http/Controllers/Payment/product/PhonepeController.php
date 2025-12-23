<?php

namespace App\Http\Controllers\Payment\product;

use App\Models\Language;
use App\Models\PostalCode;
use App\Models\BasicSetting;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use App\Models\PaymentGateway;
use App\Models\ShippingCharge;
use Illuminate\Support\Facades\Session;

class PhonepeController extends PaymentController
{
    public function store(Request $request)
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $be = $currentLang->basic_extended;
        $bs = $currentLang->basic_setting;

        if ($be->base_currency_text != 'INR') {
            return redirect()->back()->with('warning', 'Invalid currency for PhonePe payment.');
        }

        // return $request;
        if ($this->orderValidation($request)) {
            return $this->orderValidation($request);
        }

        $bs = BasicSetting::select('postal_code')->firstOrFail();

        if ($request->serving_method == 'home_delivery') {
            if ($bs->postal_code == 0) {
                if ($request->has('shipping_charge')) {
                    $shipping = ShippingCharge::findOrFail($request->shipping_charge);
                    $shippig_charge = $shipping->charge;
                } else {
                    $shipping = null;
                    $shippig_charge = 0;
                }
            } else {
                $shipping = PostalCode::findOrFail($request->postal_code);
                $shippig_charge = $shipping->charge;
            }
            if (!empty($shipping) && !empty($shipping->free_delivery_amount) && cartTotal() >= $shipping->free_delivery_amount) {
                $shippig_charge = 0;
            } else {
                $shippig_charge = $shippig_charge;
            }
        } else {
            $shipping = null;
            $shippig_charge = 0;
        }
        $total = $this->orderTotal($shippig_charge);
        // save order
        $order = $this->saveOrder($request, $shipping, $total);
        $order_id = $order->id;
        // save ordered items
        $this->saveOrderItem($order_id);
        return $this->apiRequest($order_id);
    }

    // send API request & redirect
    public function apiRequest($orderId)
    {
        $order = ProductOrder::find($orderId);
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $be = $currentLang->basic_extended;
        $title = 'Product Checkout';

        // Payment start

        $notifyURL = route('product.phonepe.notify');

        $info = PaymentGateway::where('keyword', 'phonepe')->first();
        $information = json_decode($info->information, true);
        $randomNo = substr(uniqid(), 0, 3);
        $data = [
            // 'merchantId' => 'M22ZG63B00XON', // prod merchant id
            'merchantId' => $information['phonepe_merchant_id'], // sandbox merchant id
            'merchantTransactionId' => uniqid(),
            'merchantUserId' => 'MUID' . $randomNo, // it will be the ID of tenants / vendors from database
            'amount' => round($order->total, 2) * 100,
            'redirectUrl' => $notifyURL,
            'redirectMode' => 'POST',
            'callbackUrl' => $notifyURL,
            'mobileNumber' => $order->billing_number,
            'paymentInstrument' => [
                'type' => 'PAY_PAGE',
            ],
        ];

        $encode = base64_encode(json_encode($data));

        $saltKey = $information['salt_key']; // sandbox salt key
        $saltIndex = $information['salt_index'];

        $string = $encode . '/pg/v1/pay' . $saltKey;
        $sha256 = hash('sha256', $string);

        $finalXHeader = $sha256 . '###' . $saltIndex;
        

        if ($information['phonepe_sandbox_status'] == 1) {
            $url = 'https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay'; // sandbox payment URL
        } else {
            $url = 'https://api.phonepe.com/apis/hermes/pg/v1/pay'; // prod payment URL
        }
        $response = Curl::to($url)
        ->withHeader('Content-Type:application/json')
        ->withHeader('X-VERIFY:' . $finalXHeader)
        ->withData(json_encode(['request' => $encode]))
        ->post();
        $rData = json_decode($response);
        if ($rData->success == true) {
            if (!empty($rData->data->instrumentResponse->redirectInfo->url)) {
                Session::put('order_data', $order);
                return redirect()->to($rData->data->instrumentResponse->redirectInfo->url);
            } else {
                return redirect()->back()->with('error', 'Payment Canceled.');
            }
        } else {
            return redirect()->back()->with('error', 'Payment Canceled.');
        }
        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info End ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
        return redirect()->to($rData->data->instrumentResponse->redirectInfo->url);
    }

    public function notify(Request $request)
    {
        $input = $request->all();
        $ref = session()->get('toyyibpay_ref_id');
        $order_data = Session::get('order_data');
        $order = ProductOrder::find($order_data['id']);
        if ($order->type == 'website') {
            $cancel_url = action('Payment\product\PaymentController@paycancle');
        } elseif ($order->type == 'qr') {
            $cancel_url = action('Payment\product\PaymentController@qrPayCancle');
        }
        if ($input['code'] == 'PAYMENT_SUCCESS') {
            try {
                if ($order) {
                    /** Get the payment ID before session clear **/
                    $order->payment_status = 'Completed';
                    $order->save();

                    $this->sendNotifications($order);
                    Session::forget('coupon');
                    Session::forget('cart');

                    Session::forget('order_data');
                    if ($order->type == 'website') {
                        $success_url = route('product.payment.return', $order->order_number);
                    } elseif ($order->type == 'qr') {
                        $success_url = route('qr.payment.return', $order->order_number);
                    }
                    return redirect($success_url);
                }
            } catch (\Throwable $th) {
                return redirect($cancel_url);
            }
        } else {
            return redirect($cancel_url);
        }
    }
}
