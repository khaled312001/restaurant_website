<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\OfflineGateway;
use App\Models\PaymentGateway;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class GatewayController extends Controller
{
    public function index()
    {
        $data['paypal'] = PaymentGateway::find(15);
        $data['stripe'] = PaymentGateway::find(14);
        $data['paystack'] = PaymentGateway::find(12);
        $data['paytm'] = PaymentGateway::find(11);
        $data['flutterwave'] = PaymentGateway::find(6);
        $data['instamojo'] = PaymentGateway::find(13);
        $data['mollie'] = PaymentGateway::find(17);
        $data['razorpay'] = PaymentGateway::find(9);
        $data['mercadopago'] = PaymentGateway::find(19);

        $data['midtrans'] = PaymentGateway::where('keyword', 'midtrans')->first();
        $data['paytabs'] = PaymentGateway::where('keyword', 'paytabs')->first();
        $data['iyzico'] = PaymentGateway::where('keyword', 'iyzico')->first();
        $data['toyyibpay'] = PaymentGateway::where('keyword', 'toyyibpay')->first();
        $data['phonepe'] = PaymentGateway::where('keyword', 'phonepe')->first();
        $data['myfatoorah'] = PaymentGateway::where('keyword', 'myfatoorah')->first();
        $data['xendit'] = PaymentGateway::where('keyword', 'xendit')->first();
        $data['yoco'] = PaymentGateway::where('keyword', 'yoco')->first();
        $data['perfect_money'] = PaymentGateway::where('keyword', 'perfect_money')->first();

        return view('admin.gateways.index', $data);
    }

    public function paypalUpdate(Request $request)
    {
        $paypal = PaymentGateway::find(15);
        $paypal->status = $request->status;

        $information = [];
        $information['client_id'] = $request->client_id;
        $information['client_secret'] = $request->client_secret;
        $information['sandbox_check'] = $request->sandbox_check;
        $information['text'] = 'Pay via your PayPal account.';

        $paypal->information = json_encode($information);

        $paypal->save();

        $request->session()->flash('success', 'Paypal informations updated successfully!');

        return back();
    }

    public function stripeUpdate(Request $request)
    {
        $stripe = PaymentGateway::find(14);
        $stripe->status = $request->status;

        $information = [];
        $information['key'] = $request->key;
        $information['secret'] = $request->secret;
        $information['text'] = 'Pay via your Credit account.';

        $stripe->information = json_encode($information);

        $stripe->save();

        $array = [
            'STRIPE_KEY' => $request->key,
            'STRIPE_SECRET' => $request->secret,
        ];

        setEnvironmentValue($array);
        Artisan::call('config:clear');

        Session::flash('success', 'Stripe informations updated successfully!');

        return back();
    }

    public function paystackUpdate(Request $request)
    {
        $paystack = PaymentGateway::find(12);
        $paystack->status = $request->status;

        $information = [];
        $information['key'] = $request->key;
        $information['text'] = 'Pay via your Paystack account.';

        $paystack->information = json_encode($information);

        $paystack->save();

        $request->session()->flash('success', 'Paystack informations updated successfully!');

        return back();
    }

    public function paytmUpdate(Request $request)
    {
        $paytm = PaymentGateway::find(11);
        $paytm->status = $request->status;

        $information = [];
        $information['merchant'] = $request->merchant;
        $information['secret'] = $request->secret;
        $information['website'] = $request->website;
        $information['industry'] = $request->industry;
        $information['text'] = 'Pay via your paytm account.';

        $paytm->information = json_encode($information);

        $paytm->save();

        $request->session()->flash('success', 'Paytm informations updated successfully!');

        return back();
    }

    public function flutterwaveUpdate(Request $request)
    {
        $flutterwave = PaymentGateway::find(6);
        $flutterwave->status = $request->status;

        $information = [];
        $information['public_key'] = $request->public_key;
        $information['secret_key'] = $request->secret_key;
        $information['text'] = 'Pay via your Flutterwave account.';

        $flutterwave->information = json_encode($information);

        $flutterwave->save();

        $request->session()->flash('success', 'Flutterwave informations updated successfully!');

        return back();
    }

    public function instamojoUpdate(Request $request)
    {
        $instamojo = PaymentGateway::find(13);
        $instamojo->status = $request->status;

        $information = [];
        $information['key'] = $request->key;
        $information['token'] = $request->token;
        $information['sandbox_check'] = $request->sandbox_check;
        $information['text'] = 'Pay via your Instamojo account.';

        $instamojo->information = json_encode($information);

        $instamojo->save();

        $request->session()->flash('success', 'Instamojo informations updated successfully!');

        return back();
    }

    public function mollieUpdate(Request $request)
    {
        $mollie = PaymentGateway::find(17);
        $mollie->status = $request->status;

        $information = [];
        $information['key'] = $request->key;
        $information['text'] = 'Pay via your Mollie Payment account.';

        $mollie->information = json_encode($information);

        $mollie->save();

        $arr = ['MOLLIE_KEY' => $request->key];
        setEnvironmentValue($arr);
        Artisan::call('config:clear');

        Session::flash('success', 'Mollie Payment informations updated successfully!');

        return back();
    }

    public function razorpayUpdate(Request $request)
    {
        $razorpay = PaymentGateway::find(9);
        $razorpay->status = $request->status;

        $information = [];
        $information['key'] = $request->key;
        $information['secret'] = $request->secret;
        $information['text'] = 'Pay via your Razorpay account.';

        $razorpay->information = json_encode($information);

        $razorpay->save();

        $request->session()->flash('success', 'Razorpay informations updated successfully!');

        return back();
    }

    public function mercadopagoUpdate(Request $request)
    {
        $mercadopago = PaymentGateway::find(19);
        $mercadopago->status = $request->status;

        $information = [];
        $information['token'] = $request->token;
        $information['sandbox_check'] = $request->sandbox_check;
        $information['text'] = 'Pay via your Mercado Pago account.';

        $mercadopago->information = json_encode($information);

        $mercadopago->save();

        Session::flash('success', 'Mercado Pago informations updated successfully!');

        return back();
    }

    public function midtransUpdate(Request $request)
    {
        $rules = [
            'midtrans_status' => 'required',
            'mindtrans_test_mode' => 'required',
            'midtrans_server_key' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }
        $information['midtrans_status'] = $request->midtrans_status;
        $information['mindtrans_test_mode'] = $request->mindtrans_test_mode;
        $information['midtrans_server_key'] = $request->midtrans_server_key;
        $info = PaymentGateway::where('keyword', 'midtrans')->first();
        $info->information = json_encode($information);
        $info->status = $request->midtrans_status;
        $info->save();
        Session::flash('success', 'Updated Successfully');
        return redirect()->back();
    }

    public function perfectmoneyUpdate(Request $request)
    {
        $rules = [
            'perfect_money_wallet_id' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }
        $perfect_money = PaymentGateway::where('keyword', 'perfect_money')->first();
        $perfect_money->status = $request->status;
        $information = [];
        $information['perfect_money_wallet_id'] = $request->perfect_money_wallet_id;
        $perfect_money->information = json_encode($information);
        $perfect_money->save();
        Session::flash('success', 'Perfect Money informations updated successfully!');
        return back();
    }

    public function paytabsUpdate(Request $request)
    {
        $rules = [
            'paytabs_status' => 'required',
            'profile_id' => 'required',
            'server_key' => 'required',
            'country' => 'required',
            'api_endpoint' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $information['paytabs_status'] = $request->paytabs_status;
        $information['profile_id'] = $request->profile_id;
        $information['server_key'] = $request->server_key;
        $information['country'] = $request->country;
        $information['api_endpoint'] = $request->api_endpoint;

        $data = PaymentGateway::where('keyword', 'paytabs')->first();

        $data->information = json_encode($information);
        $data->status = $request->paytabs_status;
        $data->save();
        Session::flash('success', 'Updated Successfully');

        return redirect()->back();
    }

    public function iyzicoUpdate(Request $request)
    {
        $rules = [
            'status' => 'required',
            'iyzico_sandbox_status' => 'required',
            'api_key' => 'required',
            'secret_key' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $information['iyzico_sandbox_status'] = $request->iyzico_sandbox_status;
        $information['api_key'] = $request->api_key;
        $information['secret_key'] = $request->secret_key;

        $data = PaymentGateway::where('keyword', 'iyzico')->first();

        $data->information = json_encode($information);
        $data->status = $request->status;
        $data->save();

        Session::flash('success', 'Updated Iyzico Information Successfully');
        return redirect()->back();
    }
    public function toyyibpayUpdate(Request $request)
    {
        $rules = [
            'toyyibpay_status' => 'required',
            'toyyibpay_secret_key' => 'required',
            'category_code' => 'required',
            'toyyibpay_sandbox_status' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }
        $information['toyyibpay_secret_key'] = $request->toyyibpay_secret_key;
        $information['category_code'] = $request->category_code;
        $information['toyyibpay_sandbox_status'] = $request->toyyibpay_sandbox_status;
        $data = PaymentGateway::where('keyword', 'toyyibpay')->first();
        $data->information = json_encode($information);
        $data->status = $request->toyyibpay_status;
        $data->save();
        Session::flash('success', 'Updated Successfully');
        return redirect()->back();
    }
    public function phonepeUpdate(Request $request)
    {
        $rules = [
            'phonepe_status' => 'required',
            'phonepe_merchant_id' => 'required',
            'phonepe_sandbox_status' => 'required',
            'salt_key' => 'required',
            'salt_index' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $information['phonepe_merchant_id'] = $request->phonepe_merchant_id;
        $information['salt_key'] = $request->salt_key;
        $information['phonepe_sandbox_status'] = $request->phonepe_sandbox_status;
        $information['salt_index'] = $request->salt_index;

        $data = PaymentGateway::where('keyword', 'phonepe')->first();

        $data->information = json_encode($information);
        $data->status = $request->phonepe_status;
        $data->save();
        Session::flash('success', 'Updated Successfully');

        return redirect()->back();
    }
    public function myfatoorahUpdate(Request $request)
    {
        $rules = [
            'myfatoorah_status' => 'required',
            'myfatoorah_sandbox_status' => 'required',
            'token' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $information['myfatoorah_status'] = $request->myfatoorah_status;
        $information['token'] = $request->token;
        $information['myfatoorah_sandbox_status'] = $request->myfatoorah_sandbox_status;

        $data = PaymentGateway::where('keyword', 'myfatoorah')->first();

        $data->information = json_encode($information);
        $data->status = $request->myfatoorah_status;
        $data->save();

        $array = [
            'MYFATOORAH_TOKEN' => $request->token,
        ];

        setEnvironmentValue($array);

        Session::flash('success', 'Updated Successfully');

        return redirect()->back();
    }
    public function xenditUpdate(Request $request)
    {
        $rules = [
            'xendit_status' => 'required',
            'secret_api_key' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $information['secret_api_key'] = $request->secret_api_key;

        $data = PaymentGateway::where('keyword', 'xendit')->first();

        $data->information = json_encode($information);
        $data->status = $request->xendit_status;
        $data->save();
        $array = [
            'XENDIT_SECRET_KEY' => $request->secret_api_key,
        ];

        setEnvironmentValue($array);
        Artisan::call('config:clear');

        Session::flash('success', 'Updated Successfully');

        return redirect()->back();
    }
    public function yocoUpdate(Request $request)
    {
        $rules = [
            'yoco_status' => 'required',
            'yoco_secret_key' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }
        $information['yoco_secret_key'] = $request->yoco_secret_key;
        $data = PaymentGateway::where('keyword', 'yoco')->first();
        $data->update([
            'information' => json_encode($information),
            'status' => $request->yoco_status,
        ]);
        Session::flash('success', 'Updated Yoco Information Successfully');
        return redirect()->back();
    }

    public function offline(Request $request)
    {
        $data['ogateways'] = OfflineGateway::orderBy('id', 'DESC')->get();

        return view('admin.gateways.offline.index', $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|max:100',
            'short_description' => 'nullable',
            'serial_number' => 'required|integer',
            'is_receipt' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $in = $request->all();

        OfflineGateway::create($in);

        Session::flash('success', 'Gateway added successfully!');
        return 'success';
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => 'required|max:100',
            'short_description' => 'nullable',
            'serial_number' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $in = $request->except('_token', 'ogateway_id');

        OfflineGateway::where('id', $request->ogateway_id)->update($in);

        Session::flash('success', 'Gateway updated successfully!');
        return 'success';
    }

    public function status(Request $request)
    {
        $og = OfflineGateway::find($request->ogateway_id);
        $og->status = $request->status;
        $og->save();

        Session::flash('success', 'Gateway status changed successfully!');
        return back();
    }

    public function delete(Request $request)
    {
        $ogateway = OfflineGateway::findOrFail($request->ogateway_id);
        $ogateway->delete();

        Session::flash('success', 'Gateway deleted successfully!');
        return back();
    }
}
