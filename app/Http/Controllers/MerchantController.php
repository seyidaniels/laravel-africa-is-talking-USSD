<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MerchantController extends Controller
{
    private $index;
    private $data;
    private $merchant;

    public function __construct($sessionId, $merchant, $text) {
        session(['session_id' => $sessionId]);
        session(['user' => $merchant]);
        $this->data = explode("*", $text);
        $this->index = count ($this->data);
    }
    public function index () {

        switch ($this->index) {
            case 1: $response = $this->welcome();
            break;
            case 2: $response = "CON Please enter your mobile number";
        }

        return $response;
    }

    public function welcome () {
        $response  = "CON Welcome to Credpal , what would you want to  do? \n";
        $response .= "1. Make Customer Purchase\n";
        $response .= "2. Check History ";
        return $response;
    }


}
