<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
            return "CON Please enter your mobile number";
        }else {
            return "END Invalid Statement";
        }
    }


}
