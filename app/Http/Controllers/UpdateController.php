<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Http\Controllers\Controller;

class UpdateController extends Controller
{
    public function addPaymentGateways()
    {
        $information['key'] = '';
        $gatewaysData = [
            ['name' => 'Midtrans', 'type' => 'automatic', 'keyword' => 'midtrans', 'information' => json_encode($information), 'status' => 1],
            ['name' => 'Iyzico', 'type' => 'automatic', 'keyword' => 'iyzico', 'information' => json_encode($information), 'status' => 1],
            ['name' => 'Paytabs', 'type' => 'automatic', 'keyword' => 'paytabs', 'information' => json_encode($information), 'status' => 1],
            ['name' => 'Toyyibpay', 'type' => 'automatic', 'keyword' => 'toyyibpay', 'information' => json_encode($information), 'status' => 1],
            ['name' => 'Phonepe', 'type' => 'automatic', 'keyword' => 'phonepe', 'information' => json_encode($information), 'status' => 1],
            ['name' => 'Yoco', 'type' => 'automatic', 'keyword' => 'yoco', 'information' => json_encode($information), 'status' => 1],
            ['name' => 'Myfatoorah', 'type' => 'automatic', 'keyword' => 'myfatoorah', 'information' => json_encode($information), 'status' => 1],
            ['name' => 'Xendit', 'type' => 'automatic', 'keyword' => 'xendit', 'information' => json_encode($information), 'status' => 1],
            ['name' => 'Perfect Money', 'type' => 'automatic', 'keyword' => 'perfect_money', 'information' => json_encode($information), 'status' => 1],
            // Add more gateways as needed
        ];

        foreach ($gatewaysData as $gatewayData) {
            PaymentGateway::create($gatewayData);
        }
        return 'New Gateways added successfully!';
    }

    public function index()
    {
        // run add new payments function
        $this->addPaymentGateways();
    }
}
