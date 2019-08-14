<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class USSDController extends Controller
{
    public function index (Request $request) {
        $sessionId   = $request["sessionId"];
        $serviceCode = $request["serviceCode"];
        $phoneNumber = $request["phoneNumber"];
        $text        = $request["text"];

        header('Content-type: text/plain');
        echo "CON we just getting started";

    }


    protected function getUser ($phoneNumber) {

    }
}
