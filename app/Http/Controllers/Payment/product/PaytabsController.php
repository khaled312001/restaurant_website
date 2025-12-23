<?php

namespace App\Http\Controllers\Payment\product;

use App\Models\Language;
use App\Models\PostalCode;
use App\Models\BasicSetting;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use App\Models\ShippingCharge;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class PaytabsController extends PaymentController
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

        $paytabInfo = paytabInfo();
      
        if ($be->base_currency_text != $paytabInfo['currency']) {
            return redirect()->back()->with('warning', 'Invalid currency for paytabs payment.');
        }
        $notifyURL = route('product.paytabs.notify');

        try {
            $response = Http::withHeaders([
                'Authorization' => $paytabInfo['server_key'], // Server Key
                'Content-Type' => 'application/json',
            ])->post($paytabInfo['url'], [
                'profile_id' => $paytabInfo['profile_id'], // Profile ID
                'tran_type' => 'sale',
                'tran_class' => 'ecom',
                'cart_id' => uniqid(),
                'cart_description' => $title,
                'cart_currency' => $paytabInfo['currency'], // set currency by region
                'cart_amount' => round($order->total, 2),
                'return' => $notifyURL,
            ]);
            $responseData = $response->json();
            // put some data in session before redirect
            Session::put('order_data', $order);
            return redirect()->to($responseData['redirect_url']);
        } catch (\Exception $e) {
            return redirect()->back()->with('warning', 'Payment Canceled.');
        }
    }

    public function success(Request $request)
    {

        $resp = $request->all();
        $arrData = $request->session()->get('arrData');
        if ($resp['respStatus'] == 'A' && $resp['respMessage'] == 'Authorised') {
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
        } else {
            // return cancel;
            return redirect()->route('equipment.make_booking.cancel');
        }
    }
}
