<?php

namespace App\Http\Controllers\Payment\product;

use App\Models\Language;
use App\Models\PostalCode;
use App\Models\BasicSetting;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Models\ShippingCharge;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class PerfectMoneyController extends PaymentController
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

        if ($be->base_currency_text != 'USD') {
            return redirect()->back()->with('error', 'Invalid currency for Perfect Money.');
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
        $bs = $currentLang->basic_setting;

        $title = 'Product Checkout';
        $notify_url = route('product.perfect_money.notify');
        $cancel_url = route('product.perfect_money.cancel');

        // Payment start
        $gatewayData = PaymentGateway::where('keyword', 'perfect_money')->first();
        $paydata = json_decode($gatewayData->information, true);

        $notifyURL = route('product.midtrans.notify');
        if ($order->type == 'website') {
            $cancelUrl = action('Payment\product\PaymentController@paycancle');
        } elseif ($order->type == 'qr') {
            $cancelUrl = action('Payment\product\PaymentController@qrPayCancle');
        }

        $randomNo = substr(uniqid(), 0, 8);
        $perfect_money = $gatewayData;
        $val['PAYEE_ACCOUNT'] = $paydata['perfect_money_wallet_id'];
        $val['PAYEE_NAME'] = $title;
        $val['PAYMENT_ID'] = "$randomNo"; //random id
        $val['PAYMENT_AMOUNT'] = $order->total;
        $val['PAYMENT_UNITS'] = 'USD';

        $val['STATUS_URL'] = $notify_url;
        $val['PAYMENT_URL'] = $notify_url;
        $val['PAYMENT_URL_METHOD'] = 'GET';
        $val['NOPAYMENT_URL'] = $cancel_url;
        $val['NOPAYMENT_URL_METHOD'] = 'GET';
        $val['SUGGESTED_MEMO'] = "$order->billing_fname";
        $val['BAGGAGE_FIELDS'] = 'IDENT';

        $data['val'] = $val;
        $data['method'] = 'post';
        $data['url'] = 'https://perfectmoney.com/api/step1.asp';
        // put some data in session before redirect to gateway
        Session::put('order_data', $order);
        Session::put('payment_id', $randomNo);
        Session::forget('paymentFor');
        $website_title = $bs->website_title;
        return view('front.payment.perfect-money', compact('data', 'website_title'));
    }

    
    public function notify()
    {
        $order_data = Session::get('order_data');
        $order = ProductOrder::find($order_data['id']);
        if ($order->type == 'website') {
            $cancel_url = action('Payment\product\PaymentController@paycancle');
        } elseif ($order->type == 'qr') {
            $cancel_url = action('Payment\product\PaymentController@qrPayCancle');
        }
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
    }

    public function cancel()
    {
        return redirect()->route('front.index');
    }
}
