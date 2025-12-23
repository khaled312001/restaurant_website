<?php

namespace App\Http\Controllers\Payment\product;

use Config\Iyzipay;
use App\Models\Language;
use App\Models\PostalCode;
use App\Models\BasicSetting;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use App\Models\ShippingCharge;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class IyzicoController extends PaymentController
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

        if ($be->base_currency_text != 'TRY') {
            return redirect()->back()->with('error', 'Invalid currency for Iyzico payment.');
        }
        // return $request;
        if ($this->orderValidation($request)) {
            return $this->orderValidation($request);
        }
        $bs = BasicSetting::select('postal_code')->firstOrFail();
        Session::put('zipcode', $request->zipcode);
        Session::put('address', $request->address ?? $request->billing_address);
        Session::put('city', $request->billing_city ?? $request->city);
        Session::put('country', $request->billing_country ?? $request->country);
        Session::put('identity_number', $request->identity_number);

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

        // Create  conversation_id for Iyzico only

        $conversation_id = uniqid(9999, 999999);
        $request['conversation_id'] = $conversation_id;
        // save order
        $order = $this->saveOrder($request, $shipping, $total);
        $order_id = $order->id;
        // save ordered items
        $this->saveOrderItem($order_id);
        return $this->apiRequest($order_id);
    }

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

        $notifyURL = route('product.iyzico.notify');

        // Payment start
        $options = Iyzipay::options();
        $conversation_id = $order->conversation_id;
        $basket_id = 'B' . uniqid(999, 99999);
        # create request class
        $req = new \Iyzipay\Request\CreatePayWithIyzicoInitializeRequest();
        $req->setLocale(\Iyzipay\Model\Locale::EN);
        $req->setConversationId($conversation_id);
        $req->setPrice($order->total);
        $req->setPaidPrice($order->total);
        $req->setCurrency(\Iyzipay\Model\Currency::TL);
        $req->setBasketId($basket_id);
        $req->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
        $req->setCallbackUrl($notifyURL);
        $req->setEnabledInstallments([2, 3, 6, 9]);

        $name = $order->billing_fname;
        $email = $order->billing_email;
        $address = Session::get('address');
        $city = Session::get('city');
        $country = Session::get('country');
        $state = $order->billing_state;
        $zipcode = Session::get('zipcode');
        $phone = $order->billing_number;
        $identity_number = Session::get('identity_number');

        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId(uniqid());
        $buyer->setName($name);
        $buyer->setSurname($name);
        $buyer->setGsmNumber($phone);
        $buyer->setEmail($email);
        $buyer->setIdentityNumber($identity_number);
        $buyer->setLastLoginDate('');
        $buyer->setRegistrationDate('');
        $buyer->setRegistrationAddress($address);
        $buyer->setIp('');
        $buyer->setCity($city);
        $buyer->setCountry($country);
        $buyer->setZipCode($zipcode);
        $req->setBuyer($buyer);

        $shippingAddress = new \Iyzipay\Model\Address();
        $shippingAddress->setContactName($name);
        $shippingAddress->setCity($city);
        $shippingAddress->setCountry($country);
        $shippingAddress->setAddress($address);
        $shippingAddress->setZipCode($zipcode);
        $req->setShippingAddress($shippingAddress);

        $billingAddress = new \Iyzipay\Model\Address();
        $billingAddress->setContactName($name);
        $billingAddress->setCity($city);
        $billingAddress->setCountry($country);
        $billingAddress->setAddress($address);
        $billingAddress->setZipCode($zipcode);
        $req->setBillingAddress($billingAddress);

        $q_id = uniqid(999, 99999);
        $basketItems = [];
        $firstBasketItem = new \Iyzipay\Model\BasketItem();
        $firstBasketItem->setId($q_id);
        $firstBasketItem->setName('Booking Id ' . $q_id);
        $firstBasketItem->setCategory1($title);
        $firstBasketItem->setCategory2('');
        $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
        $firstBasketItem->setPrice($order->total);
        $basketItems[0] = $firstBasketItem;

        $req->setBasketItems($basketItems);

        # make request
        $payWithIyzicoInitialize = \Iyzipay\Model\PayWithIyzicoInitialize::create($req, $options);

        $paymentResponse = (array) $payWithIyzicoInitialize;
        foreach ($paymentResponse as $key => $data) {
            $paymentInfo = json_decode($data, true);
            if ($paymentInfo['status'] == 'success' && !empty($paymentInfo['payWithIyzicoPageUrl'])) {
                Cache::forget('conversation_id');
                Session::put('iyzico_token', $paymentInfo['token']);
                Session::put('conversation_id', $conversation_id);
                Cache::put('conversation_id', $conversation_id, 60000);

                // put some data in session before redirect to gateway
                Session::put('order_data', $order);
                return redirect($paymentInfo['payWithIyzicoPageUrl']);
            }
        }

        if ($order->type == 'website') {
            $cancelUrl = action('Payment\product\PaymentController@paycancle');
        } elseif ($order->type == 'qr') {
            $cancelUrl = action('Payment\product\PaymentController@qrPayCancle');
        }
    }

    public function notify(Request $request)
    {
        $conversation_id = Cache::get('conversation_id');
        $arrData = $request->session()->get('arrData');
        $arrData['conversation_id'] = $conversation_id;

        $order_data = Session::get('order_data');
        $order = ProductOrder::find($order_data['id']);
        if ($order->type == 'website') {
            $cancel_url = action('Payment\product\PaymentController@paycancle');
        } elseif ($order->type == 'qr') {
            $cancel_url = action('Payment\product\PaymentController@qrPayCancle');
        }

        Session::forget('order_data');
        Session::forget('coupon');
        Session::forget('cart');

        if ($order->type == 'website') {
            $success_url = route('product.payment.return', $order->order_number);
        } elseif ($order->type == 'qr') {
            $success_url = route('qr.payment.return', $order->order_number);
        }
        return redirect($success_url);

        // return redirect($cancel_url);
    }
}
