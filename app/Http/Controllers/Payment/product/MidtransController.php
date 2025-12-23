<?php

namespace App\Http\Controllers\Payment\product;

use Midtrans\Snap;
use App\Models\Language;
use App\Models\PostalCode;
use App\Models\BasicSetting;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Models\ShippingCharge;
use Midtrans\Config as MidtransConfig;
use Illuminate\Support\Facades\Session;

class MidtransController extends PaymentController
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

        if ($be->base_currency_text != 'IDR') {
            return redirect()->back()->with('error', 'Invalid currency for Midtrans payment.');
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
        $gatewayData = PaymentGateway::where('keyword', 'midtrans')->first();
        $gatewayInfo = json_decode($gatewayData->information, true);

        // will come from database
        MidtransConfig::$serverKey = $gatewayInfo['midtrans_server_key'];
        MidtransConfig::$isProduction = $gatewayInfo['mindtrans_test_mode'] == 1 ? false : true;
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;
        $notifyURL = route('product.midtrans.notify');
        if ($order->type == 'website') {
            $cancelUrl = action('Payment\product\PaymentController@paycancle');
        } elseif ($order->type == 'qr') {
            $cancelUrl = action('Payment\product\PaymentController@qrPayCancle');
        }

        $params = [
            'transaction_details' => [
                'order_id' => uniqid(),
                'gross_amount' => $order->total * 1000, // will be multiplied by 1000
            ],

            'customer_details' => [
                'first_name' => $order->billing_fname,
                'email' => $order->billing_email,
                'phone' => $order->billing_number,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        if ($gatewayInfo['mindtrans_test_mode'] == 1) {
            $is_production = 0;
        } else {
            $is_production = 1;
        }

        Session::put('order_data', $order);

        return view('front.multipurpose.midtrans', compact('snapToken', 'is_production', 'notifyURL', 'cancelUrl'));
    }

    public function notify(Request $request)
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

    public function onlineBankNotify(Request $request)
    {
        if ($request->status_code == 200 && $request->order_id) {
            $order_data = Session::get('order_data');
            $order = ProductOrder::find($order_data['id']);
            // if ($order->type == 'website') {
            //     $cancel_url = action('Payment\product\PaymentController@paycancle');
            // } elseif ($order->type == 'qr') {
            //     $cancel_url = action('Payment\product\PaymentController@qrPayCancle');
            // }
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
        } else {
            Session::flash('error', 'Payment Canceled');
            // return redirect($cancel_url);
            return redirect()->route('midtrans.cancel');
        }
    }

    public function cancel()
    {
        return redirect()->route('front.index');
    }
}
