<?php

namespace App\Http\Controllers\Payment\product;

use Exception;
use App\Models\Language;
use App\Models\PostalCode;
use Illuminate\Support\Str;
use App\Models\BasicSetting;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Models\ShippingCharge;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class XenditController extends PaymentController
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
        $allowed_currency = ['IDR', 'PHP', 'USD', 'SGD', 'MYR'];
        if (!in_array($be->base_currency_text, $allowed_currency)) {
            return redirect()->back()->with('warning', 'Invalid currency for Xendit payment.');
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
        // Payment start
        $gatewayData = PaymentGateway::where('keyword', 'myfatoorah')->first();
        $gatewayInfo = json_decode($gatewayData->information, true);

        $external_id = Str::random(10);
        $secret_key = 'Basic ' . config('xendit.key_auth');
        $notifyUrl = route('product.xendit.notify');
        try {
            $data_request = Http::withHeaders([
                'Authorization' => $secret_key,
            ])->post('https://api.xendit.co/v2/invoices', [
                'external_id' => $external_id,
                'amount' => $order->total,
                'currency' => $be->base_currency_text,
                'success_redirect_url' => $notifyUrl,
            ]);

            $response = $data_request->object();
            $response = json_decode(json_encode($response), true);
            if (!empty($response['success_redirect_url'])) {
                Session::put('order_data', $order);
                Session::put('xendit_id', $response['id']);
                Session::put('secret_key', config('xendit.key_auth'));
                Session::put('xendit_payment_type', $title);
                return redirect($response['invoice_url']);
            } else {
                return redirect()
                    ->back()
                    ->with('error', $response['message']);
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Payment Canceled.');
        }
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
}
