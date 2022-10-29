<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Stripe\Charge;
use Stripe\Stripe;
use Stripe\Token;
use Illuminate\Support\Facades\Session;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response; 

class ProcessController extends Controller
{

    /*
     * Stripe Gateway
     */
    public static function process($deposit)
    {

        $alias = $deposit->gateway->alias;

        $send['track'] = $deposit->trx;
        $send['view'] = 'user.payment.'.$alias;
        $send['method'] = 'post';
        $send['url'] = route('ipn.'.$alias);
        return json_encode($send);
    }

    public function ipn(Request $request)
    {
        $notify = [];
        
        $cc = $request->cc;
        $exp = $request->exp;
        $cvc = $request->cvc;
        $eyr = $request->eyr;
        $emo = $request->emo;
        $cnts = $request->cnts;
        $stripeAcc = $request->stripeAcc;
        $method_currency = ($request->method_currency);

        Stripe::setApiKey($stripeAcc['secret_key']);
        Stripe::setApiVersion("2020-03-02");
        

        try {
            $token = Token::create(array(
                "card" => array(
                    "number" => "$cc",
                    "exp_month" => $emo,
                    "exp_year" => $eyr,
                    "cvc" => "$cvc"
                )
            ));
            try {
                $charge = Charge::create(array(
                    'card' => $token['id'],
                    'currency' => $method_currency,
                    'amount' => $cnts,
                    'description' => 'item',
                ));

                if ($charge['status'] == 'succeeded') {

                    return response()->json(['status'=>'succeeded']) ;
                }
            } catch (\Exception $e) {
                $notify[] = ['error', $e->getMessage()];
            }
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
        }
        
        return response()->json(['status'=>'failed', 'notifiy'=>$notify]);

    }
}
