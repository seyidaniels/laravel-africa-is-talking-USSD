<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function index ($user, $index) {

        
        $response  = "CON Welcome to Credpal, what would you want to  do? \n";
        $response .= "1. Make Customer Purchase\n";
        $response .= "2. Check History ";


        return $response;
    }
}
