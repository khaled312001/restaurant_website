<?php

namespace App\Http\Controllers\Payment\product;

use Exception;
use App\Models\Language;
use App\Models\PostalCode;
use App\Models\BasicSetting;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use Basel\MyFatoorah\MyFatoorah;
use App\Models\PaymentGateway;
use App\Models\ShippingCharge;
use Illuminate\Support\Facades\Session;

class MyfatoorahController extends PaymentController
{
    public $myfatoorah;

    public function __construct()
    {
        $info = PaymentGateway::where('keyword', 'myfatoorah')->first();
        $information = json_decode($info->information, true);
        $this->myfatoorah = MyFatoorah::getInstance($information['myfatoorah_sandbox_status'] == 1 ? true : false);
    }
    

    public function store(Request $request)
    {

        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $be = $currentLang->basic_extended;
        $bs = $currentLang->basic_setting;
        $allowed_currency = ['KWD', 'SAR', 'BHD', 'AED', 'QAR', 'OMR', 'JOD'];
        if (!in_array($be->base_currency_text, $allowed_currency)) {
            return redirect()->back()->with('warning', 'Invalid currency for MyFatoorah payment.');
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

        $random_1 = rand(999, 9999);
        $random_2 = rand(9999, 99999);

        try {
            $result = $this->myfatoorah->sendPayment($order->billing_fname, $order->total, [
                'CustomerMobile' => $gatewayInfo['myfatoorah_sandbox_status'] == 1 ? '56562123544' : $order->billing_number ?? $order->billing_number,
                'CustomerReference' => "$random_1", //orderID
                'UserDefinedField' => "$random_2", //clientID
                'InvoiceItems' => [
                    [
                        'ItemName' => $title,
                        'Quantity' => 1,
                        'UnitPrice' => $order->total,
                    ],
                ],
            ]);
            if ($result && $result['IsSuccess'] == true) {
                Session::forget('title');
                // put some data in session before redirect to gateway
                Session::put('order_data', $order);
                // Session::put('myfatoorah_payment_type', $title);
                return redirect($result['Data']['InvoiceURL']);
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

    public function cancel()
    {
        return redirect()->route('front.index');
    }
}
