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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class YocoController extends PaymentController
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

        if ($be->base_currency_text != 'ZAR') {
            return redirect()->back()->with('error', 'Invalid currency for Yoco payment.');
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
        // type=guest
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
        $notifyURL = route('product.yoco.notify');
        // Payment start
        $gatewayData = PaymentGateway::where('keyword', 'yoco')->first();
        $gatewayInfo = json_decode($gatewayData->information, true);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $gatewayInfo['yoco_secret_key'],
        ])->post('https://payments.yoco.com/api/checkouts', [
            'amount' => $order->total * 100,
            'currency' => $be->base_currency_text,
            'successUrl' => $notifyURL,
        ]);

        $responseData = $response->json();

        if (array_key_exists('redirectUrl', $responseData)) {
            Session::put('yoco_id', $responseData['id']);
            Session::put('s_key', $gatewayInfo['yoco_secret_key']);
            Session::put('order_data', $order);
            return redirect($responseData['redirectUrl']);
        } else {
            return redirect()->back()->with('error', 'Payment failed.');
        }
    }

    public function notify(Request $request)
    {
        $id = Session::get('yoco_id');
        $s_key = Session::get('s_key');
        $info = PaymentGateway::where('keyword', 'yoco')->first();
        $information = json_decode($info->information, true);
        $order_data = Session::get('order_data');
        $order = ProductOrder::find($order_data['id']);

        if ($id && $information['yoco_secret_key'] == $s_key) {
            
    
            $order->payment_status = 'Completed';
            $order->save();
            
            // Notify 
            $this->sendNotifications($order);
            
            // remove these session datas
            Session::forget('yoco_id');
            Session::forget('s_key');
            Session::forget('order_data');
            Session::forget('coupon');
            Session::forget('cart');


            if ($order->type == 'website') {
                $success_url = route('product.payment.return', $order->order_number);
            } elseif ($order->type == 'qr') {
                $success_url = route('qr.payment.return', $order->order_number);
            }
            return redirect($success_url);
        } else {
            return redirect()->route('front.index')->with('error', 'Payment Failed.');
        }
    }
}
