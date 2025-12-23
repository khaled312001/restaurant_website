<?php

namespace App\Http\Controllers\Payment\product;

use App\Models\Language;
use App\Models\PostalCode;
use App\Models\BasicSetting;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Models\ShippingCharge;
use Illuminate\Support\Facades\Session;

class ToyyibpayController extends PaymentController
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

        if ($be->base_currency_text != 'RM') {
            return redirect()->back()->with('warning', 'Invalid currency for ToyyibPay payment.');
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

        $gatewayData = PaymentGateway::where('keyword', 'toyyibpay')->first();
        $gatewayInfo = json_decode($gatewayData->information, true);

        $ref = uniqid();
        session()->put('toyyibpay_ref_id', $ref);

        $bill_description = 'Paying for ' . $title . ' via toyyibpay';
        $notifyURL = route('product.toyyibpay.notify');
        $some_data = [
            'userSecretKey' => $gatewayInfo['toyyibpay_secret_key'], //server key
            'categoryCode' => $gatewayInfo['category_code'], // category code
            'billName' => $title,
            'billDescription' => $bill_description,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $order->total * 100,
            'billReturnUrl' => $notifyURL,
            'billExternalReferenceNo' => $ref,
            'billTo' => $order->billing_fname,
            'billEmail' => $order->billing_email,
            'billPhone' => $order->billing_number,
        ];

        if ($gatewayInfo['toyyibpay_sandbox_status'] == 1) {
            $host = 'https://dev.toyyibpay.com/'; // for development environment
        } else {
            $host = 'https://toyyibpay.com/'; // for production environment
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, $host . 'index.php/api/createBill'); // sandbox will be dev.
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);

        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        $obj = json_decode($result, true);

        // put some data in session before redirect
        //  Session::forget('paymentFor');
        //  Session::put('arrData', $arrData);
        Session::put('order_data', $order);

        return redirect($host . $obj[0]['BillCode']);
    }

    public function notify(Request $request)
    {
        $ref = session()->get('toyyibpay_ref_id');
        $order_data = Session::get('order_data');
        $order = ProductOrder::find($order_data['id']);
        if ($order->type == 'website') {
            $cancel_url = action('Payment\product\PaymentController@paycancle');
        } elseif ($order->type == 'qr') {
            $cancel_url = action('Payment\product\PaymentController@qrPayCancle');
        }
        if ($request['status_id'] == 1 && $request['order_id'] == $ref) {
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
        } elseif ($request['status_id'] == 3 && $request['order_id'] == $ref) {
            return redirect($cancel_url);
        }
    }
}
