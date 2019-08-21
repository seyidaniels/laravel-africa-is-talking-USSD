<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiContoller;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use Session;
use stdClass;

class MerchantController extends Controller
{
    private $index = 0;
    private $data;
    private $response;
    private $user;
    private $credpalAPI;

    public function __construct($sessionId, $merchant, $text, $request) {
        session(['session_id' => $sessionId]);
        session(['merchant' => $merchant]);
        $this->request = $request;

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
            // case 3: $response = $this->otpValidation();
            // break;
            case 3: $response = $this->enterAmount();
            break;
            case 4: $response = $this->proceedWithTransaction();
            break;
            case 5: $response = $this->confirmsTransaction();
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
            return "CON Please enter Order Amount";
        }

        return "END Invalid Phone Number field";
        
    }


    // public function otpValidation (){
    //     $otp = $this->data[2];

    //     if ($this->isValidOTP($otp)) {
    //         return "CON Kindly enter purchase amount.";
    //     }

    //     return "END Invalid OTP";
        

    // }

    public function isValidOTP ($otp) {
        if (strlen($otp) != 4) return "END Invalid OTP";

        $credpalAPI = new ApiContoller();

        $user = $credpalAPI->getUser($this->data[1]);

        $response = $credpalAPI->confirmOtp([
            'user_id' => $user->id,
            'otp' => $otp
        ]);

        return $response->success ? true: false;
    }

    public function enterAmount () {
        $credpalAPI = new ApiContoller();
        $amount = (int) $this->data[2];
        $user = $credpalAPI->getUser($this->data[1]);
        $merchant = Session::get('merchant');
        $customerMessage = "";

        if ($amount > $user->credit_limit)  {
            $difference = $amount - $user->credit_limit;

            $response = "CON Customer is mandated to physically pay you ₦".$difference . " to you while we pay you ₦".$user->credit_limit."\n
            Press 1 to confirm!
            ";

            // send Otp to finalise Transaction and also tell Customer aboutn what he needs to do
            $customerMessage =  "Dear ".$user->name. ", you are required to pay ₦". $this->repaymentAmount($difference) . " over a period of 3 months. An OTP would be sent to you when merchant confirms this transaction"; 
        }else {
            $response = "CON Customer is not mandated to physically pay you, we would be paying fully for this purchase./service.\n
            Press 1 to confirm!
            ";

            $customerMessage =  "Dear ".$user->name. ", you are required to pay ₦". $this->repaymentAmount($amount) . " over a period of 3 months. An OTP would be sent to you when merchant confirms this transaction"; 
        }


        
        // Sends Text Message


        return $response;

        // recordPayment ()

        // $response = $credpalAPI->makePurchase([
        //     'order_amount' => $amount,
        //     'user_id' => $user->id,
        //     'merchant_id' => $merchant->id,
        //     'order_description' => "USSD transaction",
        //     'order_tenure' => 1,
        //     'order_amount_to_borrow' => $amount
        // ]);

        // Session::forget('user');
        // Session::forget('merchant');


        // return $response->success ? "END tranaction completed successfully, Check email for receipt" : "END     ".$response->message;


    }


    public function proceedWithTransaction () {
        $confirmation = (int) $this->data[3];

        if ($confirmation == 1) {
            // Sends OTP to the Customer
           return  $this->sendsOtp();

        }
        return "END Invalid Input";
        
    }

    public function confirmsTransaction () {
        $otp = (int) $this->data[4];
        if ($this->isValidOTP($otp)) {
            $credpalAPI = new ApiContoller();
            $amount = (int) $this->data[3];
            $user = $credpalAPI->getUser($this->data[1]);
            $merchant = Session::get('merchant');


            $response = $credpalAPI->makePurchase([
                'order_amount' => $amount,
                'user_id' => $user->id,
                'merchant_id' => $merchant->id,
                'order_description' => "USSD transaction",
                'order_tenure' => 3,
                'order_amount_to_borrow' => $amount
            ]);

            Session::forget('user');
            Session::forget('merchant');


            return $response->success ? "END tranaction completed successfully, Check email for receipt" : "END     ".$response->message;
        }

        return "END Invalid OTP";
            
    }

    public function sendsOtp() {
        $phoneNumber = $this->data[1];
        // Checks if User exists with the phone number entered
        $credpalAPI = new ApiContoller();

        $user = $credpalAPI->getUser($phoneNumber);

        if ($user && $user->type == "user") {

            if ($user->credit_limit== null) return "END ".$user->name ." does not have a credit limit";


            $response = $credpalAPI->sendOtp($user->id);

            return $response->success ? "CON Please enter the OTP that was sent to ".$user->name : "END ".$response->message;
        }

        return "END Customer not found";
    }




    protected function repaymentAmount ($amount){
        return 0.07 * $amount + $amount;
    }



    function isValidNUmber ($value) {
        return preg_match('/^[0-9]{11}+$/', $value);
    }




}
