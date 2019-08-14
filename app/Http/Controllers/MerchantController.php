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

    public function __construct($sessionId, $merchant, $text) {
        session(['session_id' => $sessionId]);
        session(['user' => $merchant]);

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

                Session::put ([
                    'user' => $user
                ]);

                

                return "CON an Otp was sent to ".$user->name . ".\nPlease enter the OTP below to continue";

            }

            return "END Customer not found";
        }

        return "END Invalid Phone Number field";
        
    }


    function isValidNUmber ($value) {
        return preg_match('/^[0-9]{11}+$/', $value);
    }




}
