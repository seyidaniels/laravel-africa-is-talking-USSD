<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiContoller;

use Session;



class MerchantController extends Controller
{
    private $index = 0;
    private $data;
    private $response;
    private $user;
    private $credpalAPI;

    public function __construct($sessionId, $merchant, $text) {
        session(['session_id' => $sessionId]);
        session(['merchant' => $merchant]);

        $this->credpalAPI = new ApiContoller();

        if($text != ""){
            $this->data = explode("*", $text);
            $this->index = count ($this->data);
            $this->response = $text;
        }
    }
    public function index () {
        switch ($this->index) {
            case 0: $response = $this->welcome();
            break;
            case 1: $response = $this->firstQuestion();
            break;
            case 2: $response = $this->getsAndVerifesPhone();
            break;
            case 3: $response = $this->validatesOtp();
            break;
            case 4: $response = $this->enterAmount();
            break;
            default: $response = "Ooops! man whatsup";
        }

        return $response;
    }

    public function welcome () {
        $response  = "CON Welcome to Credpal , what would you want to  do? \n";
        $response .= "1. Make Customer Purchase\n";
        $response .= "2. Check History ";
        return $response;
    }

    public function firstQuestion (){
        // Checks now to get what user enters
        $response = $this->response;
        if ($response == "1") {
            return "CON Please Enter Customer Mobile number";
        }else {
            return "END Invalid Statement";
        }
    }

    public function getsAndVerifesPhone () {


        $phoneNumber = $this->data[1];


        if ($this->isValidNUmber($phoneNumber)) {

            // Checks if User exists with the phone number entered
            $credpalAPI = new ApiContoller();

            $user = $credpalAPI->getUser($phoneNumber);


            if ($user && $user->type == "user") {


                if ($user->credit_limit== null) return "END ".$user->name ." does not have a credit limit";

                Session::put (
                    'user', $user
                ); 


                $response = $credpalAPI->sendOtp($user->id);

                return $response->success ? "CON Please enter the OTP that was sent to ".$user->name : "END ".$response->message;
            }

            return "END Customer not found";
        }

        return "END Invalid Phone Number field";
        
    }

    public function validatesOtp () {
        $otp = (int) $this->data[2];

        if (strlen($otp) != 4) return "END Invalid OTP";

        $user = Session::get('user');

        return "END ".$user->name;

        $credpalAPI = new ApiContoller();

        $response = $credpalAPI->confirmOtp([
            'user_id' => $user->id,
            'otp' => $otp
        ]);

       return $response->success ? "CON Kindly enter purchase amount. Limit is ".$user->credit_limit : "END Invalid OTP";

    }

    public function enterAmount () {
        $amount = (int) $this->data[3];
        $user = Session::get('user');
        $merchant = Session::get('merchant');

        if ($amount > $user->credit_limit) return "END Purchase amount cant exceed credit limit";

        $credpalAPI = new ApiContoller();


        $response = $credpalAPI->makePurchase([
            'order_amount' => $amount,
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'order_description' => "USSD transaction",
            'order_tenure' => 1,
            'order_amount_to_borrow' => $amount
        ]);

        Session::forget('user');
        Session::forget('merchant');


        return $response->success ? "END tranaction completed successfully, Check email for receipt" : "END     ".$response->message;


    }


    function isValidNUmber ($value) {
        return preg_match('/^[0-9]{11}+$/', $value);
    }




}
